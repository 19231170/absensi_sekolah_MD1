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
        Schema::create('jadwal_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->enum('hari', ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu']);
            $table->time('jam_masuk');
            $table->time('jam_keluar');
            $table->time('batas_telat')->nullable(); // Batas waktu dianggap telat
            $table->string('mata_pelajaran')->nullable(); // Mata pelajaran yang sedang berlangsung
            $table->string('guru_pengampu')->nullable(); // Nama guru
            $table->text('keterangan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Index untuk performa query
            $table->index(['kelas_id', 'hari']);
            $table->index(['hari', 'jam_masuk']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_kelas');
    }
};
