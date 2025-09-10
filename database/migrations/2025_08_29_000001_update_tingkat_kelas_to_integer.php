<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Pertama, backup data lama
        $kelas = DB::table('kelas')->select('id', 'tingkat')->get();
        $kelasData = [];

        foreach ($kelas as $k) {
            // Konversi angka Romawi ke angka biasa
            $tingkatInt = 10; // Default ke kelas 10 jika tidak bisa dikonversi
            
            if ($k->tingkat === 'X') {
                $tingkatInt = 10;
            } elseif ($k->tingkat === 'XI') {
                $tingkatInt = 11;
            } elseif ($k->tingkat === 'XII') {
                $tingkatInt = 12;
            }
            
            $kelasData[$k->id] = $tingkatInt;
        }

        // Ubah tipe kolom
        Schema::table('kelas', function (Blueprint $table) {
            $table->integer('tingkat_new')->nullable()->after('tingkat');
        });

        // Migrasi data
        foreach ($kelasData as $id => $tingkat) {
            DB::table('kelas')
                ->where('id', $id)
                ->update(['tingkat_new' => $tingkat]);
        }

        // Hapus kolom lama dan rename kolom baru
        Schema::table('kelas', function (Blueprint $table) {
            $table->dropColumn('tingkat');
        });

        Schema::table('kelas', function (Blueprint $table) {
            $table->renameColumn('tingkat_new', 'tingkat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Backup data
        $kelas = DB::table('kelas')->select('id', 'tingkat')->get();
        $kelasData = [];

        foreach ($kelas as $k) {
            // Konversi angka ke angka Romawi
            $tingkatRomawi = 'X'; // Default
            
            if ($k->tingkat == 10) {
                $tingkatRomawi = 'X';
            } elseif ($k->tingkat == 11) {
                $tingkatRomawi = 'XI';
            } elseif ($k->tingkat == 12) {
                $tingkatRomawi = 'XII';
            } elseif ($k->tingkat == 13) {
                $tingkatRomawi = 'XIII';
            }
            
            $kelasData[$k->id] = $tingkatRomawi;
        }

        // Ubah tipe kolom kembali ke string
        Schema::table('kelas', function (Blueprint $table) {
            $table->string('tingkat_old')->nullable()->after('tingkat');
        });

        // Migrasi data
        foreach ($kelasData as $id => $tingkat) {
            DB::table('kelas')
                ->where('id', $id)
                ->update(['tingkat_old' => $tingkat]);
        }

        // Hapus kolom integer dan rename kolom string
        Schema::table('kelas', function (Blueprint $table) {
            $table->dropColumn('tingkat');
        });

        Schema::table('kelas', function (Blueprint $table) {
            $table->renameColumn('tingkat_old', 'tingkat');
        });
    }
};
