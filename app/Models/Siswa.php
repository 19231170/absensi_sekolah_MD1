<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Siswa extends Model
{
    protected $table = 'siswa';
    protected $primaryKey = 'nis';
    protected $keyType = 'string';
    public $incrementing = false;
    
    protected $fillable = [
        'nis',
        'nama',
        'jenis_kelamin',
        'tanggal_lahir',
        'alamat',
        'nomor_hp',
        'kelas_id',
        'qr_code',
        'status_aktif'
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'status_aktif' => 'boolean'
    ];

    /**
     * Relasi ke model Kelas
     */
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    /**
     * Relasi ke model Absensi
     */
    public function absensi(): HasMany
    {
        return $this->hasMany(Absensi::class, 'nis', 'nis');
    }

    /**
     * Accessor untuk mendapatkan jurusan melalui kelas
     */
    public function getJurusanAttribute()
    {
        return $this->kelas->jurusan ?? null;
    }

    /**
     * Scope untuk siswa aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('status_aktif', true);
    }

    /**
     * Generate QR Code unik untuk siswa
     */
    public static function generateQrCode(): string
    {
        do {
            $qrCode = 'QR' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (self::where('qr_code', $qrCode)->exists());
        
        return $qrCode;
    }
}
