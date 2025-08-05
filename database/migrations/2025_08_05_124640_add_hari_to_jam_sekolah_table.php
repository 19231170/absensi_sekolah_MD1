<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('jam_sekolah', function (Blueprint $table) {
            $table->json('hari_berlaku')->nullable()->after('nama_sesi'); // ['senin', 'selasa', 'rabu', dll]
            $table->string('jenis_sesi')->default('pagi')->after('hari_berlaku'); // 'pagi' atau 'siang'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jam_sekolah', function (Blueprint $table) {
            $table->dropColumn(['hari_berlaku', 'jenis_sesi']);
        });
    }
};
