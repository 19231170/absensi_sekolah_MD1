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
        Schema::create('absensi_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->string('nis', 20);
            $table->unsignedBigInteger('jadwal_kelas_id');
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_keluar')->nullable();
            $table->enum('status_masuk', ['hadir', 'telat', 'tidak_hadir'])->default('tidak_hadir');
            $table->enum('status_keluar', ['sudah_keluar', 'belum_keluar'])->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('nis')->references('nis')->on('siswa')->onDelete('cascade');
            $table->foreign('jadwal_kelas_id')->references('id')->on('jadwal_kelas')->onDelete('cascade');

            // Indexes
            $table->index(['nis', 'tanggal']);
            $table->index(['jadwal_kelas_id', 'tanggal']);
            $table->unique(['nis', 'jadwal_kelas_id', 'tanggal'], 'unique_absensi_pelajaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi_pelajaran');
    }
};
