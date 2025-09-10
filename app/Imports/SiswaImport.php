<?php

namespace App\Imports;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Jurusan;
use Illuminate\Support\Facades\Log;
use Rap2hpoutre\FastExcel\FastExcel;

/**
 * Simplified Student Import using FastExcel
 * Format Kolom (hanya 4 kolom):
 * 1. nis - Nomor Induk Siswa (unik)
 * 2. nama_siswa - Nama lengkap siswa
 * 3. jenis_kelamin - L atau P
 * 4. jurusan/kelas - Format: "TKJ/10 A" atau "RPL/11 IPA 1" (jurusan/tingkat + nama kelas)
 *                    Tingkat menggunakan angka (10, 11, 12) bukan romawi
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
            $nis = $normalizedRow['nis'] ?? null;
            $nama = $normalizedRow['nama_siswa'] ?? $normalizedRow['nama siswa'] ?? null;
            $jenisKelamin = $normalizedRow['jenis_kelamin'] ?? $normalizedRow['jenis kelamin'] ?? null;
            $jurusanKelas = $normalizedRow['jurusan/kelas'] ?? $normalizedRow['jurusan/kelas'] ?? $normalizedRow['jurusan kelas'] ?? null;

            // Validate required fields
            $missingFields = [];
            if (empty($nis)) $missingFields[] = 'nis';
            if (empty($nama)) $missingFields[] = 'nama_siswa';
            if (empty($jenisKelamin)) $missingFields[] = 'jenis_kelamin';
            if (empty($jurusanKelas)) $missingFields[] = 'jurusan/kelas';
            
            if (!empty($missingFields)) {
                $error = "Baris {$this->processedRows}: Data tidak lengkap, field kosong: " . implode(', ', $missingFields);
                $this->importErrors[] = $error;
                Log::warning($error, [
                    'row' => $normalizedRow,
                    'missing_fields' => $missingFields,
                    'extracted_data' => [
                        'nis' => $nis,
                        'nama' => $nama,
                        'jenis_kelamin' => $jenisKelamin,
                        'jurusan_kelas' => $jurusanKelas
                    ]
                ]);
                return;
            }

                // Parse jurusan/kelas field to extract jurusan and kelas info
                $parsedInfo = $this->parseJurusanKelasField($jurusanKelas);
                if (!$parsedInfo) {
                    // Cobalah teknik parsing alternatif untuk format 'Rekayasa Perangkat Lunak/10 RPL 1'
                    $parsedInfo = $this->parseAlternativeFormat($jurusanKelas);
                    
                    if (!$parsedInfo) {
                        $error = "Baris {$this->processedRows}: Format jurusan/kelas '{$jurusanKelas}' tidak valid. Gunakan format: 'TKJ/10 A', 'RPL/11 IPA 1', atau 'Rekayasa Perangkat Lunak/10 RPL 1'";
                        $this->importErrors[] = $error;
                        Log::warning($error, ['jurusan_kelas_field' => $jurusanKelas]);
                        return;
                    }
                }            // Find or create jurusan based on kode_jurusan or create new
            $jurusanModel = Jurusan::firstOrCreate(
                ['kode_jurusan' => $parsedInfo['kode_jurusan']],
                [
                    'nama_jurusan' => $parsedInfo['nama_jurusan'],
                    'deskripsi' => 'Auto-created from import'
                ]
            );

            // Find or create kelas
            $kelasModel = Kelas::firstOrCreate(
                [
                    'tingkat' => $parsedInfo['tingkat'],
                    'nama_kelas' => $parsedInfo['nama_kelas'],
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
                'jurusan' => $jurusanModel->kode_jurusan
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
        // First try to match with known jurusan names
        $kodeFromNama = $this->extractKodeFromNamaJurusan($namaJurusan);
        if ($kodeFromNama) {
            return $kodeFromNama;
        }
        
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
        
        // If code is empty, use first 3 letters of the name
        if (empty($kode) && !empty($namaJurusan)) {
            $kode = strtoupper(substr($namaJurusan, 0, min(3, strlen($namaJurusan))));
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
     * Parse alternative format of jurusan/kelas
     * Specifically for "Rekayasa Perangkat Lunak/10 RPL 1" format
     */
    private function parseAlternativeFormat($jurusanKelasField) 
    {
        $jurusanKelasField = trim($jurusanKelasField);
        
        // Coba ekstrak format "Rekayasa Perangkat Lunak/10 RPL 1"
        if (preg_match('/^(.+?)\/(\d{1,2})\s+([A-Za-z]+)\s+(\d+)$/', $jurusanKelasField, $matches)) {
            $namaJurusan = trim($matches[1]);
            $tingkat = intval($matches[2]);
            $kodeKelas = trim($matches[3]);
            $nomorKelas = trim($matches[4]);
            
            // Generate kode jurusan dari nama jurusan
            $kodeJurusan = $this->extractKodeFromNamaJurusan($namaJurusan);
            
            // Gabungkan nama kelas dari komponen
            $namaKelas = $kodeKelas . ' ' . $nomorKelas;
            
            Log::info('Parsed alternative format', [
                'original' => $jurusanKelasField,
                'nama_jurusan' => $namaJurusan,
                'kode_jurusan' => $kodeJurusan,
                'tingkat' => $tingkat,
                'nama_kelas' => $namaKelas
            ]);
            
            return [
                'kode_jurusan' => $kodeJurusan,
                'nama_jurusan' => $namaJurusan,
                'tingkat' => $tingkat,
                'nama_kelas' => $namaKelas
            ];
        }
        
        return null;
    }

    /**
     * Parse jurusan/kelas field to extract jurusan and kelas info
     * Formats supported:
     * - "TKJ/10 A" -> jurusan: TKJ, tingkat: 10, nama_kelas: A
     * - "RPL/11 IPA 1" -> jurusan: RPL, tingkat: 11, nama_kelas: IPA 1
     * - "Rekayasa Perangkat Lunak/10 RPL 1" -> nama jurusan lengkap dikonversi ke kode
     */
    private function parseJurusanKelasField($jurusanKelasField)
    {
        $jurusanKelasField = trim($jurusanKelasField);
        
        // Coba ekstrak pola jurusan/kelas dengan format lengkap
        if (preg_match('/^(.+?)\/(\d{1,2})\s+(.+)$/', $jurusanKelasField, $matches)) {
            $jurusanText = trim($matches[1]);
            $tingkat = intval($matches[2]);
            $namaKelas = trim($matches[3]);
            
            // Cek apakah jurusan adalah kode atau nama lengkap
            if (strlen($jurusanText) <= 5 && preg_match('/^[A-Za-z]+$/', $jurusanText)) {
                // Ini adalah kode jurusan (seperti TKJ, RPL)
                $kodeJurusan = strtoupper($jurusanText);
                $jurusan = Jurusan::where('kode_jurusan', $kodeJurusan)->first();
                $namaJurusan = $jurusan ? $jurusan->nama_jurusan : $this->generateNamaJurusan($kodeJurusan);
            } else {
                // Ini adalah nama jurusan lengkap (seperti "Rekayasa Perangkat Lunak")
                $namaJurusan = $jurusanText;
                $kodeJurusan = $this->generateKodeJurusan($jurusanText);
            }
            
            Log::info('Parsed jurusan/kelas', [
                'original' => $jurusanKelasField,
                'jurusan_text' => $jurusanText,
                'kode_jurusan' => $kodeJurusan,
                'tingkat' => $tingkat,
                'nama_kelas' => $namaKelas
            ]);
            
            return [
                'kode_jurusan' => $kodeJurusan,
                'nama_jurusan' => $namaJurusan,
                'tingkat' => $tingkat,
                'nama_kelas' => $namaKelas
            ];
        }
        
        return null;
    }

    /**
     * Generate nama jurusan from kode jurusan if not exists
     * This is a fallback method when only jurusan code is provided
     */
    private function generateNamaJurusan($kodeJurusan)
    {
        // Common mappings for well-known jurusan codes
        $commonMappings = [
            'TKJ' => 'Teknik Komputer dan Jaringan',
            'RPL' => 'Rekayasa Perangkat Lunak',
            'MM' => 'Multimedia',
            'AKL' => 'Akuntansi dan Keuangan Lembaga',
            'TKR' => 'Teknik Kendaraan Ringan',
            'TSM' => 'Teknik Sepeda Motor',
            'AP' => 'Administrasi Perkantoran',
            'TB' => 'Tata Busana',
            'TL' => 'Teknik Listrik',
            'TBG' => 'Tata Boga',
            'TPHP' => 'Teknologi Pengolahan Hasil Pertanian',
            'DPIB' => 'Desain Permodelan dan Informasi Bangunan',
            'BDP' => 'Bisnis Daring dan Pemasaran',
            'OTKP' => 'Otomatisasi dan Tata Kelola Perkantoran',
            'UPW' => 'Usaha Perjalanan Wisata',
            'IPA' => 'Ilmu Pengetahuan Alam',
            'IPS' => 'Ilmu Pengetahuan Sosial'
        ];
        
        // Reverse lookup - handle full text to codes
        $reverseMappings = array_flip(array_map('strtolower', $commonMappings));
        
        $kodeJurusan = strtoupper($kodeJurusan);
        
        if (isset($commonMappings[$kodeJurusan])) {
            return $commonMappings[$kodeJurusan];
        }
        
        // Fallback for unknown codes
        return "Jurusan " . $kodeJurusan;
    }
    
    /**
     * Extract jurusan kode from nama jurusan if only full name is provided
     * For example: "Rekayasa Perangkat Lunak" -> "RPL"
     */
    private function extractKodeFromNamaJurusan($namaJurusan)
    {
        $commonNamaToKode = [
            'rekayasa perangkat lunak' => 'RPL',
            'teknik komputer dan jaringan' => 'TKJ',
            'multimedia' => 'MM',
            'akuntansi dan keuangan lembaga' => 'AKL',
            'teknik kendaraan ringan' => 'TKR',
            'teknik sepeda motor' => 'TSM',
            'administrasi perkantoran' => 'AP',
            'tata busana' => 'TB',
            'teknik listrik' => 'TL',
            'tata boga' => 'TBG',
            'teknologi pengolahan hasil pertanian' => 'TPHP',
            'desain permodelan dan informasi bangunan' => 'DPIB',
            'bisnis daring dan pemasaran' => 'BDP',
            'otomatisasi dan tata kelola perkantoran' => 'OTKP',
            'usaha perjalanan wisata' => 'UPW',
            'ilmu pengetahuan alam' => 'IPA',
            'ilmu pengetahuan sosial' => 'IPS'
        ];
        
        // Try exact match
        $namaJurusanLower = strtolower(trim($namaJurusan));
        if (isset($commonNamaToKode[$namaJurusanLower])) {
            return $commonNamaToKode[$namaJurusanLower];
        }
        
        // Try partial match
        foreach ($commonNamaToKode as $nama => $kode) {
            if (strpos($namaJurusanLower, $nama) !== false) {
                return $kode;
            }
        }
        
        // Fallback: generate from first letter of each word
        return $this->generateKodeJurusan($namaJurusan);
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
