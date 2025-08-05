<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\JamSekolah;

class JamSekolahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus data lama secara aman (dengan delete, bukan truncate)
        JamSekolah::query()->delete();
        
        $jamSekolah = [
            // === SESI PAGI ===
            [
                'nama_sesi' => 'Pagi Senin & Rabu (07:30-12:05)',
                'hari_berlaku' => json_encode(['senin', 'rabu']),
                'jenis_sesi' => 'pagi',
                'jam_masuk' => '07:30:00',
                'batas_telat' => '07:45:00', // 15 menit toleransi
                'jam_keluar' => '12:05:00',
                'status_aktif' => true
            ],
            [
                'nama_sesi' => 'Pagi Selasa & Kamis (06:30-12:00)', 
                'hari_berlaku' => json_encode(['selasa', 'kamis']),
                'jenis_sesi' => 'pagi',
                'jam_masuk' => '06:30:00',
                'batas_telat' => '06:45:00', // 15 menit toleransi
                'jam_keluar' => '12:00:00',
                'status_aktif' => true
            ],
            [
                'nama_sesi' => 'Pagi Jumat (07:30-11:20)',
                'hari_berlaku' => json_encode(['jumat']),
                'jenis_sesi' => 'pagi',
                'jam_masuk' => '07:30:00',
                'batas_telat' => '07:45:00', // 15 menit toleransi
                'jam_keluar' => '11:20:00',
                'status_aktif' => true
            ],
            
            // === SESI SIANG ===
            [
                'nama_sesi' => 'Siang Selasa-Kamis (13:00-17:00)',
                'hari_berlaku' => json_encode(['selasa', 'rabu', 'kamis']),
                'jenis_sesi' => 'siang',
                'jam_masuk' => '13:00:00',
                'batas_telat' => '13:15:00', // 15 menit toleransi
                'jam_keluar' => '17:00:00',
                'status_aktif' => true
            ],
            [
                'nama_sesi' => 'Siang Jumat (13:00-16:20)',
                'hari_berlaku' => json_encode(['jumat']),
                'jenis_sesi' => 'siang',
                'jam_masuk' => '13:00:00',
                'batas_telat' => '13:15:00', // 15 menit toleransi
                'jam_keluar' => '16:20:00',
                'status_aktif' => true
            ]
        ];

        foreach ($jamSekolah as $data) {
            JamSekolah::create($data);
        }
        
        echo "✅ Berhasil membuat " . count($jamSekolah) . " sesi jam sekolah\n";
    }
}
