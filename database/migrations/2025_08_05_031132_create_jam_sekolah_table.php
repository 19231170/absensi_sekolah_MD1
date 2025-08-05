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
        Schema::create('jam_sekolah', function (Blueprint $table) {
            $table->id();
            $table->string('nama_sesi'); // Pagi, Siang, dll
            $table->time('jam_masuk');
            $table->time('batas_telat'); // Batas waktu masih dianggap tidak telat
            $table->time('jam_keluar');
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jam_sekolah');
    }
};
