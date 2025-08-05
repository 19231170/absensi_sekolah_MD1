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
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->string('nis');
            $table->foreign('nis')->references('nis')->on('siswa')->onDelete('cascade');
            $table->foreignId('jam_sekolah_id')->constrained('jam_sekolah')->onDelete('cascade');
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_keluar')->nullable();
            $table->enum('status_masuk', ['hadir', 'telat', 'alpha'])->default('alpha');
            $table->enum('status_keluar', ['sudah_keluar', 'belum_keluar'])->default('belum_keluar');
            $table->text('keterangan')->nullable();
            $table->timestamps();
            
            // Index untuk performa query
            $table->index(['nis', 'tanggal']);
            $table->unique(['nis', 'tanggal', 'jam_sekolah_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};
