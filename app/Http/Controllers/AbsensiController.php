<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Siswa;
use App\Models\JamSekolah;
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

        $query = Absensi::with(['siswa.kelas.jurusan', 'jamSekolah'])
            ->whereDate('tanggal', $tanggal);

        if ($jamSekolahId) {
            $query->where('jam_sekolah_id', $jamSekolahId);
        }

        $absensi = $query->orderBy('jam_masuk', 'desc')->get();
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
