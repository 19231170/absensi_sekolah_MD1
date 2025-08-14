<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class AbsensiPelajaran extends Model
{
    protected $table = 'absensi_pelajaran';
    
    protected $fillable = [
        'nis',
        'jadwal_kelas_id',
        'tanggal',
        'jam_masuk',
        'jam_keluar',
        'status_masuk',
        'status_keluar',
        'keterangan'
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    /**
     * Relasi ke model Siswa
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'nis', 'nis');
    }

    /**
     * Relasi ke model JadwalKelas
     */
    public function jadwalKelas(): BelongsTo
    {
        return $this->belongsTo(JadwalKelas::class);
    }

    /**
     * Scope untuk tanggal tertentu
     */
    public function scopeUntukTanggal($query, $tanggal)
    {
        return $query->whereDate('tanggal', $tanggal);
    }

    /**
     * Scope untuk jadwal kelas tertentu
     */
    public function scopeUntukJadwal($query, $jadwalKelasId)
    {
        return $query->where('jadwal_kelas_id', $jadwalKelasId);
    }

    /**
     * Scope untuk siswa tertentu
     */
    public function scopeUntukSiswa($query, $nis)
    {
        return $query->where('nis', $nis);
    }

    /**
     * Static method untuk cek sudah absen masuk
     */
    public static function sudahAbsenMasuk($nis, $tanggal, $jadwalKelasId)
    {
        return self::where('nis', $nis)
            ->whereDate('tanggal', $tanggal)
            ->where('jadwal_kelas_id', $jadwalKelasId)
            ->whereNotNull('jam_masuk')
            ->exists();
    }

    /**
     * Static method untuk cek sudah absen keluar
     */
    public static function sudahAbsenKeluar($nis, $tanggal, $jadwalKelasId)
    {
        return self::where('nis', $nis)
            ->whereDate('tanggal', $tanggal)
            ->where('jadwal_kelas_id', $jadwalKelasId)
            ->whereNotNull('jam_keluar')
            ->exists();
    }

    /**
     * Accessor untuk status display
     */
    public function getStatusDisplayAttribute(): string
    {
        if ($this->status_masuk === 'telat') {
            return 'Terlambat';
        } elseif ($this->status_masuk === 'hadir') {
            return 'Hadir';
        } else {
            return 'Tidak Hadir';
        }
    }

    /**
     * Accessor untuk durasi mengikuti pelajaran
     */
    public function getDurasiPelajaranAttribute(): ?string
    {
        if (!$this->jam_masuk || !$this->jam_keluar) {
            return null;
        }

        $masuk = Carbon::parse($this->jam_masuk);
        $keluar = Carbon::parse($this->jam_keluar);
        $durasi = $masuk->diffInMinutes($keluar);
        
        $jam = floor($durasi / 60);
        $menit = $durasi % 60;
        
        if ($jam > 0 && $menit > 0) {
            return "{$jam} jam {$menit} menit";
        } elseif ($jam > 0) {
            return "{$jam} jam";
        } else {
            return "{$menit} menit";
        }
    }
}
