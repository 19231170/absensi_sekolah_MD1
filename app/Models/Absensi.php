<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Absensi extends Model
{
    protected $table = 'absensi';
    
    protected $fillable = [
        'nis',
        'jam_sekolah_id',
        'tanggal',
        'jam_masuk',
        'jam_keluar',
        'status_masuk',
        'status_keluar',
        'keterangan'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jam_masuk' => 'datetime:H:i:s',
        'jam_keluar' => 'datetime:H:i:s'
    ];

    /**
     * Relasi ke model Siswa
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'nis', 'nis');
    }

    /**
     * Relasi ke model JamSekolah
     */
    public function jamSekolah(): BelongsTo
    {
        return $this->belongsTo(JamSekolah::class);
    }

    /**
     * Scope untuk absensi hari ini
     */
    public function scopeHariIni($query)
    {
        return $query->whereDate('tanggal', Carbon::today('Asia/Jakarta'));
    }

    /**
     * Scope untuk absensi berdasarkan tanggal
     */
    public function scopeTanggal($query, $tanggal)
    {
        return $query->whereDate('tanggal', $tanggal);
    }

    /**
     * Cek apakah siswa sudah absen masuk hari ini
     */
    public static function sudahAbsenMasuk($nis, $tanggal = null, $jamSekolahId = null)
    {
        $tanggal = $tanggal ?? Carbon::today('Asia/Jakarta');
        $query = self::where('nis', $nis)
            ->whereDate('tanggal', $tanggal)
            ->whereNotNull('jam_masuk');
            
        if ($jamSekolahId) {
            $query->where('jam_sekolah_id', $jamSekolahId);
        }
            
        return $query->exists();
    }

    /**
     * Cek apakah siswa sudah absen keluar hari ini
     */
    public static function sudahAbsenKeluar($nis, $tanggal = null, $jamSekolahId = null)
    {
        $tanggal = $tanggal ?? Carbon::today('Asia/Jakarta');
        $query = self::where('nis', $nis)
            ->whereDate('tanggal', $tanggal)
            ->whereNotNull('jam_keluar');
            
        if ($jamSekolahId) {
            $query->where('jam_sekolah_id', $jamSekolahId);
        }
            
        return $query->exists();
    }
}
