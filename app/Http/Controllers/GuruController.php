<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class GuruController extends Controller
{
    /**
     * Display a listing of guru users with QR codes
     */
    public function index(Request $request)
    {
        $search = trim($request->get('search', ''));
        $guruQuery = User::guru();
        if ($search !== '') {
            $guruQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('nip', 'like', "%$search%")
                  ->orWhere('mata_pelajaran', 'like', "%$search%");
            });
        }
        $guru = $guruQuery->orderBy('name')->paginate(15)->withQueryString();
        return view('admin.guru.index', compact('guru', 'search'));
    }

    /** Show create form */
    public function create()
    {
        return view('admin.guru.create');
    }

    /** Store new guru */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'nip' => 'nullable|string|max:50|unique:users,nip',
            'mata_pelajaran' => 'nullable|string|max:100',
            'pin' => 'nullable|digits:4',
            'is_active' => 'nullable|boolean'
        ]);

        $data['role'] = 'guru';
        $data['password'] = Hash::make(Str::random(12));
        $data['is_active'] = $request->boolean('is_active');
        if (!empty($data['pin'])) {
            // store pin as plain (existing system) – consider hashing later
        }
        $user = User::create($data);

        return redirect()->route('guru.index')->with('success', 'Guru berhasil ditambahkan');
    }

    /** Edit form */
    public function edit(User $guru)
    {
        abort_unless($guru->isGuru(), 404);
        return view('admin.guru.edit', compact('guru'));
    }

    /** Update */
    public function update(Request $request, User $guru)
    {
        abort_unless($guru->isGuru(), 404);
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email,' . $guru->id,
            'nip' => 'nullable|string|max:50|unique:users,nip,' . $guru->id,
            'mata_pelajaran' => 'nullable|string|max:100',
            'pin' => 'nullable|digits:4',
            'is_active' => 'nullable|boolean'
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $guru->update($data);
        return redirect()->route('guru.index')->with('success', 'Data guru diperbarui');
    }

    /** Delete */
    public function destroy(User $guru)
    {
        abort_unless($guru->isGuru(), 404);
        $guru->delete();
        return redirect()->route('guru.index')->with('success', 'Guru dihapus');
    }

    /** Download single guru QR dengan frame berwarna */
    public function downloadQr(User $guru)
    {
        abort_unless($guru->isGuru(), 404);
        if (!$guru->qr_code) {
            $guru->update(['qr_code' => 'GRU' . str_pad($guru->id, 3, '0', STR_PAD_LEFT)]);
        }
        
        try {
            // Generate QR code dengan frame biru untuk guru
            $qrImageData = $this->generateQrWithColoredFrameData($guru->qr_code, 'guru', 400);
            
            $filename = 'QR_GURU_' . $guru->id . '_' . Str::slug($guru->name) . '.png';
            
            return response($qrImageData)
                ->header('Content-Type', 'image/png')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
                
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal generate QR Code: ' . $e->getMessage());
        }
    }

    /** Download all guru QR as ZIP dengan frame berwarna */
    public function downloadAllZip()
    {
        $guru = User::guru()->get();
        if ($guru->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada guru.');
        }
        if (!class_exists('ZipArchive')) {
            return $this->downloadAllHtml($guru);
        }
        $zip = new \ZipArchive();
        $fileName = 'QR_GURU_All_' . date('Y-m-d_H-i-s') . '.zip';
        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) mkdir($tempDir, 0755, true);
        $zipPath = $tempDir . '/' . $fileName;
        if ($zip->open($zipPath, \ZipArchive::CREATE) === true) {
            foreach ($guru as $g) {
                if (!$g->qr_code) {
                    $g->update(['qr_code' => 'GRU' . str_pad($g->id, 3, '0', STR_PAD_LEFT)]);
                }
                try {
                    // Generate QR dengan frame biru untuk guru
                    $img = $this->generateQrWithColoredFrameData($g->qr_code, 'guru', 300);
                    if ($img) {
                        $zip->addFromString(Str::slug($g->name) . '_' . $g->qr_code . '.png', $img);
                    }
                } catch (\Exception $e) {
                    // Skip this guru if QR generation fails
                    continue;
                }
            }
            $zip->close();
            return response()->download($zipPath)->deleteFileAfterSend(true);
        }
        return redirect()->back()->with('error', 'Gagal membuat ZIP');
    }

    /** Download all guru QR as PDF */
    public function downloadAllPdf()
    {
        $guru = User::guru()->get();
        if ($guru->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada guru.');
        }
        $pdf = Pdf::loadView('admin.guru.qr_pdf', compact('guru'))->setPaper('a4');
        return $pdf->download('QR_GURU_All_' . date('Y-m-d_H-i-s') . '.pdf');
    }

    /** HTML fallback dengan frame preview */
    private function downloadAllHtml($guru)
    {
        $html = '<html><head><meta charset="utf-8"><title>QR Guru</title><style>body{font-family:Arial} .item{display:inline-block;margin:10px;text-align:center;border:1px solid #ddd;padding:10px;border-radius:8px}</style></head><body><h3>QR Code Semua Guru dengan Frame Biru</h3>';
        foreach ($guru as $g) {
            if (!$g->qr_code) {
                $g->update(['qr_code' => 'GRU' . str_pad($g->id, 3, '0', STR_PAD_LEFT)]);
            }
            // Still use API for HTML preview (easier for web display)
            $url = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&bgcolor=FFFFFF&color=000000&data=' . urlencode($g->qr_code);
            $html .= '<div class="item" style="background: #2196F3; padding: 20px; border-radius: 10px;">
                        <div style="background: white; padding: 10px; border-radius: 5px; display: inline-block;">
                            <img src="' . $url . '">
                        </div>
                        <div style="color: white; margin-top: 10px;">
                            <strong>' . htmlspecialchars($g->name) . '</strong><br>
                            GURU<br>
                            ' . $g->qr_code . '
                        </div>
                      </div>';
        }
        $html .= '</body></html>';
        return response($html)->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'attachment; filename="QR_GURU_All_' . date('Y-m-d_H-i-s') . '.html"');
    }
    
    /**
     * Generate QR code data dengan frame berwarna (for download)
     */
    private function generateQrWithColoredFrameData($qrCode, $type, $size)
    {
        // Get standard QR code
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&bgcolor=FFFFFF&color=000000&data=" . urlencode($qrCode);
        $qrImageData = @file_get_contents($qrUrl);
        
        if (!$qrImageData) {
            throw new \Exception('Failed to generate QR code');
        }
        
        // Create colored frame
        $frameSize = $size + 100; // Add 50px padding on each side for downloads
        
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
                $bgColor = imagecolorallocate($canvas, 245, 245, 245); // Light gray #F5F5F5
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
        
        // Output image
        ob_start();
        imagepng($canvas);
        $imageData = ob_get_contents();
        ob_end_clean();
        
        // Clean up memory
        imagedestroy($canvas);
        imagedestroy($qrImage);
        
        return $imageData;
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
}
