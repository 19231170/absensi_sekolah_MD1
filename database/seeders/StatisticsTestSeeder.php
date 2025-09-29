<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\JadwalKelas;
use App\Models\AbsensiPelajaran;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class StatisticsTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::updateOrCreate([
            'email' => 'admin@sekolah.com'
        ], [
            'name' => 'Administrator',
            'role' => 'admin',
            'password' => Hash::make('admin123'),
            'pin' => '1234',
            'qr_code' => 'QR2025ADMIN0001',
            'is_active' => true
        ]);

        // Create guru user
        User::updateOrCreate([
            'email' => 'guru@sekolah.com'
        ], [
            'name' => 'Guru Matematika',
            'role' => 'guru',
            'password' => Hash::make('guru123'),
            'pin' => '5678',
            'qr_code' => 'QR2025GURU0001',
            'is_active' => true
        ]);

        // Create jurusan
        $jurusanRPL = Jurusan::updateOrCreate([
            'nama_jurusan' => 'Rekayasa Perangkat Lunak'
        ], [
            'kode_jurusan' => 'RPL',
            'deskripsi' => 'Jurusan Rekayasa Perangkat Lunak'
        ]);

        $jurusanTKJ = Jurusan::updateOrCreate([
            'nama_jurusan' => 'Teknik Komputer dan Jaringan'
        ], [
            'kode_jurusan' => 'TKJ',
            'deskripsi' => 'Jurusan Teknik Komputer dan Jaringan'
        ]);

        // Create kelas
        $kelas12RPL1 = Kelas::updateOrCreate([
            'tingkat' => 12,
            'nama_kelas' => 'RPL 1',
            'jurusan_id' => $jurusanRPL->id
        ]);

        $kelas12TKJ1 = Kelas::updateOrCreate([
            'tingkat' => 12,
            'nama_kelas' => 'TKJ 1',
            'jurusan_id' => $jurusanTKJ->id
        ]);

        // Create students for 12 RPL 1
        $siswaRPL = [];
        for ($i = 1; $i <= 20; $i++) {
            $nis = '2021' . str_pad($i, 4, '0', STR_PAD_LEFT);
            $siswa = Siswa::updateOrCreate([
                'nis' => $nis
            ], [
                'nama' => 'Siswa RPL ' . $i,
                'jenis_kelamin' => $i % 2 == 0 ? 'P' : 'L',
                'tanggal_lahir' => Carbon::create(2005, rand(1, 12), rand(1, 28)),
                'alamat' => 'Alamat Siswa ' . $i,
                'nomor_hp' => '08123456789' . $i,
                'kelas_id' => $kelas12RPL1->id,
                'qr_code' => 'QR2025' . $nis,
                'status_aktif' => true
            ]);
            $siswaRPL[] = $siswa;
        }

        // Create students for 12 TKJ 1
        $siswaTKJ = [];
        for ($i = 21; $i <= 35; $i++) {
            $nis = '2021' . str_pad($i, 4, '0', STR_PAD_LEFT);
            $siswa = Siswa::updateOrCreate([
                'nis' => $nis
            ], [
                'nama' => 'Siswa TKJ ' . ($i - 20),
                'jenis_kelamin' => $i % 2 == 0 ? 'P' : 'L',
                'tanggal_lahir' => Carbon::create(2005, rand(1, 12), rand(1, 28)),
                'alamat' => 'Alamat Siswa ' . ($i - 20),
                'nomor_hp' => '08123456789' . $i,
                'kelas_id' => $kelas12TKJ1->id,
                'qr_code' => 'QR2025' . $nis,
                'status_aktif' => true
            ]);
            $siswaTKJ[] = $siswa;
        }

        // Create jadwal kelas (schedules)
        $mataPelajaran = ['Matematika', 'Bahasa Indonesia', 'Bahasa Inggris', 'Pemrograman Web', 'Basis Data', 'Jaringan Komputer'];
        $hari = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'];

        foreach ([$kelas12RPL1, $kelas12TKJ1] as $kelas) {
            foreach ($hari as $hariIndex => $namaHari) {
                foreach (array_slice($mataPelajaran, 0, 2) as $mapelIndex => $mapel) {
                    $jamMasuk = sprintf('%02d:00:00', 8 + ($mapelIndex * 2));
                    $jamKeluar = sprintf('%02d:00:00', 10 + ($mapelIndex * 2));
                    
                    JadwalKelas::updateOrCreate([
                        'kelas_id' => $kelas->id,
                        'hari' => $namaHari,
                        'mata_pelajaran' => $mapel,
                    ], [
                        'jam_masuk' => $jamMasuk,
                        'jam_keluar' => $jamKeluar,
                        'batas_telat' => '08:15:00',
                        'guru_pengampu' => 'Guru ' . $mapel,
                        'is_active' => true
                    ]);
                }
            }
        }

        // Create sample attendance data for the last month
        $this->createSampleAttendanceData($siswaRPL, $kelas12RPL1->id);
        $this->createSampleAttendanceData($siswaTKJ, $kelas12TKJ1->id);
        
        $this->command->info('Statistics test data has been seeded successfully!');
    }

    private function createSampleAttendanceData($students, $kelasId)
    {
        $jadwalKelas = JadwalKelas::where('kelas_id', $kelasId)->get();
        
        // Create data for the last 30 days
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();
        
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            // Skip weekends
            if ($date->isWeekend()) continue;
            
            $dayName = strtolower($date->format('l'));
            $hariMap = [
                'monday' => 'senin',
                'tuesday' => 'selasa', 
                'wednesday' => 'rabu',
                'thursday' => 'kamis',
                'friday' => 'jumat'
            ];
            
            $hari = $hariMap[$dayName] ?? null;
            if (!$hari) continue;
            
            $jadwalHari = $jadwalKelas->where('hari', $hari);
            
            foreach ($jadwalHari as $jadwal) {
                foreach ($students as $student) {
                    // Create random attendance pattern
                    $attendanceType = $this->getRandomAttendanceType($student);
                    
                    $absensiData = [
                        'nis' => $student->nis,
                        'jadwal_kelas_id' => $jadwal->id,
                        'tanggal' => $date->format('Y-m-d'),
                    ];
                    
                    if ($attendanceType === 'hadir') {
                        $jamMasuk = Carbon::parse($jadwal->jam_masuk)->addMinutes(rand(-5, 10));
                        $jamKeluar = Carbon::parse($jadwal->jam_keluar)->addMinutes(rand(-10, 5));
                        
                        $absensiData['jam_masuk'] = $jamMasuk->format('H:i:s');
                        $absensiData['jam_keluar'] = $jamKeluar->format('H:i:s');
                        $absensiData['status_masuk'] = 'hadir';
                        $absensiData['status_keluar'] = 'sudah_keluar';
                        
                    } elseif ($attendanceType === 'telat') {
                        $jamMasuk = Carbon::parse($jadwal->jam_masuk)->addMinutes(rand(15, 45));
                        $jamKeluar = Carbon::parse($jadwal->jam_keluar)->addMinutes(rand(-10, 5));
                        
                        $absensiData['jam_masuk'] = $jamMasuk->format('H:i:s');
                        $absensiData['jam_keluar'] = $jamKeluar->format('H:i:s');
                        $absensiData['status_masuk'] = 'telat';
                        $absensiData['status_keluar'] = 'sudah_keluar';
                        $absensiData['keterangan'] = 'Terlambat karena macet';
                        
                    } else { // alpha
                        $absensiData['jam_masuk'] = null;
                        $absensiData['jam_keluar'] = null;
                        $absensiData['status_masuk'] = 'tidak_hadir';
                        $absensiData['status_keluar'] = null;
                        $absensiData['keterangan'] = 'Tidak hadir tanpa keterangan';
                    }
                    
                    AbsensiPelajaran::updateOrCreate([
                        'nis' => $absensiData['nis'],
                        'jadwal_kelas_id' => $absensiData['jadwal_kelas_id'],
                        'tanggal' => $absensiData['tanggal']
                    ], $absensiData);
                }
            }
        }
    }

    private function getRandomAttendanceType($student)
    {
        // Create different patterns for different students to simulate real scenarios
        $studentId = intval(substr($student->nis, -2));
        
        if ($studentId <= 5) {
            // Top performing students (85% hadir, 10% telat, 5% alpha)
            $rand = rand(1, 100);
            if ($rand <= 85) return 'hadir';
            if ($rand <= 95) return 'telat';
            return 'alpha';
            
        } elseif ($studentId <= 15) {
            // Average students (70% hadir, 20% telat, 10% alpha)
            $rand = rand(1, 100);
            if ($rand <= 70) return 'hadir';
            if ($rand <= 90) return 'telat';
            return 'alpha';
            
        } else {
            // Students with issues (50% hadir, 25% telat, 25% alpha)
            $rand = rand(1, 100);
            if ($rand <= 50) return 'hadir';
            if ($rand <= 75) return 'telat';
            return 'alpha';
        }
    }
}
