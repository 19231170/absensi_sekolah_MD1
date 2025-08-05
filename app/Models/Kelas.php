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
        'kapasitas'
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
     * Accessor untuk nama lengkap kelas
     */
    public function getNamaLengkapAttribute(): string
    {
        return "{$this->tingkat} {$this->nama_kelas} - {$this->jurusan->nama_jurusan}";
    }
}
