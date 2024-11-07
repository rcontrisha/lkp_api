<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Materi extends Model
{
    use HasFactory;

    protected $fillable = [
        'pelatihan_id',
        'file_path',
        'judul',        // Menambahkan field judul
        'deskripsi',    // Menambahkan field deskripsi
        'link_video',   // Menambahkan field link_video
    ];

    // Relasi ke Pelatihan (Setiap materi terkait dengan satu pelatihan)
    public function pelatihan()
    {
        return $this->belongsTo(Pelatihan::class);
    }
}
