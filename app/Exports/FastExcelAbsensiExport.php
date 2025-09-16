<?php

namespace App\Exports;

use App\Models\Absensi;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class FastExcelAbsensiExport
{
    /**
     * Export absensi data to Excel (with ZipArchive fallback to CSV)
     *
     * @param \Illuminate\Database\Eloquent\Collection $absensi
     * @param string $tanggal
     * @param int|null $jamSekolahId
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function toExcel($absensi, $tanggal, $jamSekolahId = null)
    {
        // Check if ZipArchive is available
        if (!class_exists('ZipArchive')) {
            // Log the fallback
            \Log::info('ZipArchive not available, using CSV fallback for Excel export');
            
            // Use CSV fallback with Excel-compatible headers
            return $this->toCsvWithExcelHeaders($absensi, $tanggal, $jamSekolahId);
        }
        
        try {
            $fileName = 'Laporan_Absensi_' . Carbon::parse($tanggal)->format('Y-m-d') . '.xlsx';
            
            $data = $this->mapAbsensiData($absensi);
            
            $tempFile = tempnam(sys_get_temp_dir(), 'absensi_export');
            
            (new FastExcel($data))->export($tempFile);
            
            return response()->download($tempFile, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            \Log::warning('Excel export failed, falling back to CSV: ' . $e->getMessage());
            
            // Fallback to CSV if Excel export fails
            return $this->toCsvWithExcelHeaders($absensi, $tanggal, $jamSekolahId);
        }
    }
    
    /**
     * Export absensi data to CSV with Excel-compatible headers (fallback method)
     *
     * @param \Illuminate\Support\Collection $absensi
     * @param string $tanggal
     * @param int|null $jamSekolahId
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function toCsvWithExcelHeaders($absensi, $tanggal, $jamSekolahId = null)
    {
        $fileName = 'Laporan_Absensi_' . Carbon::parse($tanggal)->format('Y-m-d') . '.csv';
        
        $data = $this->mapAbsensiData($absensi);
        
        // Create CSV content manually to avoid ZipArchive dependency
        $csvContent = $this->generateCsvContent($data);
        
        // Create temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'absensi_export_csv');
        file_put_contents($tempFile, $csvContent);
        
        return response()->download($tempFile, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ])->deleteFileAfterSend(true);
    }
    
    /**
     * Generate CSV content from data array
     *
     * @param \Illuminate\Support\Collection $data
     * @return string
     */
    protected function generateCsvContent($data)
    {
        if ($data->isEmpty()) {
            return "No data available\n";
        }
        
        $csvContent = '';
        
        // Add BOM for proper UTF-8 encoding in Excel
        $csvContent .= "\xEF\xBB\xBF";
        
        // Get headers from first row
        $headers = array_keys($data->first());
        $csvContent .= $this->arrayToCsvLine($headers);
        
        // Add data rows
        foreach ($data as $row) {
            $csvContent .= $this->arrayToCsvLine(array_values($row));
        }
        
        return $csvContent;
    }
    
    /**
     * Convert array to CSV line
     *
     * @param array $fields
     * @return string
     */
    protected function arrayToCsvLine($fields)
    {
        $line = '';
        $delimiter = ',';
        $enclosure = '"';
        
        foreach ($fields as $field) {
            // Escape enclosures in the field
            $field = str_replace($enclosure, $enclosure . $enclosure, (string)$field);
            
            // Enclose field if it contains delimiter, enclosure, or newline
            if (strpos($field, $delimiter) !== false || 
                strpos($field, $enclosure) !== false || 
                strpos($field, "\n") !== false || 
                strpos($field, "\r") !== false) {
                $field = $enclosure . $field . $enclosure;
            }
            
            $line .= $field . $delimiter;
        }
        
        // Remove trailing delimiter and add newline
        return rtrim($line, $delimiter) . "\n";
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
        // Check if ZipArchive is available, if not use manual CSV generation
        if (!class_exists('ZipArchive')) {
            return $this->toCsvWithExcelHeaders($absensi, $tanggal, $jamSekolahId);
        }
        
        try {
            $fileName = 'Laporan_Absensi_' . Carbon::parse($tanggal)->format('Y-m-d') . '.csv';
            
            $data = $this->mapAbsensiData($absensi);
            
            $tempFile = tempnam(sys_get_temp_dir(), 'absensi_export');
            
            (new FastExcel($data))->export($tempFile);
            
            return response()->download($tempFile, $fileName, [
                'Content-Type' => 'text/csv',
            ])->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            \Log::warning('FastExcel CSV export failed, using manual CSV generation: ' . $e->getMessage());
            
            // Fallback to manual CSV generation
            return $this->toCsvWithExcelHeaders($absensi, $tanggal, $jamSekolahId);
        }
    }
    
    /**
     * Map absensi data for export
     *
     * @param \Illuminate\Support\Collection $absensi
     * @return \Illuminate\Support\Collection
     */
    protected function mapAbsensiData($absensi)
    {
        return $absensi->map(function ($item) {
            // Handle both array and object formats
            if (is_array($item)) {
                // New array format
                $jamMasuk = $item['jam_masuk'] ? Carbon::parse($item['jam_masuk'])->format('H:i') : '-';
                $jamKeluar = $item['jam_keluar'] ? Carbon::parse($item['jam_keluar'])->format('H:i') : '-';
                
                return [
                    'NIS' => $item['nis'],
                    'Nama Siswa' => $item['nama'],
                    'Kelas' => $item['kelas'],
                    'Jurusan' => $item['jurusan'],
                    'Tanggal' => Carbon::today()->format('d/m/Y'),
                    'Sesi/Pelajaran' => $item['sesi'],
                    'Mata Pelajaran' => $item['mata_pelajaran'],
                    'Guru Pengampu' => $item['guru_pengampu'],
                    'Jam Masuk' => $jamMasuk,
                    'Status Masuk' => $item['status'],
                    'Jam Keluar' => $jamKeluar,
                    'Keterangan' => $item['keterangan'],
                    'Durasi' => $this->calculateDuration($item['jam_masuk'], $item['jam_keluar']),
                ];
            } else {
                // Old object format
                $kelas = isset($item->siswa) && isset($item->siswa->kelas) ? 
                    $item->siswa->kelas->tingkat . ' ' . $item->siswa->kelas->nama_kelas : 'N/A';
                    
                $jurusan = isset($item->siswa) && isset($item->siswa->kelas) && isset($item->siswa->kelas->jurusan) ? 
                    $item->siswa->kelas->jurusan->nama_jurusan : 'N/A';

                $jamMasuk = $item->jam_masuk ? Carbon::parse($item->jam_masuk)->format('H:i') : '-';
                $jamKeluar = $item->jam_keluar ? Carbon::parse($item->jam_keluar)->format('H:i') : '-';
                $tanggal = Carbon::parse($item->tanggal)->format('d/m/Y');
                
                $sesi = isset($item->jamSekolah) ? $item->jamSekolah->nama_sesi : 'N/A';
                
                return [
                    'NIS' => $item->nis,
                    'Nama Siswa' => isset($item->siswa) ? $item->siswa->nama : 'N/A',
                    'Kelas' => $kelas,
                    'Jurusan' => $jurusan,
                    'Tanggal' => $tanggal,
                    'Sesi/Pelajaran' => $sesi,
                    'Mata Pelajaran' => '-',
                    'Guru Pengampu' => '-',
                    'Jam Masuk' => $jamMasuk,
                    'Status Masuk' => $item->status_masuk,
                    'Jam Keluar' => $jamKeluar,
                    'Keterangan' => $item->keterangan ?? '-',
                    'Durasi' => $this->calculateDuration($item->jam_masuk, $item->jam_keluar),
                ];
            }
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
