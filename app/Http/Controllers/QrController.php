<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class QrController extends Controller
{
    /**
     * Tampilkan daftar QR code siswa untuk testing
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        
        $query = Siswa::with(['kelas', 'kelas.jurusan'])->aktif();
        
        if ($search && trim($search) !== '') {
            // Simple search - hanya NIS dan nama untuk menghindari relationship issues
            $query->where(function($q) use ($search) {
                $q->where('nis', 'LIKE', '%' . trim($search) . '%')
                  ->orWhere('nama', 'LIKE', '%' . trim($search) . '%');
            });
        }
        
        $siswa = $query->orderBy('nama')->paginate(20);
        
        // Append parameter pencarian ke pagination
        $siswa->appends($request->query());
        
        return view('qr.index', compact('siswa', 'search'));
    }

    /**
     * Generate QR code untuk siswa tertentu
     */
    public function show($nis)
    {
        $siswa = Siswa::with('kelas.jurusan')->where('nis', $nis)->firstOrFail();
        return view('qr.show', compact('siswa'));
    }

    /**
     * Download QR code sebagai file PNG dengan frame berwarna
     */
    public function download($nis)
    {
        $siswa = Siswa::with('kelas.jurusan')->where('nis', $nis)->firstOrFail();
        
        // Generate QR code dengan frame berwarna untuk siswa
        $qrImageData = $this->generateQrWithColoredFrameData($siswa->qr_code, 'siswa', 400);
        
        // Set filename
        $filename = 'QR_' . $siswa->nis . '_' . str_replace(' ', '_', $siswa->nama) . '.png';

        return response($qrImageData)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Generate QR code image untuk preview dengan frame berwarna
     */
    public function image($nis)
    {
        $siswa = Siswa::where('nis', $nis)->firstOrFail();
        
        // Generate QR code dengan frame putih untuk siswa
        return $this->generateQrWithColoredFrame($siswa->qr_code, 'siswa', 200);
    }
    
    /**
     * Generate QR code dengan frame berwarna
     */
    private function generateQrWithColoredFrame($qrCode, $type, $size)
    {
        // Get standard QR code
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&bgcolor=FFFFFF&color=000000&data=" . urlencode($qrCode);
        $qrImageData = @file_get_contents($qrUrl);
        
        if (!$qrImageData) {
            return response('Failed to generate QR code', 500);
        }
        
        // Create colored frame
        $frameSize = $size + 60; // Add 30px padding on each side
        
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
        
        // Add white background for QR code
        $whiteColor = imagecolorallocate($canvas, 255, 255, 255);
        imagefilledrectangle($canvas, $x-5, $y-5, $x+$size+4, $y+$size+4, $whiteColor);
        
        // Add subtle border
        $borderColor = imagecolorallocate($canvas, 200, 200, 200);
        imagerectangle($canvas, $x-5, $y-5, $x+$size+4, $y+$size+4, $borderColor);
        
        // Place QR code on canvas
        imagecopy($canvas, $qrImage, $x, $y, 0, 0, $size, $size);
        
        // Output image
        ob_start();
        imagepng($canvas);
        $imageData = ob_get_contents();
        ob_end_clean();
        
        // Clean up memory
        imagedestroy($canvas);
        imagedestroy($qrImage);
        
        return response($imageData)
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'public, max-age=3600');
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

    /**
     * Generate QR code untuk semua siswa dalam ZIP dengan frame berwarna
     */
    public function downloadAll()
    {
        $siswa = Siswa::with('kelas.jurusan')->aktif()->get();
        
        if ($siswa->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data siswa untuk didownload.');
        }

        // Check if ZipArchive is available
        if (!class_exists('ZipArchive')) {
            // Alternative: Create a simple HTML page with all QR codes for download
            return $this->downloadAllAsHtml($siswa);
        }

        $zip = new \ZipArchive();
        $zipFileName = 'QR_Codes_All_Students_' . date('Y-m-d_H-i-s') . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);

        // Create temp directory if not exists
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        if ($zip->open($zipPath, \ZipArchive::CREATE) === TRUE) {
            foreach ($siswa as $s) {
                try {
                    // Generate QR code dengan frame abu-abu untuk siswa
                    $qrImageData = $this->generateQrWithColoredFrameData($s->qr_code, 'siswa', 300);
                    
                    // Add to ZIP
                    $filename = $s->kelas->tingkat . '_' . $s->kelas->nama_kelas . '_' . $s->nis . '_' . str_replace(' ', '_', $s->nama) . '.png';
                    $zip->addFromString($filename, $qrImageData);
                } catch (\Exception $e) {
                    // Skip this student if QR generation fails
                    continue;
                }
            }
            $zip->close();

            // Download and delete temp file
            return response()->download($zipPath)->deleteFileAfterSend(true);
        }

        return redirect()->back()->with('error', 'Gagal membuat file ZIP.');
    }

    /**
     * Alternative download method - HTML page with all QR codes dengan frame preview
     */
    private function downloadAllAsHtml($siswa)
    {
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>QR Codes - All Students</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .qr-container { 
            display: inline-block; 
            margin: 10px; 
            text-align: center; 
            background: #F5F5F5;
            padding: 20px; 
            border-radius: 10px;
        }
        .qr-inner {
            background: white;
            padding: 10px;
            border-radius: 5px;
            display: inline-block;
            border: 1px solid #ddd;
        }
        .qr-info { 
            margin-top: 10px; 
            font-size: 12px; 
            color: #333;
        }
        .qr-info strong { color: #333; }
        img { max-width: 200px; }
        .role-label {
            color: #666;
            font-weight: bold;
            margin-top: 5px;
        }
        @media print {
            .qr-container { break-inside: avoid; }
        }
    </style>
</head>
<body>
    <h1>QR Codes - Semua Siswa dengan Frame Abu-abu</h1>
    <p><strong>Generated:</strong> ' . date('d/m/Y H:i:s') . '</p>
    <p><strong>Total Siswa:</strong> ' . $siswa->count() . '</p>
    <hr>';
    
        foreach ($siswa as $s) {
            $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&bgcolor=FFFFFF&color=000000&data=' . urlencode($s->qr_code);
            
            $html .= '
    <div class="qr-container">
        <div class="qr-inner">
            <img src="' . $qrUrl . '" alt="QR Code ' . htmlspecialchars($s->nama) . '">
        </div>
        <div class="role-label">SISWA</div>
        <div class="qr-info">
            <strong>' . htmlspecialchars($s->nama) . '</strong><br>
            NIS: ' . $s->nis . '<br>
            Kelas: ' . htmlspecialchars($s->kelas->nama_lengkap) . '<br>
            QR: ' . $s->qr_code . '
        </div>
    </div>';
        }
        
        $html .= '
</body>
</html>';

        $fileName = 'QR_Codes_All_Students_' . date('Y-m-d_H-i-s') . '.html';
        
        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    /**
     * Download QR codes as PDF
     */
    public function downloadAllPdf()
    {
        $siswa = Siswa::with('kelas.jurusan')->aktif()->get();
        
        if ($siswa->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data siswa untuk didownload.');
        }

        $pdf = Pdf::loadView('qr.pdf', compact('siswa'))
                  ->setPaper('a4', 'portrait');
                  
        $fileName = 'QR_Codes_All_Students_' . date('Y-m-d_H-i-s') . '.pdf';
        
        return $pdf->download($fileName);
    }

    /**
     * Generate QR Code untuk Staff Authentication
     */
    public function generateStaffQr(Request $request)
    {
        $user = Auth::user();
        
        if (!$user || !in_array($user->role, ['admin', 'guru'])) {
            return redirect()->back()->with('error', 'Tidak memiliki akses untuk generate QR Code staff.');
        }

        // Generate QR code jika belum ada
        if (!$user->qr_code) {
            $qrCode = $user->role === 'admin' ? 'ADM' . str_pad($user->id, 3, '0', STR_PAD_LEFT) : 'GRU' . str_pad($user->id, 3, '0', STR_PAD_LEFT);
            $user->update(['qr_code' => $qrCode]);
        }

        return view('qr.staff', compact('user'));
    }

    /**
     * Download Staff QR Code dengan frame berwarna
     */
    public function downloadStaffQr(Request $request)
    {
        $user = Auth::user();
        
        if (!$user || !in_array($user->role, ['admin', 'guru'])) {
            abort(403, 'Unauthorized');
        }

        // Ensure QR code exists
        if (!$user->qr_code) {
            $qrCode = $user->role === 'admin' ? 'ADM' . str_pad($user->id, 3, '0', STR_PAD_LEFT) : 'GRU' . str_pad($user->id, 3, '0', STR_PAD_LEFT);
            $user->update(['qr_code' => $qrCode]);
        }

        try {
            // Generate QR code dengan frame berwarna
            $qrImageData = $this->generateQrWithColoredFrameData($user->qr_code, $user->role, 400);
            
            $fileName = 'QR_Staff_' . $user->role . '_' . $user->qr_code . '.png';
            
            return response($qrImageData)
                ->header('Content-Type', 'image/png')
                ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
                
        } catch (\Exception $e) {
            Log::error('Error generating staff QR: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal generate QR Code. Silakan coba lagi.');
        }
    }
}
