<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    protected $table = 'kelas';
    
    protected $fillable = [
        'nama_kelas',
        'tingkat',
        'jurusan_id',
        'kapasitas',
        'keterangan',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'kapasitas' => 'integer',
        'tingkat' => 'integer'
    ];

    protected $attributes = [
        'is_active' => true
    ];

    /**
     * Relasi ke model Jurusan
     */
    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class);
    }

    /**
     * Relasi ke model Siswa
     */
    public function siswa(): HasMany
    {
        return $this->hasMany(Siswa::class);
    }

    /**
     * Accessor para nama lengkap kelas
     */
    public function getNamaLengkapAttribute(): string
    {
        $jurusanNama = $this->jurusan ? $this->jurusan->nama_jurusan : 'Jurusan Tidak Diketahui';
        return "Kelas {$this->tingkat} {$this->nama_kelas} - {$jurusanNama}";
    }
}
