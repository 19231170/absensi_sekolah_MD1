<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Jurusan;
use App\Imports\SiswaImport;
use App\Imports\FastExcelSiswaImport;
use App\Imports\CsvImportFallback;
use Carbon\Carbon;

class SiswaController extends Controller
{
    /**
     * Display a listing of the siswa.
     */
    public function index()
    {
        $siswa = Siswa::with('kelas.jurusan')->orderBy('nama')->get();
        return view('siswa.index', compact('siswa'));
    }

    /**
     * Show the form for creating a new siswa.
     */
    public function create()
    {
        $kelas = Kelas::with('jurusan')->get();
        return view('siswa.create', compact('kelas'));
    }

    /**
     * Store a newly created siswa in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nis' => 'required|string|max:20|unique:siswa,nis',
            'nama' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'tanggal_lahir' => 'nullable|date',
            'alamat' => 'nullable|string',
            'nomor_hp' => 'nullable|string|max:15',
            'kelas_id' => 'required|exists:kelas,id',
            'status_aktif' => 'required|in:0,1'
        ]);

        // Generate QR Code
        $qrCode = Siswa::generateQrCode();

        $siswa = new Siswa([
            'nis' => $request->nis,
            'nama' => $request->nama,
            'jenis_kelamin' => $request->jenis_kelamin,
            'tanggal_lahir' => $request->tanggal_lahir,
            'alamat' => $request->alamat,
            'nomor_hp' => $request->nomor_hp,
            'kelas_id' => $request->kelas_id,
            'qr_code' => $qrCode,
            'status_aktif' => $request->status_aktif == '1' ? true : false
        ]);

        $siswa->save();

        return redirect()->route('siswa.index')
            ->with('success', 'Siswa berhasil ditambahkan!');
    }

    /**
     * Display the specified siswa.
     */
    public function show($nis)
    {
        $siswa = Siswa::with('kelas.jurusan')->findOrFail($nis);
        return view('siswa.show', compact('siswa'));
    }

    /**
     * Show the form for editing the specified siswa.
     */
    public function edit($nis)
    {
        $siswa = Siswa::findOrFail($nis);
        $kelas = Kelas::with('jurusan')->get();
        
        return view('siswa.edit', compact('siswa', 'kelas'));
    }

    /**
     * Update the specified siswa in storage.
     */
    public function update(Request $request, $nis)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'tanggal_lahir' => 'nullable|date',
            'alamat' => 'nullable|string',
            'nomor_hp' => 'nullable|string|max:15',
            'kelas_id' => 'required|exists:kelas,id',
            'status_aktif' => 'required|in:0,1'
        ]);

        $siswa = Siswa::findOrFail($nis);
        $siswa->nama = $request->nama;
        $siswa->jenis_kelamin = $request->jenis_kelamin;
        $siswa->tanggal_lahir = $request->tanggal_lahir;
        $siswa->alamat = $request->alamat;
        $siswa->nomor_hp = $request->nomor_hp;
        $siswa->kelas_id = $request->kelas_id;
        $siswa->status_aktif = $request->status_aktif == '1' ? true : false;
        $siswa->save();

        return redirect()->route('siswa.index')
            ->with('success', 'Data siswa berhasil diupdate!');
    }

    /**
     * Remove the specified siswa from storage.
     */
    public function destroy($nis)
    {
        try {
            $siswa = Siswa::where('nis', $nis)->firstOrFail();
            
            // Optional: Check if siswa has related records that need to be handled
            // For example, delete related absensi records or set constraints
            
            $siswaName = $siswa->nama;
            $siswa->delete();

            \Log::info('Siswa deleted successfully', [
                'nis' => $nis,
                'nama' => $siswaName
            ]);

            return redirect()->route('siswa.index')
                ->with('success', "Siswa {$siswaName} berhasil dihapus!");
                
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::warning('Attempt to delete non-existent siswa', ['nis' => $nis]);
            
            return redirect()->route('siswa.index')
                ->with('error', 'Siswa tidak ditemukan.');
                
        } catch (\Exception $e) {
            \Log::error('Error deleting siswa', [
                'nis' => $nis,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('siswa.index')
                ->with('error', 'Gagal menghapus siswa. Silakan coba lagi.');
        }
    }

    /**
     * Show form for importing students from Excel.
     */
    public function importForm()
    {
        return view('siswa.import');
    }

    /**
     * Import students from Excel file (Simplified Format).
     * Expected columns: nama_siswa, nis, jenis_kelamin, jurusan, kelas
     */
    public function importExcel(Request $request)
    {
        // More flexible file validation - only check for file presence and size
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'file' => 'required|file|max:2048'
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('siswa.import')
                ->withErrors($validator)
                ->withInput();
        }
        
        $file = $request->file('file');
        
        try {
            // Comprehensive file debugging
            Log::info('Processing simplified import', [
                'filename' => $file->getClientOriginalName(),
                'client_extension' => $file->getClientOriginalExtension(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'real_path' => $file->getRealPath(),
                'is_valid' => $file->isValid(),
                'path_info' => pathinfo($file->getClientOriginalName()),
                'error' => $file->getError()
            ]);
            
            // Validate file upload first
            if (!$file->isValid()) {
                Log::error('File upload invalid', [
                    'error_code' => $file->getError(),
                    'filename' => $file->getClientOriginalName()
                ]);
                throw new \Exception('File upload tidak valid. Silakan coba lagi.');
            }
            
            // Get file extension - try multiple methods for reliability
            $originalExtension = strtolower($file->getClientOriginalExtension());
            $pathExtension = strtolower(pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));
            
            // Use the most reliable extension
            $detectedExtension = !empty($originalExtension) ? $originalExtension : $pathExtension;
            
            Log::info('Extension detection', [
                'client_extension' => $originalExtension,
                'path_extension' => $pathExtension,
                'detected_extension' => $detectedExtension
            ]);
            
            // Check if extension is supported (more flexible validation)
            $supportedExtensions = ['xls', 'xlsx', 'csv'];
            if (!in_array($detectedExtension, $supportedExtensions)) {
                Log::warning('Unsupported file extension', [
                    'detected_extension' => $detectedExtension,
                    'client_extension' => $originalExtension,
                    'path_extension' => $pathExtension,
                    'filename' => $file->getClientOriginalName(),
                    'mime' => $file->getMimeType()
                ]);
                
                return redirect()->route('siswa.import')
                    ->with('error', "Format file tidak didukung. Gunakan .xls, .xlsx, atau .csv. Format yang terdeteksi: " . $detectedExtension . " (client: " . $originalExtension . ", path: " . $pathExtension . ")");
            }
            
            // Check if ZipArchive is available
            $zipArchiveAvailable = class_exists('ZipArchive');
            Log::info('ZipArchive availability', ['available' => $zipArchiveAvailable]);
            
            if ($zipArchiveAvailable) {
                // Use the normal import process if ZipArchive is available
                $import = new SiswaImport();
                $result = $import->import($file);
            } else {
                // If ZipArchive is not available and file is CSV, use our fallback
                if ($detectedExtension === 'csv') {
                    Log::info('Using CSV fallback import (ZipArchive not available)');
                    
                    try {
                        // Get the temporary path of the uploaded file
                        $tempPath = $file->getRealPath();
                        
                        // Use our CSV fallback
                        $collection = CsvImportFallback::import($tempPath);
                        
                        // Process the data manually
                        $import = new SiswaImport();
                        $processedRows = 0;
                        $successRows = 0;
                        $importErrors = [];
                        
                        foreach ($collection as $row) {
                            try {
                                $processedRows++;
                                $result = $import->processRow($row);
                                if ($result) {
                                    $successRows++;
                                }
                            } catch (\Exception $rowException) {
                                $importErrors[] = "Error pada baris {$processedRows}: " . $rowException->getMessage();
                                Log::warning("Import row error", [
                                    'row' => $processedRows,
                                    'error' => $rowException->getMessage(),
                                    'data' => $row
                                ]);
                            }
                        }
                        
                        $result = [
                            'success' => true,
                            'processed' => $processedRows,
                            'success_count' => $successRows,
                            'failed' => $processedRows - $successRows,
                            'errors' => $importErrors
                        ];
                    } catch (\Exception $csvException) {
                        throw new \Exception("Error dengan CSV fallback: " . $csvException->getMessage());
                    }
                } else {
                    // For Excel files, we need ZipArchive
                    throw new \Exception(
                        "Tidak dapat mengimport file Excel karena ekstensi PHP ZipArchive tidak tersedia. ".
                        "Silakan gunakan file CSV atau pasang ekstensi PHP zip di server Anda."
                    );
                }
            }
            
            // Check if import was successful
            if (!$result['success']) {
                throw new \Exception('Import failed: ' . implode(', ', $result['errors']));
            }
            
            // Get import statistics  
            $stats = $import->getImportStats();
            
            Log::info('Import completed', $stats);

            $message = "Import selesai: {$stats['success']} siswa berhasil diimport";
            
            if ($stats['failed'] > 0) {
                $message .= ", {$stats['failed']} siswa gagal diimport.";
                return redirect()->route('siswa.import')
                    ->with('warning', $message)
                    ->with('import_errors', $stats['errors']);
            }
            
            return redirect()->route('siswa.index')
                ->with('success', $message);
                
        } catch (\Exception $e) {
            Log::error('Import error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $file ? $file->getClientOriginalName() : 'no file',
                'file_size' => $file ? $file->getSize() : 0,
                'file_mime' => $file ? $file->getMimeType() : 'unknown'
            ]);
            
            // Provide user-friendly error messages
            $userMessage = 'Error: ';
            if (strpos($e->getMessage(), 'Could not open') !== false) {
                $userMessage .= 'File tidak dapat dibaca. Pastikan file tidak corrupt dan format benar.';
            } elseif (strpos($e->getMessage(), 'File kosong') !== false) {
                $userMessage .= 'File kosong atau tidak memiliki data. Pastikan file berisi data siswa.';
            } elseif (strpos($e->getMessage(), 'Format file') !== false) {
                $userMessage .= 'Format file tidak didukung. Gunakan file Excel (.xlsx, .xls) atau CSV (.csv).';
            } else {
                $userMessage .= $e->getMessage();
            }
            
            return redirect()->route('siswa.import')
                ->with('error', $userMessage);
        }
    }

    /**
     * Download Excel template for student import (Simplified Format).
     * New Format: nama_siswa, nis, jenis_kelamin, jurusan, kelas
     */
    public function downloadTemplate()
    {
        try {
            // Create a file in the storage path instead of a temporary file
            $storageDir = storage_path('app/public/templates');
            
            // Ensure directory exists
            if (!file_exists($storageDir)) {
                mkdir($storageDir, 0755, true);
            }
            
            $filePath = $storageDir . '/Template_Siswa_Simplified.csv';
            
            // Simplified CSV header (4 kolom - lebih ringkas)
            $header = [
                'nis', 'nama_siswa', 'jenis_kelamin', 'jurusan/kelas'
            ];
            
            // Example data rows dengan format baru yang lebih ringkas
            $exampleRows = [
                [
                    '2024001', 'Ahmad Fauzi', 'L', 'TKJ/10 A'
                ],
                [
                    '2024002', 'Siti Nurhaliza', 'P', 'RPL/10 B'
                ],
                [
                    '2024003', 'Budi Santoso', 'L', 'TKR/11 A'
                ],
                [
                    '2024004', 'Dewi Sartika', 'P', 'AKL/11 AKL 1'
                ],
                [
                    '2024005', 'Ridwan Kamil', 'L', 'MM/12 MM 2'
                ]
            ];
            
            // Write CSV file without BOM to avoid import issues
            $csv = fopen($filePath, 'w');
            
            if (!$csv) {
                throw new \Exception('Tidak dapat membuat file template');
            }
            
            // Write header row (no BOM)
            fputcsv($csv, $header);
            
            // Write example rows
            foreach ($exampleRows as $row) {
                fputcsv($csv, $row);
            }
            
            fclose($csv);
            
            // Verify file was created successfully
            if (!file_exists($filePath) || filesize($filePath) === 0) {
                throw new \Exception('Template file tidak dapat dibuat dengan benar');
            }
            
            Log::info('Template download requested', [
                'file_path' => $filePath,
                'file_size' => filesize($filePath),
                'file_exists' => file_exists($filePath)
            ]);
            
            // Create response with the file
            return response()->download(
                $filePath, 
                'Template_Siswa_Simplified.csv', 
                [
                    'Content-Type' => 'text/csv; charset=UTF-8',
                    'Content-Disposition' => 'attachment; filename="Template_Siswa_Simplified.csv"'
                ]
            );
            
        } catch (\Exception $e) {
            Log::error('Template download error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('siswa.import')
                ->with('error', 'Error downloading template: ' . $e->getMessage());
        }
    }
    
    /**
     * Download Excel template (XLSX format) for student import
     */
    public function downloadTemplateExcel()
    {
        // Create an Excel file using fast-excel
        $storageDir = storage_path('app/public/templates');
        
        // Ensure directory exists
        if (!file_exists($storageDir)) {
            mkdir($storageDir, 0755, true);
        }
        
        $filePath = $storageDir . '/Template_Siswa.xlsx';
        
        // Create example data
        $data = collect([
            [
                'nis' => '12345678',
                'nama' => 'Nama Siswa',
                'tingkat' => 'X',
                'kelas' => 'A',
                'jurusan' => 'TKJ',
                'jenis_kelamin' => 'L',
                'tanggal_lahir' => '01/01/2008',
                'alamat' => 'Jl. Contoh No. 123',
                'nomor_hp' => '081234567890'
            ],
            [
                'nis' => '87654321',
                'nama' => 'Siswa Contoh',
                'tingkat' => 'XI',
                'kelas' => 'B',
                'jurusan' => 'RPL',
                'jenis_kelamin' => 'P',
                'tanggal_lahir' => '02/02/2007',
                'alamat' => 'Jl. Contoh No. 456',
                'nomor_hp' => '089876543210'
            ]
        ]);
        
        // Export to Excel
        (new \Rap2hpoutre\FastExcel\FastExcel($data))->export($filePath);
        
        // Create response with the file
        return response()->download(
            $filePath, 
            'Template_Siswa.xlsx', 
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="Template_Siswa.xlsx"'
            ]
        );
    }
}
