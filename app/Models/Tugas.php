<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tugas extends Model
{
    use HasFactory;

    protected $fillable = [
        'pelatihan_id',
        'judul',
        'deskripsi',
        'batas_waktu',
    ];

    // Relasi ke Pelatihan (Setiap tugas terkait dengan satu pelatihan)
    public function pelatihan()
    {
        return $this->belongsTo(Pelatihan::class);
    }

    // Relasi ke Pengumpulan Tugas (Satu tugas bisa memiliki banyak pengumpulan tugas)
    public function pengumpulanTugas()
    {
        return $this->hasMany(PengumpulanTugas::class);
    }
}