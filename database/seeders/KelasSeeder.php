<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Kelas;
use App\Models\Jurusan;

class KelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jurusan = Jurusan::all();
        
        $tingkat = ['X', 'XI', 'XII'];
        
        foreach ($jurusan as $jur) {
            foreach ($tingkat as $t) {
                for ($i = 1; $i <= 2; $i++) {
                    Kelas::create([
                        'nama_kelas' => $jur->kode_jurusan . ' ' . $i,
                        'tingkat' => $t,
                        'jurusan_id' => $jur->id,
                        'kapasitas' => 36
                    ]);
                }
            }
        }
    }
}
