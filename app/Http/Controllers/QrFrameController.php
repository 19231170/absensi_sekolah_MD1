<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use App\Models\Siswa;
use App\Models\User;

class QrFrameController extends Controller
{
    /**
     * Generate QR code with colored frame/background
     */
    public function generateQrWithFrame($type, $code, $size = 300)
    {
        // Get QR code image
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&bgcolor=FFFFFF&color=000000&data=" . urlencode($code);
        $qrImage = file_get_contents($qrUrl);
        
        if (!$qrImage) {
            return response('Failed to generate QR code', 500);
        }
        
        // Create frame with colored background
        $frameSize = $size + 100; // Add 50px padding on each side
        $frame = imagecreatetruecolor($frameSize, $frameSize);
        
        // Set background color based on type
        switch ($type) {
            case 'admin':
                $bgColor = imagecolorallocate($frame, 76, 175, 80); // Green #4CAF50
                break;
            case 'guru':
                $bgColor = imagecolorallocate($frame, 33, 150, 243); // Blue #2196F3
                break;
            case 'siswa':
            default:
                $bgColor = imagecolorallocate($frame, 255, 255, 255); // White #FFFFFF
                break;
        }
        
        // Fill background
        imagefill($frame, 0, 0, $bgColor);
        
        // Load QR code image
        $qr = imagecreatefromstring($qrImage);
        
        // Calculate center position
        $x = ($frameSize - $size) / 2;
        $y = ($frameSize - $size) / 2;
        
        // Place QR code on frame
        imagecopy($frame, $qr, $x, $y, 0, 0, $size, $size);
        
        // Add border/shadow effect
        $borderColor = imagecolorallocate($frame, 0, 0, 0);
        imagerectangle($frame, $x-2, $y-2, $x+$size+1, $y+$size+1, $borderColor);
        
        // Output image
        ob_start();
        imagepng($frame);
        $imageData = ob_get_contents();
        ob_end_clean();
        
        // Clean up memory
        imagedestroy($frame);
        imagedestroy($qr);
        
        return response($imageData)
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'public, max-age=3600');
    }
    
    /**
     * Generate QR with frame for student
     */
    public function studentQrFrame($nis, $size = 300)
    {
        $siswa = Siswa::where('nis', $nis)->firstOrFail();
        return $this->generateQrWithFrame('siswa', $siswa->qr_code, $size);
    }
    
    /**
     * Generate QR with frame for staff
     */
    public function staffQrFrame($userId, $size = 300)
    {
        $user = User::findOrFail($userId);
        $type = $user->role === 'admin' ? 'admin' : 'guru';
        return $this->generateQrWithFrame($type, $user->qr_code, $size);
    }
    
    /**
     * Generate HTML canvas-based QR with colored background
     */
    public function generateCanvasQr($type, $code, $size = 300)
    {
        $colors = [
            'admin' => ['bg' => '#4CAF50', 'text' => 'Admin'],
            'guru' => ['bg' => '#2196F3', 'text' => 'Guru'],
            'siswa' => ['bg' => '#FFFFFF', 'text' => 'Siswa']
        ];
        
        $config = $colors[$type] ?? $colors['siswa'];
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&bgcolor=FFFFFF&color=000000&data=" . urlencode($code);
        
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>QR Code - ' . $config['text'] . '</title>
            <style>
                body { margin: 0; padding: 20px; font-family: Arial, sans-serif; }
                .qr-container {
                    display: inline-block;
                    background: ' . $config['bg'] . ';
                    padding: 30px;
                    border-radius: 15px;
                    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
                    text-align: center;
                }
                .qr-image {
                    background: white;
                    padding: 15px;
                    border-radius: 10px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                }
                .qr-label {
                    color: ' . ($type === 'siswa' ? '#333' : 'white') . ';
                    font-weight: bold;
                    margin-top: 15px;
                    font-size: 18px;
                }
                .qr-code {
                    color: ' . ($type === 'siswa' ? '#666' : 'rgba(255,255,255,0.8)') . ';
                    font-size: 14px;
                    margin-top: 5px;
                    font-family: monospace;
                }
            </style>
        </head>
        <body>
            <div class="qr-container">
                <div class="qr-image">
                    <img src="' . $qrUrl . '" alt="QR Code">
                </div>
                <div class="qr-label">' . $config['text'] . '</div>
                <div class="qr-code">' . $code . '</div>
            </div>
        </body>
        </html>';
        
        return response($html)->header('Content-Type', 'text/html');
    }
}
