<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\JadwalKelas;

class JadwalKelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat jurusan jika belum ada
        $pplg = Jurusan::firstOrCreate(
            ['nama_jurusan' => 'Pengembangan Perangkat Lunak dan Gim'],
            ['kode_jurusan' => 'PPLG']
        );
        
        $rpl = Jurusan::firstOrCreate(
            ['nama_jurusan' => 'Rekayasa Perangkat Lunak'],
            ['kode_jurusan' => 'RPL']
        );

        // Buat kelas-kelas lab
        $kelasData = [
            // Kelas 10 PPLG
            ['nama_kelas' => 'PPLG 1', 'tingkat' => 10, 'jurusan_id' => $pplg->id],
            ['nama_kelas' => 'PPLG 2', 'tingkat' => 10, 'jurusan_id' => $pplg->id],
            
            // Kelas 11 RPL
            ['nama_kelas' => 'RPL 1', 'tingkat' => 11, 'jurusan_id' => $rpl->id],
            ['nama_kelas' => 'RPL 2', 'tingkat' => 11, 'jurusan_id' => $rpl->id],
            
            // Kelas 12 RPL
            ['nama_kelas' => 'RPL 1', 'tingkat' => 12, 'jurusan_id' => $rpl->id],
            ['nama_kelas' => 'RPL 2', 'tingkat' => 12, 'jurusan_id' => $rpl->id],
        ];

        $kelasIds = [];
        foreach ($kelasData as $data) {
            $kelas = Kelas::firstOrCreate([
                'nama_kelas' => $data['nama_kelas'],
                'tingkat' => $data['tingkat'],
                'jurusan_id' => $data['jurusan_id']
            ], [
                'kapasitas' => 35
            ]);
            $kelasIds[] = $kelas->id;
        }

        // Jadwal default untuk lab - contoh jadwal
        $jadwalData = [
            // Senin
            [
                'kelas_id' => $kelasIds[0], // X PPLG 1
                'hari' => 'senin',
                'jam_masuk' => '07:30:00',
                'jam_keluar' => '10:30:00',
                'batas_telat' => '07:45:00',
                'mata_pelajaran' => 'Pemrograman Dasar',
                'guru_pengampu' => 'Pak Budi',
                'keterangan' => 'Lab Komputer 1'
            ],
            [
                'kelas_id' => $kelasIds[1], // X PPLG 2
                'hari' => 'senin',
                'jam_masuk' => '10:45:00',
                'jam_keluar' => '13:45:00',
                'batas_telat' => '11:00:00',
                'mata_pelajaran' => 'Pemrograman Dasar',
                'guru_pengampu' => 'Bu Sari',
                'keterangan' => 'Lab Komputer 1'
            ],
            
            // Selasa
            [
                'kelas_id' => $kelasIds[2], // XI RPL 1
                'hari' => 'selasa',
                'jam_masuk' => '07:30:00',
                'jam_keluar' => '10:30:00',
                'batas_telat' => '07:45:00',
                'mata_pelajaran' => 'Pemrograman Web',
                'guru_pengampu' => 'Pak Andi',
                'keterangan' => 'Lab Komputer 2'
            ],
            [
                'kelas_id' => $kelasIds[3], // XI RPL 2
                'hari' => 'selasa',
                'jam_masuk' => '10:45:00',
                'jam_keluar' => '13:45:00',
                'batas_telat' => '11:00:00',
                'mata_pelajaran' => 'Pemrograman Web',
                'guru_pengampu' => 'Bu Lisa',
                'keterangan' => 'Lab Komputer 2'
            ],
            
            // Rabu
            [
                'kelas_id' => $kelasIds[4], // XII RPL 1
                'hari' => 'rabu',
                'jam_masuk' => '07:30:00',
                'jam_keluar' => '10:30:00',
                'batas_telat' => '07:45:00',
                'mata_pelajaran' => 'Pemrograman Mobile',
                'guru_pengampu' => 'Pak Dedi',
                'keterangan' => 'Lab Komputer 3'
            ],
            [
                'kelas_id' => $kelasIds[5], // XII RPL 2
                'hari' => 'rabu',
                'jam_masuk' => '10:45:00',
                'jam_keluar' => '13:45:00',
                'batas_telat' => '11:00:00',
                'mata_pelajaran' => 'Pemrograman Mobile',
                'guru_pengampu' => 'Bu Nina',
                'keterangan' => 'Lab Komputer 3'
            ],

            // Kamis
            [
                'kelas_id' => $kelasIds[0], // X PPLG 1
                'hari' => 'kamis',
                'jam_masuk' => '13:00:00',
                'jam_keluar' => '16:00:00',
                'batas_telat' => '13:15:00',
                'mata_pelajaran' => 'Basis Data',
                'guru_pengampu' => 'Pak Eko',
                'keterangan' => 'Lab Komputer 1'
            ],
            [
                'kelas_id' => $kelasIds[2], // XI RPL 1
                'hari' => 'kamis',
                'jam_masuk' => '07:30:00',
                'jam_keluar' => '10:30:00',
                'batas_telat' => '07:45:00',
                'mata_pelajaran' => 'Framework Programming',
                'guru_pengampu' => 'Bu Rina',
                'keterangan' => 'Lab Komputer 2'
            ],

            // Jumat
            [
                'kelas_id' => $kelasIds[1], // X PPLG 2
                'hari' => 'jumat',
                'jam_masuk' => '07:30:00',
                'jam_keluar' => '10:00:00',
                'batas_telat' => '07:45:00',
                'mata_pelajaran' => 'Algoritma dan Pemrograman',
                'guru_pengampu' => 'Pak Joko',
                'keterangan' => 'Lab Komputer 1'
            ],
            [
                'kelas_id' => $kelasIds[4], // XII RPL 1
                'hari' => 'jumat',
                'jam_masuk' => '10:15:00',
                'jam_keluar' => '11:45:00',
                'batas_telat' => '10:30:00',
                'mata_pelajaran' => 'Project Based Learning',
                'guru_pengampu' => 'Bu Maya',
                'keterangan' => 'Lab Komputer 3'
            ]
        ];

        foreach ($jadwalData as $jadwal) {
            JadwalKelas::create($jadwal);
        }
    }
}
