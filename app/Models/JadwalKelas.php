<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class JadwalKelas extends Model
{
    protected $table = 'jadwal_kelas';
    
    protected $fillable = [
        'kelas_id',
        'hari',
        'jam_masuk',
        'jam_keluar', 
        'batas_telat',
        'mata_pelajaran',
        'guru_pengampu',
        'keterangan',
        'is_active'
    ];

    protected $casts = [
        'jam_masuk' => 'datetime:H:i:s',
        'jam_keluar' => 'datetime:H:i:s',
        'batas_telat' => 'datetime:H:i:s',
        'is_active' => 'boolean'
    ];

    /**
     * Relasi ke model Kelas
     */
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    /**
     * Scope untuk jadwal aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk hari tertentu
     */
    public function scopeUntukHari($query, $hari)
    {
        return $query->where('hari', strtolower($hari));
    }

    /**
     * Scope untuk kelas tertentu
     */
    public function scopeUntukKelas($query, $kelasId)
    {
        return $query->where('kelas_id', $kelasId);
    }

    /**
     * Accessor untuk nama hari dalam bahasa Indonesia
     */
    public function getNamaHariAttribute(): string
    {
        $hariMap = [
            'senin' => 'Senin',
            'selasa' => 'Selasa', 
            'rabu' => 'Rabu',
            'kamis' => 'Kamis',
            'jumat' => 'Jumat',
            'sabtu' => 'Sabtu'
        ];
        
        return $hariMap[$this->hari] ?? ucfirst($this->hari);
    }

    /**
     * Accessor untuk format jam masuk
     */
    public function getJamMasukFormatAttribute(): string
    {
        return Carbon::parse($this->jam_masuk)->format('H:i');
    }

    /**
     * Accessor untuk format jam keluar
     */
    public function getJamKeluarFormatAttribute(): string
    {
        return Carbon::parse($this->jam_keluar)->format('H:i');
    }

    /**
     * Accessor untuk durasi kelas
     */
    public function getDurasiAttribute(): string
    {
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
