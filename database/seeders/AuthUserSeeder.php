<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Str;

class AuthUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@sekolah.edu',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'role' => 'admin',
            'pin' => '1234',
            'qr_code' => 'ADM001',
            'nip' => 'ADM001',
            'mata_pelajaran' => null,
            'is_active' => true,
            'remember_token' => Str::random(10),
        ]);

        // Create Test Teachers
        $teachers = [
            [
                'name' => 'Budi Santoso',
                'email' => 'budi@sekolah.edu',
                'nip' => 'GRU001',
                'mata_pelajaran' => 'Matematika',
                'pin' => '1111'
            ],
            [
                'name' => 'Siti Rahayu',
                'email' => 'siti@sekolah.edu',
                'nip' => 'GRU002',
                'mata_pelajaran' => 'Bahasa Indonesia',
                'pin' => '2222'
            ],
            [
                'name' => 'Andi Pratama',
                'email' => 'andi@sekolah.edu',
                'nip' => 'GRU003',
                'mata_pelajaran' => 'Fisika',
                'pin' => '3333'
            ],
            [
                'name' => 'Dewi Sartika',
                'email' => 'dewi@sekolah.edu',
                'nip' => 'GRU004',
                'mata_pelajaran' => 'Kimia',
                'pin' => '4444'
            ],
            [
                'name' => 'Rudi Hermawan',
                'email' => 'rudi@sekolah.edu',
                'nip' => 'GRU005',
                'mata_pelajaran' => 'Sejarah',
                'pin' => '5555'
            ]
        ];

        foreach ($teachers as $teacher) {
            User::create([
                'name' => $teacher['name'],
                'email' => $teacher['email'],
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
                'role' => 'guru',
                'pin' => $teacher['pin'],
                'qr_code' => $teacher['nip'], // Use NIP as QR code for simplicity
                'nip' => $teacher['nip'],
                'mata_pelajaran' => $teacher['mata_pelajaran'],
                'is_active' => true,
                'remember_token' => Str::random(10),
            ]);
        }

        $this->command->info('Created 1 admin and 5 teachers with QR authentication.');
        $this->command->info('Admin: QR=ADM001, PIN=1234');
        $this->command->info('Teacher 1: QR=GRU001, PIN=1111 (Budi Santoso - Matematika)');
        $this->command->info('Teacher 2: QR=GRU002, PIN=2222 (Siti Rahayu - Bahasa Indonesia)');
        $this->command->info('Teacher 3: QR=GRU003, PIN=3333 (Andi Pratama - Fisika)');
        $this->command->info('Teacher 4: QR=GRU004, PIN=4444 (Dewi Sartika - Kimia)');
        $this->command->info('Teacher 5: QR=GRU005, PIN=5555 (Rudi Hermawan - Sejarah)');
    }
}
