<?php

namespace App\Imports;

use App\Models\Siswa;
use App\Models\Kelas;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class SiswaImport 
{
    private $errors = [];
    private $processed = 0;
    private $success = 0;

    /**
     * Import students from Excel/CSV
     */
    public function import($filePath)
    {
        // Determine file type by extension
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        if ($extension === 'csv') {
            return $this->importCsv($filePath);
        } else {
            // Try to use Excel library
            try {
                // Load the Excel file
                $reader = \Maatwebsite\Excel\Facades\Excel::load($filePath, function($reader) {
                    // Set configuration
                    $reader->ignoreEmpty();
                });
                
                // Get all sheets
                $results = $reader->get();
                
                // Process each row
                foreach ($results as $row) {
                    $this->processRow($row);
                }
            } catch (\Exception $e) {
                // If Excel library fails, try CSV import as fallback
                return $this->importCsv($filePath);
            }
        }
        
        // Return import statistics
        return [
            'processed' => $this->processed,
            'success' => $this->success,
            'failed' => $this->processed - $this->success,
            'errors' => $this->errors
        ];
    }
    
    /**
     * Import from CSV file
     */
    protected function importCsv($filePath)
    {
        // Open the CSV file
        $file = fopen($filePath, 'r');
        if (!$file) {
            $this->errors[] = "Tidak dapat membuka file CSV";
            return [
                'processed' => 0,
                'success' => 0,
                'failed' => 0,
                'errors' => $this->errors
            ];
        }
        
        // Get headers
        $headers = fgetcsv($file);
        if (!$headers) {
            $this->errors[] = "Format CSV tidak valid: header tidak ditemukan";
            fclose($file);
            return [
                'processed' => 0,
                'success' => 0,
                'failed' => 0,
                'errors' => $this->errors
            ];
        }
        
        // Convert headers to lowercase
        $headers = array_map('strtolower', $headers);
        
        // Process each row
        while (($data = fgetcsv($file)) !== false) {
            // Skip empty rows
            if (empty($data) || count(array_filter($data)) === 0) {
                continue;
            }
            
            // Create row object with properties
            $row = new \stdClass();
            foreach ($headers as $i => $header) {
                if (isset($data[$i])) {
                    $row->{$header} = $data[$i];
                } else {
                    $row->{$header} = null;
                }
            }
            
            // Process the row
            $this->processRow($row);
        }
        
        // Close the file
        fclose($file);
        
        // Return import statistics
        return [
            'processed' => $this->processed,
            'success' => $this->success,
            'failed' => $this->processed - $this->success,
            'errors' => $this->errors
        ];
    }

    /**
     * Process a single row
     */
    private function processRow($row)
    {
        $this->processed++;

        try {
            // Check required fields
            if (empty($row->nis) || empty($row->nama) || 
                empty($row->tingkat) || empty($row->kelas) || 
                empty($row->jurusan)) {
                
                $this->errors[] = "Baris dengan NIS: {$row->nis} - Data tidak lengkap";
                return;
            }

            // Find kelas by tingkat, nama_kelas, and jurusan
            $kelas = Kelas::whereHas('jurusan', function ($query) use ($row) {
                $query->where('nama_jurusan', $row->jurusan);
            })
            ->where('tingkat', $row->tingkat)
            ->where('nama_kelas', $row->kelas)
            ->first();

            // If kelas not found, log error and skip
            if (!$kelas) {
                $this->errors[] = "Baris dengan NIS: {$row->nis} - Kelas '{$row->tingkat} {$row->kelas} - {$row->jurusan}' tidak ditemukan";
                return;
            }

            // Parse the date of birth if provided
            $tanggalLahir = null;
            if (!empty($row->tanggal_lahir)) {
                try {
                    $tanggalLahir = Carbon::createFromFormat('d/m/Y', $row->tanggal_lahir)->format('Y-m-d');
                } catch (\Exception $e) {
                    try {
                        $tanggalLahir = Carbon::parse($row->tanggal_lahir)->format('Y-m-d');
                    } catch (\Exception $e) {
                        $tanggalLahir = null;
                    }
                }
            }

            // Generate QR code for the student
            $qrCode = Siswa::generateQrCode();

            // Create or update the student
            Siswa::updateOrCreate(
                ['nis' => $row->nis], // Find by NIS
                [
                    'nama' => $row->nama,
                    'jenis_kelamin' => $row->jenis_kelamin ?? 'L', // Default to 'L' if not provided
                    'tanggal_lahir' => $tanggalLahir,
                    'alamat' => $row->alamat ?? null,
                    'nomor_hp' => $row->nomor_hp ?? null,
                    'kelas_id' => $kelas->id,
                    'qr_code' => $qrCode,
                    'status_aktif' => true
                ]
            );

            $this->success++;
        } catch (\Exception $e) {
            $this->errors[] = "Baris dengan NIS: {$row->nis} - Error: " . $e->getMessage();
        }
    }
}
