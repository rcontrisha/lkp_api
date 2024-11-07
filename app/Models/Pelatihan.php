<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Pelatihan extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'deskripsi',
        'tanggal_mulai',
        'tanggal_selesai',
        'foto_pelatihan',
        'kuota',
        'instruktur',
        'lama_pelatihan'
    ];

    // Relasi ke Materi
    public function materis()
    {
        return $this->hasMany(Materi::class);
    }

    // Relasi ke Tugas
    public function tugas()
    {
        return $this->hasMany(Tugas::class);
    }

    // Accessor untuk lama pelatihan (tanggal_selesai - tanggal_mulai)
    public function getLamaPelatihanAttribute()
    {
        if ($this->tanggal_mulai && $this->tanggal_selesai) {
            $startDate = Carbon::parse($this->tanggal_mulai);
            $endDate = Carbon::parse($this->tanggal_selesai);
            return $startDate->diffInDays($endDate);
        }
        return null;
    }
}
