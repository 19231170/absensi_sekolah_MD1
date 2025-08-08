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
            $table->json('hari_berlaku')->nullable()->after('nama_sesi');
            $table->enum('jenis_sesi', ['pagi', 'siang', 'malam'])->default('pagi')->after('hari_berlaku');
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
