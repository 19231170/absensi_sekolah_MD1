<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JamSekolah extends Model
{
    protected $table = 'jam_sekolah';
    
    protected $fillable = [
        'nama_sesi',
        'hari_berlaku',
        'jenis_sesi',
        'jam_masuk',
        'batas_telat',
        'jam_keluar',
        'status_aktif'
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
        'hari_berlaku' => 'array'
    ];

    /**
     * Relasi ke model Absensi
     */
    public function absensi(): HasMany
    {
        return $this->hasMany(Absensi::class);
    }

    /**
     * Scope untuk jam sekolah yang aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('status_aktif', true);
    }
    
    /**
     * Scope untuk jam sekolah berdasarkan hari
     */
    public function scopeUntukHari($query, $hari)
    {
        return $query->whereJsonContains('hari_berlaku', strtolower($hari));
    }
    
    /**
     * Scope untuk jam sekolah berdasarkan jenis sesi
     */
    public function scopeJenisSesi($query, $jenis)
    {
        return $query->where('jenis_sesi', $jenis);
    }
    
    /**
     * Get nama hari dalam bahasa Indonesia
     */
    public function getHariIndonesiaAttribute()
    {
        $hariMap = [
            'sunday' => 'minggu',
            'monday' => 'senin', 
            'tuesday' => 'selasa',
            'wednesday' => 'rabu',
            'thursday' => 'kamis',
            'friday' => 'jumat',
            'saturday' => 'sabtu'
        ];
        
        $hariInggris = strtolower(now()->format('l'));
        return $hariMap[$hariInggris] ?? 'unknown';
    }
    
    /**
     * Cek apakah sesi berlaku untuk hari ini
     */
    public function berlakuHariIni()
    {
        $hariIni = $this->hari_indonesia;
        return in_array($hariIni, $this->hari_berlaku ?? []);
    }
}
