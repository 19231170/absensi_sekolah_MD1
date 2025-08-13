<?php

namespace App\Imports;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Jurusan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Rap2hpoutre\FastExcel\FastExcel;

class FastExcelSiswaImport
{
    protected $successCount = 0;
    protected $failedCount = 0;
    protected $errors = [];

    /**
     * Import data from file
     *
     * @param string $filePath
     * @return array
     */
    public function import($filePath)
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeType = mime_content_type($filePath);
        
        // Log the file info for debugging
        \Illuminate\Support\Facades\Log::info('Importing file', [
            'path' => $filePath,
            'extension' => $ext,
            'mime_type' => $mimeType,
            'file_size' => filesize($filePath),
            'exists' => file_exists($filePath),
            'is_readable' => is_readable($filePath)
        ]);
        
        // Check by mime type first
        if (str_contains($mimeType, 'excel') || str_contains($mimeType, 'spreadsheet') || 
            in_array($ext, ['xls', 'xlsx'])) {
            return $this->importExcel($filePath);
        } else if (str_contains($mimeType, 'csv') || str_contains($mimeType, 'text/plain') || 
                  $ext === 'csv') {
            return $this->importCsv($filePath);
        } else if ($ext === 'tmp' || empty($ext)) {
            // Try to detect by reading first few bytes
            $handle = fopen($filePath, 'r');
            $header = fread($handle, 8);
            fclose($handle);
            
            // Excel files usually start with these bytes
            if (str_starts_with(bin2hex($header), 'd0cf11e0') || str_starts_with(bin2hex($header), '504b0304')) {
                return $this->importExcel($filePath);
            } else {
                // Default to CSV for other types
                return $this->importCsv($filePath);
            }
        }
        
        throw new \Exception("Format file tidak didukung. Gunakan .xls, .xlsx, atau .csv. Format yang terdeteksi: " . $ext . " (MIME: " . $mimeType . ")");
    }

    /**
     * Import data from Excel file
     *
     * @param string $filePath
     * @return array
     */
    protected function importExcel($filePath)
    {
        $fastExcel = new FastExcel();
        $collection = $fastExcel->import($filePath);
        return $this->processImport($collection);
    }

    /**
     * Import data from CSV file
     *
     * @param string $filePath
     * @return array
     */
    protected function importCsv($filePath)
    {
        // First try using FastExcel
        try {
            $fastExcel = new FastExcel();
            $collection = $fastExcel->importSheets($filePath);
            
            if (!isset($collection[0]) || empty($collection[0])) {
                throw new \Exception("FastExcel tidak dapat membaca data CSV");
            }
            
            return $this->processImport($collection[0]);
        } catch (\Exception $e) {
            // If FastExcel fails, use native PHP CSV parsing as fallback
            \Illuminate\Support\Facades\Log::warning('FastExcel failed to import CSV, using fallback', [
                'error' => $e->getMessage()
            ]);
            
            return $this->importCsvFallback($filePath);
        }
    }
    
    /**
     * Fallback method to import CSV using native PHP functions
     * 
     * @param string $filePath
     * @return array
     */
    protected function importCsvFallback($filePath)
    {
        // Try to open the file
        $file = fopen($filePath, 'r');
        if (!$file) {
            throw new \Exception("Tidak dapat membuka file CSV");
        }
        
        // Read headers
        $headers = fgetcsv($file);
        if (!$headers) {
            fclose($file);
            throw new \Exception("Format CSV tidak valid: header tidak ditemukan");
        }
        
        // Convert headers to lowercase
        $headers = array_map('strtolower', $headers);
        
        // Read data rows
        $rows = [];
        while (($data = fgetcsv($file)) !== false) {
            // Skip empty rows
            if (empty($data) || count(array_filter($data)) === 0) {
                continue;
            }
            
            // Create associative array
            $row = [];
            foreach ($headers as $i => $header) {
                if (isset($data[$i])) {
                    $row[$header] = $data[$i];
                } else {
                    $row[$header] = null;
                }
            }
            
            $rows[] = $row;
        }
        
        // Close the file
        fclose($file);
        
        // Log what was found
        \Illuminate\Support\Facades\Log::info('CSV import fallback', [
            'headers' => $headers,
            'row_count' => count($rows),
            'first_row' => !empty($rows) ? $rows[0] : null
        ]);
        
        // Process the data
        return $this->processImport(collect($rows));
    }

    /**
     * Process the imported data
     *
     * @param Collection $rows
     * @return array
     */
    protected function processImport($rows)
    {
        DB::beginTransaction();
        
        try {
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // Account for header row and 0-based index
                $this->processRow($row, $rowNumber);
            }
            
            DB::commit();
            
            return [
                'success' => $this->successCount,
                'failed' => $this->failedCount,
                'errors' => $this->errors
            ];
            
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Process a single row from the import
     *
     * @param array $row
     * @param int $rowNumber
     */
    protected function processRow($row, $rowNumber)
    {
        // Handle lower case headers and normalize them
        $normalizedRow = $this->normalizeRowKeys($row);
        
        // Validation
        $validator = Validator::make($normalizedRow, [
            'nis' => 'required|string|max:20',
            'nama' => 'required|string|max:100',
            'tingkat' => 'required|string|in:X,XI,XII',
            'kelas' => 'required|string|max:5',
            'jurusan' => 'required|string|max:50',
            'jenis_kelamin' => 'required|string|in:L,P',
            'tanggal_lahir' => 'required',
            'alamat' => 'nullable|string',
            'nomor_hp' => 'nullable|string|max:15',
        ]);

        if ($validator->fails()) {
            $this->failedCount++;
            $this->errors[] = [
                'row' => $rowNumber,
                'errors' => $validator->errors()->all(),
                'data' => $normalizedRow
            ];
            return;
        }

        try {
            // Parse tanggal lahir (assuming dd/mm/yyyy format from template)
            $tanggalLahir = Carbon::createFromFormat('d/m/Y', $normalizedRow['tanggal_lahir']);
            
            // Find or create jurusan
            $jurusan = Jurusan::firstOrCreate(
                ['nama' => $normalizedRow['jurusan']],
                ['kode' => substr($normalizedRow['jurusan'], 0, 3)]
            );
            
            // Find or create kelas
            $kelas = Kelas::firstOrCreate([
                'tingkat' => $normalizedRow['tingkat'],
                'nama' => $normalizedRow['kelas'],
                'jurusan_id' => $jurusan->id,
            ]);
            
            // Find or create siswa
            $siswa = Siswa::updateOrCreate(
                ['nis' => $normalizedRow['nis']],
                [
                    'nama' => $normalizedRow['nama'],
                    'kelas_id' => $kelas->id,
                    'jenis_kelamin' => $normalizedRow['jenis_kelamin'],
                    'tanggal_lahir' => $tanggalLahir,
                    'alamat' => $normalizedRow['alamat'] ?? null,
                    'nomor_hp' => $normalizedRow['nomor_hp'] ?? null,
                ]
            );
            
            $this->successCount++;
            
        } catch (\Exception $e) {
            $this->failedCount++;
            $this->errors[] = [
                'row' => $rowNumber,
                'errors' => [$e->getMessage()],
                'data' => $normalizedRow
            ];
        }
    }

    /**
     * Normalize row keys to handle different header formats
     *
     * @param array $row
     * @return array
     */
    protected function normalizeRowKeys($row)
    {
        $normalized = [];
        $keyMap = [
            'nis' => ['nis', 'nisn', 'nomorinduk', 'nomor induk'],
            'nama' => ['nama', 'nama siswa', 'namasiswa'],
            'tingkat' => ['tingkat', 'kelas', 'level'],
            'kelas' => ['kelas', 'nama kelas', 'kelompok', 'rombel'],
            'jurusan' => ['jurusan', 'kejuruan', 'bidang', 'program'],
            'jenis_kelamin' => ['jenis_kelamin', 'jeniskelamin', 'jenis kelamin', 'gender', 'sex'],
            'tanggal_lahir' => ['tanggal_lahir', 'tanggallahir', 'tanggal lahir', 'tgl lahir', 'tgllahir', 'dob'],
            'alamat' => ['alamat', 'address'],
            'nomor_hp' => ['nomor_hp', 'nomorhp', 'nomor hp', 'telepon', 'phone', 'hp', 'no hp', 'no telepon', 'notelepon']
        ];
        
        // Lowercase all keys from the input
        $lowercaseRow = [];
        foreach ($row as $key => $value) {
            $lowercaseRow[strtolower(str_replace('_', '', $key))] = $value;
        }
        
        // Map input keys to normalized keys
        foreach ($keyMap as $normalKey => $possibleKeys) {
            foreach ($possibleKeys as $possibleKey) {
                $searchKey = strtolower(str_replace('_', '', $possibleKey));
                if (isset($lowercaseRow[$searchKey])) {
                    $normalized[$normalKey] = $lowercaseRow[$searchKey];
                    break;
                }
            }
        }
        
        return $normalized;
    }
}
