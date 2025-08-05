<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Siswa;
use App\Models\Kelas;

class SiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kelas = Kelas::all();
        
        $namaSiswa = [
            'Ahmad Rizki Pratama', 'Siti Nurhaliza', 'Budi Santoso', 'Rina Wijayanti',
            'Dimas Aditya', 'Fitri Rahmawati', 'Eko Prasetyo', 'Dewi Sartika',
            'Fajar Nugroho', 'Lestari Indah', 'Gilang Ramadan', 'Maya Sari',
            'Hendra Gunawan', 'Nur Azizah', 'Iqbal Maulana'
        ];
        
        $counter = 1001;
        
        foreach ($kelas->take(5) as $k) { // Ambil 5 kelas pertama untuk demo
            for ($i = 0; $i < 5; $i++) { // 5 siswa per kelas
                $nama = $namaSiswa[array_rand($namaSiswa)];
                $nis = str_pad($counter, 8, '0', STR_PAD_LEFT);
                
                Siswa::create([
                    'nis' => $nis,
                    'nama' => $nama,
                    'jenis_kelamin' => rand(0, 1) ? 'L' : 'P',
                    'tanggal_lahir' => fake()->dateTimeBetween('2005-01-01', '2008-12-31')->format('Y-m-d'),
                    'alamat' => fake()->address(),
                    'nomor_hp' => '08' . fake()->numerify('##########'),
                    'kelas_id' => $k->id,
                    'qr_code' => Siswa::generateQrCode(),
                    'status_aktif' => true
                ]);
                
                $counter++;
            }
        }
    }
}
