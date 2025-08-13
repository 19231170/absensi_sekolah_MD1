<?php

namespace App\Exports;

use App\Models\Absensi;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class FastExcelAbsensiExport
{
    /**
     * Export absensi data to Excel
     *
     * @param \Illuminate\Database\Eloquent\Collection $absensi
     * @param string $tanggal
     * @param int|null $jamSekolahId
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function toExcel($absensi, $tanggal, $jamSekolahId = null)
    {
        $fileName = 'Laporan_Absensi_' . Carbon::parse($tanggal)->format('Y-m-d') . '.xlsx';
        
        $data = $this->mapAbsensiData($absensi);
        
        $tempFile = tempnam(sys_get_temp_dir(), 'absensi_export');
        
        (new FastExcel($data))->export($tempFile);
        
        return response()->download($tempFile, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
    
    /**
     * Export absensi data to CSV
     *
     * @param \Illuminate\Database\Eloquent\Collection $absensi
     * @param string $tanggal
     * @param int|null $jamSekolahId
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function toCsv($absensi, $tanggal, $jamSekolahId = null)
    {
        $fileName = 'Laporan_Absensi_' . Carbon::parse($tanggal)->format('Y-m-d') . '.csv';
        
        $data = $this->mapAbsensiData($absensi);
        
        $tempFile = tempnam(sys_get_temp_dir(), 'absensi_export');
        
        (new FastExcel($data))->export($tempFile);
        
        return response()->download($tempFile, $fileName, [
            'Content-Type' => 'text/csv',
        ])->deleteFileAfterSend(true);
    }
    
    /**
     * Map absensi data for export
     *
     * @param \Illuminate\Database\Eloquent\Collection $absensi
     * @return \Illuminate\Support\Collection
     */
    protected function mapAbsensiData($absensi)
    {
        return $absensi->map(function ($item) {
            // Extract kelas and jurusan
            $kelas = isset($item->siswa) && isset($item->siswa->kelas) ? 
                $item->siswa->kelas->tingkat . ' ' . $item->siswa->kelas->nama : 'N/A';
                
            $jurusan = isset($item->siswa) && isset($item->siswa->kelas) && isset($item->siswa->kelas->jurusan) ? 
                $item->siswa->kelas->jurusan->nama : 'N/A';

            // Format timestamps for output
            $jamMasuk = $item->jam_masuk ? Carbon::parse($item->jam_masuk)->format('H:i') : '-';
            $jamKeluar = $item->jam_keluar ? Carbon::parse($item->jam_keluar)->format('H:i') : '-';
            $tanggal = Carbon::parse($item->tanggal)->format('d/m/Y');
            
            // Get sesi
            $sesi = isset($item->jamSekolah) ? $item->jamSekolah->nama : 'N/A';
            
            // Return formatted data for export
            return [
                'NIS' => $item->nis,
                'Nama Siswa' => isset($item->siswa) ? $item->siswa->nama : 'N/A',
                'Kelas' => $kelas,
                'Jurusan' => $jurusan,
                'Tanggal' => $tanggal,
                'Sesi' => $sesi,
                'Jam Masuk' => $jamMasuk,
                'Status Masuk' => $item->status_masuk,
                'Jam Keluar' => $jamKeluar,
                'Status Keluar' => $item->status_keluar,
                'Durasi' => $this->calculateDuration($item->jam_masuk, $item->jam_keluar),
            ];
        });
    }
    
    /**
     * Calculate duration between two timestamps
     *
     * @param string|null $start
     * @param string|null $end
     * @return string
     */
    protected function calculateDuration($start, $end)
    {
        if (!$start || !$end) {
            return '-';
        }
        
        $startTime = Carbon::parse($start);
        $endTime = Carbon::parse($end);
        
        $durationMinutes = $endTime->diffInMinutes($startTime);
        $hours = floor($durationMinutes / 60);
        $minutes = $durationMinutes % 60;
        
        return sprintf('%d jam %d menit', $hours, $minutes);
    }
}
