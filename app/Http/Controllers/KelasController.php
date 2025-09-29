<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Jurusan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class KelasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Kelas::with('jurusan');
            
            // Add search functionality
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nama_kelas', 'LIKE', "%{$search}%")
                      ->orWhere('tingkat', 'LIKE', "%{$search}%")
                      ->orWhereHas('jurusan', function($jurusanQuery) use ($search) {
                          $jurusanQuery->where('nama_jurusan', 'LIKE', "%{$search}%");
                      });
                });
            }
            
            // Add jurusan filter
            if ($request->has('jurusan_id') && $request->jurusan_id) {
                $query->where('jurusan_id', $request->jurusan_id);
            }
            
            $kelas = $query->orderBy('tingkat')->orderBy('nama_kelas')->paginate(15);
            $jurusanList = \App\Models\Jurusan::orderBy('nama_jurusan')->get();
            
            return view('admin.kelas.index', compact('kelas', 'jurusanList'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memuat data kelas: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $jurusans = Jurusan::orderBy('nama_jurusan')->get();
        $tingkatOptions = [10, 11, 12, 13];
        return view('admin.kelas.create', compact('jurusans', 'tingkatOptions'));
    }

    /**
     * Store a newly created kelas in storage.
     */
    public function store(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'jurusan_id' => 'required|exists:jurusan,id',
            'tingkat' => 'required|integer|in:10,11,12,13',
            'nama_kelas' => 'required|string|max:255',
            'kapasitas' => 'nullable|integer|min:1|max:50',
            'keterangan' => 'nullable|string'
        ], [
            'jurusan_id.required' => 'Jurusan harus dipilih',
            'jurusan_id.exists' => 'Jurusan tidak valid',
            'tingkat.required' => 'Tingkat harus dipilih',
            'tingkat.in' => 'Tingkat harus antara 10-13',
            'nama_kelas.required' => 'Nama kelas harus diisi',
            'nama_kelas.max' => 'Nama kelas maksimal 255 karakter',
            'kapasitas.integer' => 'Kapasitas harus berupa angka',
            'kapasitas.min' => 'Kapasitas minimal 1',
            'kapasitas.max' => 'Kapasitas maksimal 50'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Check if kelas already exists
            $exists = Kelas::where('jurusan_id', $request->jurusan_id)
                          ->where('tingkat', $request->tingkat)
                          ->where('nama_kelas', $request->nama_kelas)
                          ->exists();

            if ($exists) {
                return redirect()->back()
                    ->with('error', 'Kelas dengan jurusan, tingkat, dan nama yang sama sudah ada.')
                    ->withInput();
            }

            $kelas = Kelas::create([
                'jurusan_id' => $request->jurusan_id,
                'tingkat' => $request->tingkat,
                'nama_kelas' => $request->nama_kelas,
                'kapasitas' => $request->kapasitas ?? 30,
                'keterangan' => $request->keterangan,
                'is_active' => true
            ]);

            return redirect()->route('kelas.index')
                ->with('success', 'Kelas berhasil ditambahkan.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menambahkan kelas: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Kelas $kelas)
    {
        try {
            $kelas->load(['siswa.kelas.jurusan', 'jurusan']);
            return view('admin.kelas.show', compact('kelas'));
        } catch (\Exception $e) {
            return redirect()->route('kelas.index')
                ->with('error', 'Gagal memuat detail kelas: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Kelas $kelas)
    {
        $jurusans = Jurusan::orderBy('nama_jurusan')->get();
        $tingkatOptions = [10, 11, 12, 13];
        return view('admin.kelas.edit', compact('kelas', 'jurusans', 'tingkatOptions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Kelas $kelas)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'jurusan_id' => 'required|exists:jurusan,id',
            'tingkat' => 'required|integer|in:10,11,12,13',
            'nama_kelas' => 'required|string|max:255',
            'kapasitas' => 'nullable|integer|min:1|max:50',
            'keterangan' => 'nullable|string',
            'is_active' => 'boolean'
        ], [
            'jurusan_id.required' => 'Jurusan harus dipilih',
            'jurusan_id.exists' => 'Jurusan tidak valid',
            'tingkat.required' => 'Tingkat harus dipilih',
            'tingkat.in' => 'Tingkat harus antara 10-13',
            'nama_kelas.required' => 'Nama kelas harus diisi',
            'nama_kelas.max' => 'Nama kelas maksimal 255 karakter',
            'kapasitas.integer' => 'Kapasitas harus berupa angka',
            'kapasitas.min' => 'Kapasitas minimal 1',
            'kapasitas.max' => 'Kapasitas maksimal 50'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Check if kelas already exists (exclude current kelas)
            $exists = Kelas::where('id', '!=', $kelas->id)
                          ->where('jurusan_id', $request->jurusan_id)
                          ->where('tingkat', $request->tingkat)
                          ->where('nama_kelas', $request->nama_kelas)
                          ->exists();

            if ($exists) {
                return redirect()->back()
                    ->with('error', 'Kelas dengan jurusan, tingkat, dan nama yang sama sudah ada.')
                    ->withInput();
            }

            $kelas->update([
                'jurusan_id' => $request->jurusan_id,
                'tingkat' => $request->tingkat,
                'nama_kelas' => $request->nama_kelas,
                'kapasitas' => $request->kapasitas ?? 30,
                'keterangan' => $request->keterangan,
                'is_active' => $request->has('is_active')
            ]);

            return redirect()->route('kelas.show', $kelas)
                ->with('success', 'Kelas berhasil diperbarui.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal memperbarui kelas: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kelas $kelas)
    {
        try {
            // Check if kelas has associated siswa
            $siswaCount = $kelas->siswa()->count();
            if ($siswaCount > 0) {
                return redirect()->route('kelas.index')
                    ->with('error', "Kelas tidak dapat dihapus karena masih memiliki {$siswaCount} siswa.");
            }

            $kelas->delete();
            return redirect()->route('kelas.index')
                ->with('success', 'Kelas berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('kelas.index')
                ->with('error', 'Gagal menghapus kelas: ' . $e->getMessage());
        }
    }

    /**
     * Get all jurusan for dropdown
     */
    public function getJurusan()
    {
        try {
            $jurusan = Jurusan::where('is_active', true)
                             ->orderBy('nama_jurusan')
                             ->get(['id', 'nama_jurusan']);

            return response()->json([
                'success' => true,
                'jurusan' => $jurusan
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data jurusan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download QR codes for all students in a class as ZIP
     */
    public function downloadQrCodes(Kelas $kelas)
    {
        try {
            $siswaList = $kelas->siswa()->where('status_aktif', true)->get();
            
            if ($siswaList->isEmpty()) {
                return redirect()->back()->with('error', 'Tidak ada siswa aktif di kelas ini untuk diunduh QR code-nya.');
            }

            // Filter siswa yang memiliki QR code
            $siswaWithQr = $siswaList->filter(function($siswa) {
                return !empty($siswa->qr_code);
            });

            if ($siswaWithQr->isEmpty()) {
                return redirect()->back()->with('error', 'Tidak ada siswa dengan QR code di kelas ini.');
            }

            // Check if ZIP extension is available
            if (!class_exists('ZipArchive')) {
                // Use JavaScript fallback instead of HTML
                return $this->downloadQrCodesJs($kelas);
            }

            $zip = new \ZipArchive();
            $fileName = "QR_Siswa_Kelas_{$kelas->tingkat}_{$kelas->nama_kelas}_" . date('Y-m-d_H-i-s') . '.zip';
            $tempDir = storage_path('app/temp');
            
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            
            $zipPath = $tempDir . '/' . $fileName;
            
            if ($zip->open($zipPath, \ZipArchive::CREATE) === true) {
                $successCount = 0;
                
                foreach ($siswaWithQr as $siswa) {
                    try {
                        // Generate QR dengan frame berwarna untuk siswa
                        $qrImageData = $this->generateQrWithColoredFrameData($siswa->qr_code, 'siswa', $siswa->nama, 300);
                        
                        if ($qrImageData) {
                            $fileName = "QR_{$siswa->nis}_{$siswa->nama}.png";
                            $fileName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $fileName);
                            $zip->addFromString($fileName, $qrImageData);
                            $successCount++;
                        }
                        
                    } catch (\Exception $e) {
                        \Log::warning("Gagal membuat QR untuk siswa {$siswa->nis}: " . $e->getMessage());
                        continue;
                    }
                }
                
                $zip->close();
                
                if ($successCount === 0) {
                    if (file_exists($zipPath)) unlink($zipPath);
                    return redirect()->back()->with('error', 'Gagal membuat QR code untuk semua siswa.');
                }
                
                return response()->download($zipPath)->deleteFileAfterSend(true);
            }
            
            return redirect()->back()->with('error', 'Gagal membuat file ZIP');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengunduh QR codes: ' . $e->getMessage());
        }
    }

    /**
     * Download individual QR code for a student
     */
    public function downloadStudentQr($kelasId, $siswaNis)
    {
        try {
            $kelas = Kelas::findOrFail($kelasId);
            $siswa = $kelas->siswa()->where('nis', $siswaNis)->firstOrFail();
            
            if (!$siswa->qr_code) {
                return redirect()->back()->with('error', 'QR code tidak tersedia untuk siswa ini.');
            }

            // Generate QR code dengan frame berwarna
            $qrImageData = $this->generateQrWithColoredFrameData($siswa->qr_code, 'siswa', $siswa->nama, 400);
            
            $fileName = "QR_{$siswa->nis}_{$siswa->nama}.png";
            $fileName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $fileName);
            
            return Response::make($qrImageData, 200, [
                'Content-Type' => 'image/png',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"'
            ]);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengunduh QR code: ' . $e->getMessage());
        }
    }

    /**
     * Show QR codes page for printing/saving manually
     */
    public function showQrCodes(Kelas $kelas)
    {
        try {
            $siswaList = $kelas->siswa()->where('status_aktif', true)->get();
            
            if ($siswaList->isEmpty()) {
                return redirect()->back()->with('error', 'Tidak ada siswa aktif di kelas ini.');
            }

            return view('admin.kelas.qr-codes', compact('kelas', 'siswaList'));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memuat QR codes: ' . $e->getMessage());
        }
    }

    /**
     * Helper method to clean up temporary directory
     */
    private function cleanupTempDir($tempDir)
    {
        if (file_exists($tempDir)) {
            $files = glob($tempDir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($tempDir);
        }
    }

    /**
     * Generate QR code data dengan frame berwarna (similar to GuruController)
     */
    private function generateQrWithColoredFrameData($qrCode, $type, $name, $size)
    {
        try {
            // Get standard QR code from API
            $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&bgcolor=FFFFFF&color=000000&data=" . urlencode($qrCode);
            $qrImageData = @file_get_contents($qrUrl);
            
            if (!$qrImageData) {
                throw new \Exception('Failed to generate QR code from API');
            }
            
            // Create colored frame
            $frameSize = $size + 100; // Add 50px padding on each side
            
            // Create image canvas
            $canvas = imagecreatetruecolor($frameSize, $frameSize);
            
            // Set frame colors based on type
            switch ($type) {
                case 'admin':
                    $bgColor = imagecolorallocate($canvas, 76, 175, 80); // Green #4CAF50
                    break;
                case 'guru':
                    $bgColor = imagecolorallocate($canvas, 33, 150, 243); // Blue #2196F3
                    break;
                case 'siswa':
                default:
                    $bgColor = imagecolorallocate($canvas, 156, 39, 176); // Purple #9C27B0
                    break;
            }
            
            // Fill background with color
            imagefill($canvas, 0, 0, $bgColor);
            
            // Load QR code image
            $qrImage = imagecreatefromstring($qrImageData);
            
            // Calculate center position
            $x = ($frameSize - $size) / 2;
            $y = ($frameSize - $size) / 2;
            
            // Add white background for QR code with extra padding
            $whiteColor = imagecolorallocate($canvas, 255, 255, 255);
            imagefilledrectangle($canvas, $x-10, $y-10, $x+$size+9, $y+$size+9, $whiteColor);
            
            // Add subtle border
            $borderColor = imagecolorallocate($canvas, 180, 180, 180);
            imagerectangle($canvas, $x-10, $y-10, $x+$size+9, $y+$size+9, $borderColor);
            
            // Place QR code on canvas
            imagecopy($canvas, $qrImage, $x, $y, 0, 0, $size, $size);
            
            // Add role label at bottom
            $this->addRoleLabel($canvas, $frameSize, $type);
            
            // Add name label at top (for siswa)
            if ($type === 'siswa' && !empty($name)) {
                $this->addNameLabel($canvas, $frameSize, $name);
            }
            
            // Output image
            ob_start();
            imagepng($canvas);
            $imageData = ob_get_contents();
            ob_end_clean();
            
            // Clean up memory
            imagedestroy($canvas);
            imagedestroy($qrImage);
            
            return $imageData;
            
        } catch (\Exception $e) {
            // Fallback to simple QR generation using Endroid
            try {
                $qrCode = QrCode::create($qrCode)
                    ->setSize($size)
                    ->setMargin(10);
                
                $writer = new PngWriter();
                $result = $writer->write($qrCode);
                
                return $result->getString();
            } catch (\Exception $endroidError) {
                throw new \Exception('Failed to generate QR code: ' . $e->getMessage() . ' | Fallback error: ' . $endroidError->getMessage());
            }
        }
    }

    /**
     * Add role label to QR code frame
     */
    private function addRoleLabel($canvas, $frameSize, $type)
    {
        // Set text color to white
        $textColor = imagecolorallocate($canvas, 255, 255, 255);
        
        // Role labels
        $labels = [
            'admin' => 'ADMIN',
            'guru' => 'GURU', 
            'siswa' => 'SISWA'
        ];
        
        $label = $labels[$type] ?? 'USER';
        
        // Use built-in font (font 5 is the largest built-in font)
        $font = 5;
        $textWidth = imagefontwidth($font) * strlen($label);
        $textHeight = imagefontheight($font);
        
        // Calculate center position for text
        $x = ($frameSize - $textWidth) / 2;
        $y = $frameSize - 25; // 25px from bottom
        
        // Add text shadow
        $shadowColor = imagecolorallocate($canvas, 0, 0, 0);
        imagestring($canvas, $font, $x+1, $y+1, $label, $shadowColor);
        
        // Add main text
        imagestring($canvas, $font, $x, $y, $label, $textColor);
    }

    /**
     * Add name label to QR code frame (for students)
     */
    private function addNameLabel($canvas, $frameSize, $name)
    {
        // Set text color to white
        $textColor = imagecolorallocate($canvas, 255, 255, 255);
        
        // Truncate name if too long
        $displayName = strlen($name) > 20 ? substr($name, 0, 17) . '...' : $name;
        
        // Use built-in font
        $font = 3;
        $textWidth = imagefontwidth($font) * strlen($displayName);
        
        // Calculate center position for text
        $x = ($frameSize - $textWidth) / 2;
        $y = 15; // 15px from top
        
        // Add text shadow
        $shadowColor = imagecolorallocate($canvas, 0, 0, 0);
        imagestring($canvas, $font, $x+1, $y+1, $displayName, $shadowColor);
        
        // Add main text
        imagestring($canvas, $font, $x, $y, $displayName, $textColor);
    }

    /**
     * HTML fallback when ZIP extension is not available
     */
    private function downloadQrCodesHtml(Kelas $kelas, $siswaWithQr)
    {
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>QR Code Siswa Kelas ' . $kelas->tingkat . ' ' . $kelas->nama_kelas . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
        .qr-item { 
            border: 1px solid #ddd; 
            border-radius: 10px; 
            padding: 15px; 
            text-align: center;
            background: linear-gradient(135deg, #9C27B0, #673AB7);
            color: white;
        }
        .qr-container { 
            background: white; 
            padding: 15px; 
            border-radius: 8px; 
            margin: 10px 0;
            display: inline-block;
        }
        .student-name { font-weight: bold; margin-bottom: 5px; }
        .student-nis { font-size: 0.9em; opacity: 0.9; }
        .print-btn { 
            position: fixed; 
            top: 20px; 
            right: 20px; 
            padding: 10px 20px; 
            background: #2196F3; 
            color: white; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer;
        }
        @media print {
            .print-btn { display: none; }
            body { margin: 0; }
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">🖨️ Print/Save as PDF</button>
    
    <div class="header">
        <h2>QR Code Siswa</h2>
        <h3>Kelas ' . $kelas->tingkat . ' ' . $kelas->nama_kelas . '</h3>
        <p>Jurusan: ' . ($kelas->jurusan ? $kelas->jurusan->nama_jurusan : 'Unknown') . '</p>
        <p><em>ZIP extension tidak tersedia. Gunakan Print/Save as PDF untuk menyimpan.</em></p>
    </div>
    
    <div class="grid">';

        foreach ($siswaWithQr as $siswa) {
            $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&bgcolor=FFFFFF&color=000000&data=' . urlencode($siswa->qr_code);
            
            $html .= '
            <div class="qr-item">
                <div class="student-name">' . htmlspecialchars($siswa->nama) . '</div>
                <div class="student-nis">NIS: ' . htmlspecialchars($siswa->nis) . '</div>
                <div class="qr-container">
                    <img src="' . $qrUrl . '" alt="QR Code ' . htmlspecialchars($siswa->nis) . '">
                </div>
                <div style="font-size: 0.8em; margin-top: 5px;">SISWA</div>
            </div>';
        }

        $html .= '
    </div>
    
    <script>
        // Auto-focus for better print experience
        window.onload = function() {
            document.title = "QR_Siswa_Kelas_' . $kelas->tingkat . '_' . $kelas->nama_kelas . '_" + new Date().toISOString().slice(0,10);
        };
    </script>
</body>
</html>';

        return response($html)
            ->header('Content-Type', 'text/html; charset=utf-8')
            ->header('Content-Disposition', 'inline; filename="QR_Siswa_Kelas_' . $kelas->tingkat . '_' . $kelas->nama_kelas . '.html"');
    }

    /**
     * JavaScript-based ZIP download for single class (no PHP ZIP extension needed)
     */
    public function downloadQrCodesJs(Kelas $kelas)
    {
        try {
            $siswaList = $kelas->siswa()->where('status_aktif', true)->get();
            
            if ($siswaList->isEmpty()) {
                return redirect()->back()->with('error', 'Tidak ada siswa aktif di kelas ini.');
            }

            $siswaWithQr = $siswaList->filter(function($siswa) {
                return !empty($siswa->qr_code);
            });

            if ($siswaWithQr->isEmpty()) {
                return redirect()->back()->with('error', 'Tidak ada siswa dengan QR code di kelas ini.');
            }

            // Prepare data for JavaScript ZIP creation
            $qrData = [];
            foreach ($siswaWithQr as $siswa) {
                try {
                    // Generate QR dengan frame berwarna untuk siswa
                    $qrImageData = $this->generateQrWithColoredFrameData($siswa->qr_code, 'siswa', $siswa->nama, 300);
                    
                    if ($qrImageData) {
                        $fileName = "QR_{$siswa->nis}_{$siswa->nama}.png";
                        $fileName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $fileName);
                        
                        $qrData[] = [
                            'filename' => $fileName,
                            'data' => 'data:image/png;base64,' . base64_encode($qrImageData),
                            'siswa' => $siswa->nama,
                            'nis' => $siswa->nis
                        ];
                    }
                    
                } catch (\Exception $e) {
                    \Log::warning("Gagal membuat QR untuk siswa {$siswa->nis}: " . $e->getMessage());
                    continue;
                }
            }

            if (empty($qrData)) {
                return redirect()->back()->with('error', 'Gagal membuat QR code untuk siswa di kelas ini.');
            }

            // Return view with JavaScript ZIP functionality
            return view('admin.kelas.download_zip_js', [
                'qrData' => $qrData,
                'filename' => "QR_Siswa_Kelas_{$kelas->tingkat}_{$kelas->nama_kelas}_" . date('Y-m-d_H-i-s') . '.zip',
                'kelasInfo' => [
                    'tingkat' => $kelas->tingkat,
                    'nama_kelas' => $kelas->nama_kelas,
                    'jurusan' => $kelas->jurusan ? $kelas->jurusan->nama_jurusan : 'Unknown'
                ]
            ]);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memuat QR codes: ' . $e->getMessage());
        }
    }

    /**
     * Get QR codes data as JSON for JavaScript processing
     */
    public function getQrCodesData($kelasId)
    {
        try {
            \Log::info('getQrCodesData called', ['kelas_id' => $kelasId]);
            
            $kelas = Kelas::findOrFail($kelasId);
            \Log::info('Kelas found', ['kelas' => $kelas->nama_kelas]);
            
            $siswaList = $kelas->siswa()->where('status_aktif', true)->get();
            \Log::info('Siswa loaded', ['count' => $siswaList->count()]);
            
            $qrData = [];
            $successCount = 0;
            $errorCount = 0;
            
            foreach ($siswaList as $siswa) {
                if (!empty($siswa->qr_code)) {
                    try {
                        // Generate QR code dengan frame berwarna
                        $qrImageData = $this->generateQrWithColoredFrameData($siswa->qr_code, 'siswa', $siswa->nama, 300);
                        
                        $fileName = "QR_{$siswa->nis}_{$siswa->nama}.png";
                        $fileName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $fileName);
                        
                        $qrData[] = [
                            'filename' => $fileName,
                            'data' => base64_encode($qrImageData),
                            'nis' => $siswa->nis,
                            'nama' => $siswa->nama
                        ];
                        $successCount++;
                        
                    } catch (\Exception $qrError) {
                        \Log::warning('Failed to generate QR for siswa', [
                            'nis' => $siswa->nis,
                            'error' => $qrError->getMessage()
                        ]);
                        $errorCount++;
                    }
                }
            }

            \Log::info('QR generation completed', [
                'success_count' => $successCount,
                'error_count' => $errorCount
            ]);

            return response()->json([
                'success' => true,
                'data' => $qrData,
                'stats' => [
                    'total_siswa' => $siswaList->count(),
                    'qr_generated' => $successCount,
                    'errors' => $errorCount
                ],
                'kelas_info' => [
                    'tingkat' => $kelas->tingkat,
                    'nama_kelas' => $kelas->nama_kelas,
                    'jurusan' => $kelas->jurusan ? $kelas->jurusan->nama_jurusan : 'Unknown'
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('getQrCodesData error', [
                'kelas_id' => $kelas->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data QR: ' . $e->getMessage(),
                'debug_info' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }

    /**
     * Alternative: Multiple individual downloads with auto-trigger
     */
    public function downloadMultipleQr(Kelas $kelas)
    {
        try {
            $siswaList = $kelas->siswa()->where('status_aktif', true)->get();
            
            $siswaWithQr = $siswaList->filter(function($siswa) {
                return !empty($siswa->qr_code);
            });

            if ($siswaWithQr->isEmpty()) {
                return redirect()->back()->with('error', 'Tidak ada siswa dengan QR code di kelas ini.');
            }

            return view('admin.kelas.download-multiple-qr', compact('kelas', 'siswaWithQr'));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memuat data: ' . $e->getMessage());
        }
    }

    /**
     * Download all students QR codes from all classes as ZIP (similar to GuruController)
     */
    public function downloadAllSiswaZip()
    {
        try {
            $siswaList = Siswa::with('kelas.jurusan')->where('status_aktif', true)->get();
            
            if ($siswaList->isEmpty()) {
                return redirect()->back()->with('error', 'Tidak ada siswa aktif.');
            }

            // Filter siswa yang memiliki QR code
            $siswaWithQr = $siswaList->filter(function($siswa) {
                return !empty($siswa->qr_code);
            });

            if ($siswaWithQr->isEmpty()) {
                return redirect()->back()->with('error', 'Tidak ada siswa dengan QR code.');
            }

            // Check if ZIP extension is available
            if (!class_exists('ZipArchive')) {
                // Instead of HTML fallback, return JSON data for JavaScript ZIP creation
                return $this->downloadAllSiswaJs($siswaWithQr);
            }

            $zip = new \ZipArchive();
            $fileName = 'QR_Siswa_All_' . date('Y-m-d_H-i-s') . '.zip';
            $tempDir = storage_path('app/temp');
            
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            
            $zipPath = $tempDir . '/' . $fileName;
            
            if ($zip->open($zipPath, \ZipArchive::CREATE) === true) {
                $successCount = 0;
                
                foreach ($siswaWithQr as $siswa) {
                    try {
                        // Generate QR dengan frame berwarna untuk siswa
                        $qrImageData = $this->generateQrWithColoredFrameData($siswa->qr_code, 'siswa', $siswa->nama, 300);
                        
                        if ($qrImageData) {
                            $kelasInfo = $siswa->kelas ? "{$siswa->kelas->tingkat}_{$siswa->kelas->nama_kelas}" : "NoClass";
                            $fileName = "{$kelasInfo}_QR_{$siswa->nis}_{$siswa->nama}.png";
                            $fileName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $fileName);
                            $zip->addFromString($fileName, $qrImageData);
                            $successCount++;
                        }
                        
                    } catch (\Exception $e) {
                        \Log::warning("Gagal membuat QR untuk siswa {$siswa->nis}: " . $e->getMessage());
                        continue;
                    }
                }
                
                $zip->close();
                
                if ($successCount === 0) {
                    if (file_exists($zipPath)) unlink($zipPath);
                    return redirect()->back()->with('error', 'Gagal membuat QR code untuk semua siswa.');
                }
                
                return response()->download($zipPath)->deleteFileAfterSend(true);
            }
            
            return redirect()->back()->with('error', 'Gagal membuat file ZIP');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengunduh QR codes: ' . $e->getMessage());
        }
    }

    /**
     * Download all students QR codes as PDF
     */
    public function downloadAllSiswaPdf()
    {
        try {
            $siswaList = Siswa::with('kelas.jurusan')->where('status_aktif', true)->get();
            
            // Filter siswa yang memiliki QR code
            $siswaWithQr = $siswaList->filter(function($siswa) {
                return !empty($siswa->qr_code);
            });

            if ($siswaWithQr->isEmpty()) {
                return redirect()->back()->with('error', 'Tidak ada siswa dengan QR code.');
            }

            // Group by class for better organization
            $siswaByKelas = $siswaWithQr->groupBy(function($siswa) {
                return $siswa->kelas ? "{$siswa->kelas->tingkat} {$siswa->kelas->nama_kelas}" : 'No Class';
            });

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.kelas.qr_pdf_all', compact('siswaByKelas'))->setPaper('a4');
            return $pdf->download('QR_Siswa_All_' . date('Y-m-d_H-i-s') . '.pdf');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengunduh PDF: ' . $e->getMessage());
        }
    }

    /**
     * HTML fallback for all students when ZIP extension is not available
     */
    private function downloadAllSiswaHtml($siswaWithQr)
    {
        // Group by class for better organization
        $siswaByKelas = $siswaWithQr->groupBy(function($siswa) {
            return $siswa->kelas ? "{$siswa->kelas->tingkat} {$siswa->kelas->nama_kelas}" : 'No Class';
        });

        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>QR Code Semua Siswa</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .class-section { margin-bottom: 40px; }
        .class-title { 
            background: #9C27B0; 
            color: white; 
            padding: 10px; 
            margin-bottom: 20px; 
            border-radius: 5px;
            text-align: center;
        }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
        .qr-item { 
            border: 1px solid #ddd; 
            border-radius: 10px; 
            padding: 15px; 
            text-align: center;
            background: linear-gradient(135deg, #9C27B0, #673AB7);
            color: white;
        }
        .qr-container { 
            background: white; 
            padding: 15px; 
            border-radius: 8px; 
            margin: 10px 0;
            display: inline-block;
        }
        .student-name { font-weight: bold; margin-bottom: 5px; }
        .student-nis { font-size: 0.9em; opacity: 0.9; }
        .print-btn { 
            position: fixed; 
            top: 20px; 
            right: 20px; 
            padding: 10px 20px; 
            background: #2196F3; 
            color: white; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer;
        }
        @media print {
            .print-btn { display: none; }
            body { margin: 0; }
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">🖨️ Print/Save as PDF</button>
    
    <div class="header">
        <h2>QR Code Semua Siswa</h2>
        <p>Total: ' . $siswaWithQr->count() . ' siswa dengan QR code</p>
        <p><em>ZIP extension tidak tersedia. Gunakan Print/Save as PDF untuk menyimpan.</em></p>
    </div>';

        foreach ($siswaByKelas as $kelasName => $siswasInKelas) {
            $html .= '<div class="class-section">
                <h3 class="class-title">Kelas: ' . htmlspecialchars($kelasName) . ' (' . $siswasInKelas->count() . ' siswa)</h3>
                <div class="grid">';

            foreach ($siswasInKelas as $siswa) {
                $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&bgcolor=FFFFFF&color=000000&data=' . urlencode($siswa->qr_code);
                
                $html .= '
                <div class="qr-item">
                    <div class="student-name">' . htmlspecialchars($siswa->nama) . '</div>
                    <div class="student-nis">NIS: ' . htmlspecialchars($siswa->nis) . '</div>
                    <div class="qr-container">
                        <img src="' . $qrUrl . '" alt="QR Code ' . htmlspecialchars($siswa->nis) . '">
                    </div>
                    <div style="font-size: 0.8em; margin-top: 5px;">SISWA</div>
                </div>';
            }

            $html .= '</div></div>';
        }

        $html .= '
    <script>
        window.onload = function() {
            document.title = "QR_Siswa_All_" + new Date().toISOString().slice(0,10);
        };
    </script>
</body>
</html>';

        return response($html)
            ->header('Content-Type', 'text/html; charset=utf-8')
            ->header('Content-Disposition', 'inline; filename="QR_Siswa_All.html"');
    }

    /**
     * JavaScript-based ZIP download when ZipArchive is not available
     */
    private function downloadAllSiswaJs($siswaWithQr)
    {
        // Prepare data for JavaScript ZIP creation
        $qrData = [];
        foreach ($siswaWithQr as $siswa) {
            try {
                // Generate QR dengan frame berwarna untuk siswa
                $qrImageData = $this->generateQrWithColoredFrameData($siswa->qr_code, 'siswa', $siswa->nama, 300);
                
                if ($qrImageData) {
                    $kelasInfo = $siswa->kelas ? "{$siswa->kelas->tingkat}_{$siswa->kelas->nama_kelas}" : "NoClass";
                    $fileName = "{$kelasInfo}_QR_{$siswa->nis}_{$siswa->nama}.png";
                    $fileName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $fileName);
                    
                    $qrData[] = [
                        'filename' => $fileName,
                        'data' => 'data:image/png;base64,' . base64_encode($qrImageData),
                        'siswa' => $siswa->nama,
                        'nis' => $siswa->nis,
                        'kelas' => $kelasInfo
                    ];
                }
                
            } catch (\Exception $e) {
                \Log::warning("Gagal membuat QR untuk siswa {$siswa->nis}: " . $e->getMessage());
                continue;
            }
        }

        if (empty($qrData)) {
            return redirect()->back()->with('error', 'Gagal membuat QR code untuk semua siswa.');
        }

        // Return view with JavaScript ZIP functionality
        return view('admin.kelas.download_zip_js', [
            'qrData' => $qrData,
            'filename' => 'QR_Siswa_All_' . date('Y-m-d_H-i-s') . '.zip'
        ]);
    }
}
