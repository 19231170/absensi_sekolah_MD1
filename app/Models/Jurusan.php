<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Jurusan extends Model
{
    protected $table = 'jurusan';
    
    protected $fillable = [
        'nama_jurusan',
        'kode_jurusan',
        'deskripsi'
    ];

    /**
     * Relasi ke model Kelas
     */
    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class);
    }

    /**
     * Relasi ke siswa melalui kelas
     */
    public function siswa(): HasManyThrough
    {
        return $this->hasManyThrough(Siswa::class, Kelas::class);
    }
}
