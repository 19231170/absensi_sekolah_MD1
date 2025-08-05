<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use Illuminate\Http\Request;

class QrController extends Controller
{
    /**
     * Tampilkan daftar QR code siswa untuk testing
     */
    public function index()
    {
        $siswa = Siswa::with('kelas.jurusan')->aktif()->get();
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
}
