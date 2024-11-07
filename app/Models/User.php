<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // Tambahkan role ke fillable
        'alamat',
        'profile_picture'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Method untuk memeriksa apakah user adalah mahasiswa
    public function isMahasiswa()
    {
        return $this->role === 'user';
    }

    // Method untuk memeriksa apakah user adalah pengajar
    public function isPengajar()
    {
        return $this->role === 'pengajar';
    }

    // Relasi dengan model PengumpulanTugas
    public function pengumpulanTugas()
    {
        return $this->hasMany(PengumpulanTugas::class);
    }
}