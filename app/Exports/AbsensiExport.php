<?php

namespace App\Exports;

use App\Models\Absensi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class AbsensiExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    private $tanggal;
    private $jamSekolahId;

    public function __construct($tanggal, $jamSekolahId = null)
    {
        $this->tanggal = $tanggal;
        $this->jamSekolahId = $jamSekolahId;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Absensi::with(['siswa.kelas.jurusan', 'jamSekolah'])
            ->whereDate('tanggal', $this->tanggal);

        if ($this->jamSekolahId) {
            $query->where('jam_sekolah_id', $this->jamSekolahId);
        }

        return $query->orderBy('jam_masuk', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'NIS',
            'Nama Siswa',
            'Kelas',
            'Jurusan',
            'Sesi',
            'Tanggal',
            'Jam Masuk',
            'Jam Keluar',
            'Status Masuk',
            'Status Keluar',
            'Keterangan'
        ];
    }

    public function map($absensi): array
    {
        static $no = 1;
        
        return [
            $no++,
            $absensi->nis,
            $absensi->siswa->nama,
            $absensi->siswa->kelas->nama_lengkap,
            $absensi->siswa->kelas->jurusan->nama_jurusan,
            $absensi->jamSekolah->nama_sesi,
            Carbon::parse($absensi->tanggal)->format('d/m/Y'),
            $absensi->jam_masuk ?? '-',
            $absensi->jam_keluar ?? '-',
            ucfirst($absensi->status_masuk),
            $absensi->status_keluar ? 'Sudah Keluar' : 'Belum Keluar',
            $absensi->keterangan ?? '-'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style header
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
