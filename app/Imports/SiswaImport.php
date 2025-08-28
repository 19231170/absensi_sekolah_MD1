<?php

namespace App\Imports;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Jurusan;
use Illuminate\Support\Facades\Log;
use Rap2hpoutre\FastExcel\FastExcel;

/**
 * Simplified Student Import using FastExcel
 * Format Kolom (hanya 5 kolom):
 * 1. nama_siswa - Nama lengkap siswa
 * 2. nis - Nomor Induk Siswa (unik)
 * 3. jenis_kelamin - L atau P
 * 4. jurusan - Nama jurusan
 * 5. kelas - Format: "10 A" atau "11 IPA 1" (tingkat + nama kelas)
 */
class SiswaImport
{
    private $processedRows = 0;
    private $successRows = 0;
    private $importErrors = [];

    /**
     * Import from uploaded file
     */
    public function import($file)
    {
        $this->processedRows = 0;
        $this->successRows = 0;
        $this->importErrors = [];

        try {
            // Handle different input types
            if (is_string($file)) {
                // File path provided
                $filePath = $file;
            } else {
                // Uploaded file object provided
                $filePath = $file->getRealPath();
            }
            
            // Verify file exists and is readable
            if (!file_exists($filePath) || !is_readable($filePath)) {
                throw new \Exception("File tidak dapat dibaca: {$filePath}");
            }
            
            Log::info('Starting import process', [
                'file_path' => $filePath,
                'file_size' => filesize($filePath),
                'file_exists' => file_exists($filePath),
                'is_readable' => is_readable($filePath)
            ]);
            
            // Create a temporary copy to ensure file access
            $tempPath = $this->createSecureTempFile($filePath);
            
            try {
                Log::info('Attempting FastExcel import', [
                    'temp_path' => $tempPath,
                    'temp_file_size' => filesize($tempPath),
                    'temp_file_exists' => file_exists($tempPath)
                ]);
                
                $collection = (new FastExcel())->import($tempPath);
                
                Log::info('FastExcel import result', [
                    'collection_type' => gettype($collection),
                    'collection_count' => $collection ? $collection->count() : 0,
                    'is_empty' => $collection ? $collection->isEmpty() : true
                ]);
                
                // Check if collection is empty
                if (!$collection || $collection->isEmpty()) {
                    throw new \Exception("File kosong atau format tidak dapat dibaca");
                }
                
                foreach ($collection as $row) {
                    $this->processRow($row);
                }
                
                return [
                    'success' => true,
                    'processed' => $this->processedRows,
                    'success_count' => $this->successRows,
                    'failed' => $this->processedRows - $this->successRows,
                    'errors' => $this->importErrors
                ];
                
            } catch (\Exception $e) {
                // Try alternative approach for problematic files
                Log::warning('FastExcel failed, trying manual CSV parsing', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $tempPath,
                    'file_exists' => file_exists($tempPath),
                    'file_size' => file_exists($tempPath) ? filesize($tempPath) : 0
                ]);
                
                return $this->fallbackCsvImport($tempPath);
            } finally {
                // Clean up temporary file
                if (file_exists($tempPath)) {
                    @unlink($tempPath);
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Import file error', [
                'error' => $e->getMessage(),
                'file' => isset($filePath) ? $filePath : 'unknown',
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'processed' => 0,
                'success_count' => 0,
                'failed' => 1,
                'errors' => ['Error membaca file: ' . $e->getMessage()]
            ];
        }
    }

    /**
     * Create a secure temporary file copy
     */
    private function createSecureTempFile($originalPath)
    {
        // Create temp directory if it doesn't exist
        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        
        // Generate unique temp file name
        $tempFile = $tempDir . '/import_' . uniqid() . '_' . time() . '.tmp';
        
        // Copy file to secure location
        if (!copy($originalPath, $tempFile)) {
            throw new \Exception("Tidak dapat membuat file temporary: {$tempFile}");
        }
        
        return $tempFile;
    }

    /**
     * Process single row
     */
    private function processRow($row)
    {
        $this->processedRows++;
        
        Log::info('Processing import row', [
            'row_number' => $this->processedRows,
            'data' => $row
        ]);

        try {
            // Normalize column names (handle different case variations and BOM)
            $normalizedRow = [];
            foreach ($row as $key => $value) {
                // Remove BOM (UTF-8 BOM = ï»¿) from key
                $cleanKey = $this->removeBOM(trim($key));
                $normalizedKey = strtolower($cleanKey);
                $normalizedRow[$normalizedKey] = $value;
            }

            // Map possible column variations
            $nama = $normalizedRow['nama_siswa'] ?? $normalizedRow['nama siswa'] ?? null;
            $nis = $normalizedRow['nis'] ?? null;
            $jenisKelamin = $normalizedRow['jenis_kelamin'] ?? $normalizedRow['jenis kelamin'] ?? null;
            $jurusan = $normalizedRow['jurusan'] ?? null;
            $kelas = $normalizedRow['kelas'] ?? null;

            // Validate required fields
            $missingFields = [];
            if (empty($nama)) $missingFields[] = 'nama_siswa';
            if (empty($nis)) $missingFields[] = 'nis';
            if (empty($jenisKelamin)) $missingFields[] = 'jenis_kelamin';
            if (empty($jurusan)) $missingFields[] = 'jurusan';
            if (empty($kelas)) $missingFields[] = 'kelas';
            
            if (!empty($missingFields)) {
                $error = "Baris {$this->processedRows}: Data tidak lengkap, field kosong: " . implode(', ', $missingFields);
                $this->importErrors[] = $error;
                Log::warning($error, [
                    'row' => $normalizedRow,
                    'missing_fields' => $missingFields,
                    'extracted_data' => [
                        'nama' => $nama,
                        'nis' => $nis,
                        'jenis_kelamin' => $jenisKelamin,
                        'jurusan' => $jurusan,
                        'kelas' => $kelas
                    ]
                ]);
                return;
            }

            // Parse kelas field (format: "10 A" atau "11 IPA 1")
            $kelasInfo = $this->parseKelasField($kelas);
            if (!$kelasInfo) {
                $error = "Baris {$this->processedRows}: Format kelas '{$kelas}' tidak valid. Gunakan format: '10 A' atau '11 IPA 1'";
                $this->importErrors[] = $error;
                Log::warning($error, ['kelas_field' => $kelas]);
                return;
            }

            // Find or create jurusan
            $jurusanModel = Jurusan::firstOrCreate(
                ['nama_jurusan' => trim($jurusan)],
                [
                    'kode_jurusan' => $this->generateKodeJurusan(trim($jurusan)),
                    'deskripsi' => 'Auto-created from import'
                ]
            );

            // Find or create kelas
            $kelasModel = Kelas::firstOrCreate(
                [
                    'tingkat' => $kelasInfo['tingkat'],
                    'nama_kelas' => $kelasInfo['nama_kelas'],
                    'jurusan_id' => $jurusanModel->id
                ],
                [
                    'kapasitas' => 40, // Default capacity
                    'status_aktif' => true
                ]
            );

            // Validate jenis_kelamin
            $jenisKelaminNormalized = strtoupper(trim($jenisKelamin));
            if (!in_array($jenisKelaminNormalized, ['L', 'P'])) {
                $error = "Baris {$this->processedRows}: Jenis kelamin harus 'L' atau 'P', diterima: '{$jenisKelamin}'";
                $this->importErrors[] = $error;
                Log::warning($error, ['jenis_kelamin' => $jenisKelamin]);
                return;
            }

            // Generate QR code if not exists
            $qrCode = Siswa::generateQrCode();

            // Create or update siswa
            $siswa = Siswa::updateOrCreate(
                ['nis' => trim($nis)],
                [
                    'nama' => trim($nama),
                    'jenis_kelamin' => $jenisKelaminNormalized,
                    'tanggal_lahir' => '2000-01-01', // Default birth date untuk import
                    'kelas_id' => $kelasModel->id,
                    'qr_code' => $qrCode,
                    'status_aktif' => true
                ]
            );

            $this->successRows++;
            
            Log::info('Successfully imported student', [
                'nis' => $siswa->nis,
                'nama' => $siswa->nama,
                'kelas' => "{$kelasModel->tingkat} {$kelasModel->nama_kelas}",
                'jurusan' => $jurusanModel->nama_jurusan
            ]);

        } catch (\Exception $e) {
            $error = "Baris {$this->processedRows}: Error - " . $e->getMessage();
            $this->importErrors[] = $error;
            Log::error($error, [
                'exception' => $e,
                'row' => $row
            ]);
        }
    }

    /**
     * Remove BOM (Byte Order Mark) from string
     */
    private function removeBOM($string)
    {
        // UTF-8 BOM
        if (substr($string, 0, 3) === "\xEF\xBB\xBF") {
            return substr($string, 3);
        }
        
        return $string;
    }

    /**
     * Generate kode jurusan from nama jurusan
     * Example: "Teknik Komputer dan Jaringan" -> "TKJ"
     */
    private function generateKodeJurusan($namaJurusan)
    {
        // Split by words and take first letter of each significant word
        $words = explode(' ', $namaJurusan);
        $kode = '';
        
        foreach ($words as $word) {
            $word = trim($word);
            // Skip common words like "dan", "atau", etc.
            if (!in_array(strtolower($word), ['dan', 'atau', 'atau', 'di', 'ke', 'dari', 'dengan'])) {
                $kode .= strtoupper(substr($word, 0, 1));
            }
        }
        
        // Ensure unique kode
        $originalKode = $kode;
        $counter = 1;
        while (Jurusan::where('kode_jurusan', $kode)->exists()) {
            $kode = $originalKode . $counter;
            $counter++;
        }
        
        return $kode;
    }

    /**
     * Parse kelas field to extract tingkat and nama_kelas
     * Formats supported:
     * - "10 A" -> tingkat: 10, nama_kelas: A
     * - "11 IPA 1" -> tingkat: 11, nama_kelas: IPA 1
     * - "12 IPS 2" -> tingkat: 12, nama_kelas: IPS 2
     */
    private function parseKelasField($kelasField)
    {
        $kelasField = trim($kelasField);
        
        // Pattern: tingkat (space) nama_kelas
        if (preg_match('/^(\d{1,2})\s+(.+)$/', $kelasField, $matches)) {
            return [
                'tingkat' => intval($matches[1]),
                'nama_kelas' => trim($matches[2])
            ];
        }
        
        return null;
    }

    /**
     * Get import statistics
     */
    public function getImportStats()
    {
        return [
            'processed' => $this->processedRows,
            'success' => $this->successRows,
            'failed' => $this->processedRows - $this->successRows,
            'errors' => $this->importErrors
        ];
    }

    /**
     * Fallback CSV import when FastExcel fails
     */
    private function fallbackCsvImport($filePath)
    {
        try {
            // Note: For temporary files, we don't rely on extension
            // because temp files may have .tmp extension
            // We already validated the original file extension in the controller
            Log::info('Fallback CSV import starting', [
                'file_path' => $filePath,
                'file_exists' => file_exists($filePath)
            ]);
            
            $handle = fopen($filePath, 'r');
            if (!$handle) {
                throw new \Exception("Tidak dapat membuka file untuk fallback import");
            }
            
            // Read header row
            $headers = fgetcsv($handle);
            if (!$headers) {
                fclose($handle);
                throw new \Exception("Header CSV tidak ditemukan");
            }
            
            // Normalize headers
            $headers = array_map(function($header) {
                return strtolower(trim($header));
            }, $headers);
            
            // Process data rows
            while (($data = fgetcsv($handle)) !== false) {
                if (empty(array_filter($data))) {
                    continue; // Skip empty rows
                }
                
                // Create associative array
                $row = [];
                foreach ($headers as $index => $header) {
                    $row[$header] = isset($data[$index]) ? $data[$index] : '';
                }
                
                $this->processRow($row);
            }
            
            fclose($handle);
            
            return [
                'success' => true,
                'processed' => $this->processedRows,
                'success_count' => $this->successRows,
                'failed' => $this->processedRows - $this->successRows,
                'errors' => $this->importErrors
            ];
            
        } catch (\Exception $e) {
            if (isset($handle) && $handle) {
                fclose($handle);
            }
            
            throw new \Exception("Fallback CSV import gagal: " . $e->getMessage());
        }
    }
}
