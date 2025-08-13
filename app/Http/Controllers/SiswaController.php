<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Jurusan;
use App\Imports\SiswaImport;
use App\Imports\FastExcelSiswaImport;
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
        $siswa = Siswa::findOrFail($nis);
        $siswa->delete();

        return redirect()->route('siswa.index')
            ->with('success', 'Siswa berhasil dihapus!');
    }

    /**
     * Show form for importing students from Excel.
     */
    public function importForm()
    {
        return view('siswa.import');
    }

    /**
     * Import students from Excel file.
     */
    public function importExcel(Request $request)
    {
        // Validate the file upload
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'file' => 'required|file'
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('siswa.import')
                ->withErrors($validator)
                ->withInput();
        }
        
        $file = $request->file('file');
        
        try {
            // Log file information for debugging
            \Illuminate\Support\Facades\Log::info('Importing file', [
                'filename' => $file->getClientOriginalName(),
                'extension' => $file->getClientOriginalExtension(),
                'mime' => $file->getMimeType(),
                'real_path' => $file->getRealPath()
            ]);
            
            // Get the file extension
            $originalExtension = strtolower($file->getClientOriginalExtension());
            
            // Check if extension is supported
            if (!in_array($originalExtension, ['xls', 'xlsx', 'csv'])) {
                return redirect()->route('siswa.import')
                    ->with('error', "Format file tidak didukung. Gunakan .xls, .xlsx, atau .csv. Format yang terdeteksi: " . $originalExtension);
            }
            
            // Create a copy of the file with the correct extension
            $newFilename = 'import_' . time() . '.' . $originalExtension;
            $newPath = storage_path('app/temp/' . $newFilename);
            
            // Ensure the directory exists
            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }
            
            // Copy the file to the new location
            copy($file->getRealPath(), $newPath);
            
            // Check if the new file exists and is readable
            if (!file_exists($newPath) || !is_readable($newPath)) {
                throw new \Exception('File tidak dapat dibaca. Coba upload ulang.');
            }
            
            // Use the new FastExcelSiswaImport with the correct file extension
            $import = new \App\Imports\FastExcelSiswaImport();
            $result = $import->import($newPath);

            // Delete the temporary file
            @unlink($newPath);

            $message = "Import selesai: {$result['success']} siswa berhasil diimport";
            
            if ($result['failed'] > 0) {
                $message .= ", {$result['failed']} siswa gagal diimport.";
                return redirect()->route('siswa.import')
                    ->with('warning', $message)
                    ->with('import_errors', $result['errors']);  // Changed 'errors' to 'import_errors'
            }
            
            return redirect()->route('siswa.index')
                ->with('success', $message);
                
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Import error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('siswa.import')
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Download Excel template for student import.
     */
    public function downloadTemplate()
    {
        // Create a file in the storage path instead of a temporary file
        $storageDir = storage_path('app/public/templates');
        
        // Ensure directory exists
        if (!file_exists($storageDir)) {
            mkdir($storageDir, 0755, true);
        }
        
        $filePath = $storageDir . '/Template_Siswa.csv';
        
        // CSV header
        $header = [
            'nis', 'nama', 'tingkat', 'kelas', 'jurusan',
            'jenis_kelamin', 'tanggal_lahir', 'alamat', 'nomor_hp'
        ];
        
        // Example data rows
        $exampleRows = [
            [
                '12345678', 'Nama Siswa', 'X', 'A', 'TKJ',
                'L', '01/01/2008', 'Jl. Contoh No. 123', '081234567890'
            ],
            [
                '87654321', 'Siswa Contoh', 'XI', 'B', 'RPL',
                'P', '02/02/2007', 'Jl. Contoh No. 456', '089876543210'
            ]
        ];
        
        // Write CSV file
        $csv = fopen($filePath, 'w');
        
        // Add UTF-8 BOM to ensure Excel recognizes the file as UTF-8
        fwrite($csv, "\xEF\xBB\xBF");
        
        // Write header row
        fputcsv($csv, $header);
        
        // Write example rows
        foreach ($exampleRows as $row) {
            fputcsv($csv, $row);
        }
        
        fclose($csv);
        
        // Create response with the file
        return response()->download(
            $filePath, 
            'Template_Siswa.csv', 
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="Template_Siswa.csv"'
            ]
        );
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
