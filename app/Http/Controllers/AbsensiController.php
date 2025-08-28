<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\AbsensiPelajaran;
use App\Models\Siswa;
use App\Models\JamSekolah;
use App\Models\JadwalKelas;
use App\Exports\FastExcelAbsensiExport;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class AbsensiController extends Controller
{
    /**
     * Tampilkan laporan absensi
     */
    public function laporan(Request $request)
    {
        $tanggal = $request->get('tanggal', Carbon::today('Asia/Jakarta'));
        $jamSekolahId = $request->get('jam_sekolah_id');
        $export = $request->get('export');

        // Query untuk absensi lama (per sesi)
        $queryAbsensiLama = Absensi::with(['siswa.kelas.jurusan', 'jamSekolah'])
            ->whereDate('tanggal', $tanggal);

        if ($jamSekolahId) {
            $queryAbsensiLama->where('jam_sekolah_id', $jamSekolahId);
        }

        $absensiLama = $queryAbsensiLama->orderBy('jam_masuk', 'desc')->get();

        // Query untuk absensi baru (per pelajaran)
        $queryAbsensiPelajaran = AbsensiPelajaran::with(['siswa.kelas.jurusan', 'jadwalKelas'])
            ->whereDate('tanggal', $tanggal);

        $absensiPelajaran = $queryAbsensiPelajaran->orderBy('jam_masuk', 'desc')->get();

        // Gabungkan kedua data absensi
        $absensi = collect();
        
                // Add old absensi data
        foreach ($absensiLama as $item) {
            $absensi->push([
                'type' => 'sesi',
                'nis' => $item->siswa ? $item->siswa->nis : 'N/A',
                'nama' => $item->siswa ? $item->siswa->nama : 'N/A',
                'kelas' => $item->siswa && $item->siswa->kelas ? 
                    "{$item->siswa->kelas->tingkat} {$item->siswa->kelas->nama_kelas}" : 'N/A',
                'jurusan' => $item->siswa && $item->siswa->kelas && $item->siswa->kelas->jurusan ? 
                    $item->siswa->kelas->jurusan->nama_jurusan : 'N/A',
                'sesi' => $item->jamSekolah ? $item->jamSekolah->nama_sesi : 'N/A',
                'mata_pelajaran' => '-',
                'guru_pengampu' => '-',
                'jam_masuk' => $item->jam_masuk,
                'jam_keluar' => $item->jam_keluar,
                'status' => $item->status_masuk ?: 'N/A',
                'keterangan' => $item->keterangan ?: '-'
            ]);
        }

        // Add new absensi pelajaran data
        foreach ($absensiPelajaran as $item) {
            $absensi->push([
                'type' => 'pelajaran',
                'nis' => $item->siswa ? $item->siswa->nis : 'N/A',
                'nama' => $item->siswa ? $item->siswa->nama : 'N/A',
                'kelas' => $item->siswa && $item->siswa->kelas ? 
                    "{$item->siswa->kelas->tingkat} {$item->siswa->kelas->nama_kelas}" : 'N/A',
                'jurusan' => $item->siswa && $item->siswa->kelas && $item->siswa->kelas->jurusan ? 
                    $item->siswa->kelas->jurusan->nama_jurusan : 'N/A',
                'sesi' => $item->jadwalKelas ? $item->jadwalKelas->mata_pelajaran : 'N/A',
                'mata_pelajaran' => $item->jadwalKelas ? $item->jadwalKelas->mata_pelajaran : 'N/A',
                'guru_pengampu' => $item->jadwalKelas ? $item->jadwalKelas->guru_pengampu : 'N/A',
                'jam_masuk' => $item->jam_masuk,
                'jam_keluar' => $item->jam_keluar,
                'status' => $item->status_masuk ?: 'N/A',
                'keterangan' => $item->keterangan ?: '-'
            ]);
        }

        // Sort by jam_masuk descending
        $absensi = $absensi->sortByDesc('jam_masuk');

        $jamSekolah = JamSekolah::aktif()->get();

        // Handle export requests
        if ($export === 'excel') {
            return $this->exportExcel($absensi, $tanggal, $jamSekolahId);
        }
        
        if ($export === 'pdf') {
            return $this->exportPdf($absensi, $tanggal, $jamSekolahId);
        }

        return view('absensi.laporan', compact('absensi', 'jamSekolah', 'tanggal', 'jamSekolahId'));
    }

    /**
     * Export laporan ke Excel (HTML format)
     */
    private function exportExcel($absensi, $tanggal, $jamSekolahId)
    {
        // Use the new FastExcelAbsensiExport
        $export = new \App\Exports\FastExcelAbsensiExport();
        return $export->toExcel($absensi, $tanggal, $jamSekolahId);
    }

    /**
     * Export laporan ke PDF
     */
    private function exportPdf($absensi, $tanggal, $jamSekolahId)
    {
        $fileName = 'Laporan_Absensi_' . Carbon::parse($tanggal)->format('Y-m-d') . '.pdf';
        
        $jamSekolah = JamSekolah::aktif()->get();
        $selectedSesi = $jamSekolahId ? $jamSekolah->find($jamSekolahId) : null;
        
        $pdf = Pdf::loadView('absensi.laporan-pdf', compact('absensi', 'tanggal', 'selectedSesi'));
        
        return $pdf->download($fileName);
    }
}
