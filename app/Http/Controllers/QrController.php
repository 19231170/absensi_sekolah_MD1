<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class QrController extends Controller
{
    /**
     * Tampilkan daftar QR code siswa untuk testing
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        
        if ($search && trim($search) !== '') {
            // Simple search - hanya NIS dan nama untuk menghindari relationship issues
            $siswa = Siswa::with(['kelas', 'kelas.jurusan'])
                ->aktif()
                ->where(function($query) use ($search) {
                    $query->where('nis', 'LIKE', '%' . trim($search) . '%')
                          ->orWhere('nama', 'LIKE', '%' . trim($search) . '%');
                })
                ->orderBy('nama')
                ->get();
        } else {
            $siswa = Siswa::with(['kelas', 'kelas.jurusan'])->aktif()->orderBy('nama')->get();
        }
        
        return view('qr.index', compact('siswa'));
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
     * Download QR code sebagai file PNG menggunakan API online
     */
    public function download($nis)
    {
        $siswa = Siswa::with('kelas.jurusan')->where('nis', $nis)->firstOrFail();
        
        // Generate QR code using online API
        $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($siswa->qr_code);
        $qrCodeContent = file_get_contents($qrCodeUrl);

        // Set filename
        $filename = 'QR_' . $siswa->nis . '_' . str_replace(' ', '_', $siswa->nama) . '.png';

        return response($qrCodeContent)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Generate QR code image untuk preview
     */
    public function image($nis)
    {
        $siswa = Siswa::where('nis', $nis)->firstOrFail();
        
        // Generate QR code using online API
        $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($siswa->qr_code);
        $qrCodeContent = file_get_contents($qrCodeUrl);

        return response($qrCodeContent)
            ->header('Content-Type', 'image/png');
    }

    /**
     * Generate QR code untuk semua siswa dalam ZIP
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
                // Generate QR code using online API
                $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($s->qr_code);
                $qrCodeContent = @file_get_contents($qrCodeUrl);
                
                if ($qrCodeContent !== false) {
                    // Add to ZIP
                    $filename = $s->kelas->tingkat . '_' . $s->kelas->nama_kelas . '_' . $s->nis . '_' . str_replace(' ', '_', $s->nama) . '.png';
                    $zip->addFromString($filename, $qrCodeContent);
                }
            }
            $zip->close();

            // Download and delete temp file
            return response()->download($zipPath)->deleteFileAfterSend(true);
        }

        return redirect()->back()->with('error', 'Gagal membuat file ZIP.');
    }

    /**
     * Alternative download method - HTML page with all QR codes
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
            border: 1px solid #ddd; 
            padding: 15px; 
            border-radius: 8px;
        }
        .qr-info { margin-top: 10px; font-size: 12px; }
        .qr-info strong { color: #333; }
        img { max-width: 200px; }
        @media print {
            .qr-container { break-inside: avoid; }
        }
    </style>
</head>
<body>
    <h1>QR Codes - Semua Siswa</h1>
    <p><strong>Generated:</strong> ' . date('d/m/Y H:i:s') . '</p>
    <p><strong>Total Siswa:</strong> ' . $siswa->count() . '</p>
    <hr>';
    
        foreach ($siswa as $s) {
            $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($s->qr_code);
            
            $html .= '
    <div class="qr-container">
        <img src="' . $qrUrl . '" alt="QR Code ' . htmlspecialchars($s->nama) . '">
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
        $user = auth()->user();
        
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
     * Download Staff QR Code
     */
    public function downloadStaffQr(Request $request)
    {
        $user = auth()->user();
        
        if (!$user || !in_array($user->role, ['admin', 'guru'])) {
            abort(403, 'Unauthorized');
        }

        // Ensure QR code exists
        if (!$user->qr_code) {
            $qrCode = $user->role === 'admin' ? 'ADM' . str_pad($user->id, 3, '0', STR_PAD_LEFT) : 'GRU' . str_pad($user->id, 3, '0', STR_PAD_LEFT);
            $user->update(['qr_code' => $qrCode]);
        }

        try {
            $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=" . urlencode($user->qr_code);
            $qrImage = file_get_contents($qrUrl);
            
            if ($qrImage === false) {
                throw new \Exception('Failed to generate QR code image');
            }
            
            $fileName = 'QR_Staff_' . $user->role . '_' . $user->qr_code . '.png';
            
            return response($qrImage)
                ->header('Content-Type', 'image/png')
                ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
                
        } catch (\Exception $e) {
            Log::error('Error generating staff QR: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal generate QR Code. Silakan coba lagi.');
        }
    }
}
