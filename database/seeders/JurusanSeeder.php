<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Jurusan;

class JurusanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jurusan = [
            [
                'nama_jurusan' => 'Rekayasa Perangkat Lunak',
                'kode_jurusan' => 'RPL',
                'deskripsi' => 'Jurusan yang mempelajari tentang pengembangan perangkat lunak'
            ],
            [
                'nama_jurusan' => 'Teknik Komputer dan Jaringan',
                'kode_jurusan' => 'TKJ',
                'deskripsi' => 'Jurusan yang mempelajari tentang perangkat keras komputer dan jaringan'
            ],
            [
                'nama_jurusan' => 'Multimedia',
                'kode_jurusan' => 'MM',
                'deskripsi' => 'Jurusan yang mempelajari tentang desain grafis dan multimedia'
            ],
            [
                'nama_jurusan' => 'Akuntansi dan Keuangan Lembaga',
                'kode_jurusan' => 'AKL',
                'deskripsi' => 'Jurusan yang mempelajari tentang akuntansi dan keuangan'
            ],
            [
                'nama_jurusan' => 'Otomatisasi dan Tata Kelola Perkantoran',
                'kode_jurusan' => 'OTKP',
                'deskripsi' => 'Jurusan yang mempelajari tentang administrasi perkantoran'
            ]
        ];

        foreach ($jurusan as $data) {
            Jurusan::create($data);
        }
    }
}
