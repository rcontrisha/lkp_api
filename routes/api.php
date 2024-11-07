<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PelatihanController;
use App\Http\Controllers\MateriController;
use App\Http\Controllers\TugasController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PengumpulanTugasController;
use App\Http\Controllers\UserTrainingController;
use App\Http\Controllers\ProfileController;

/*
|---------------------------------------------------------------------------
| API Routes
|---------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route untuk registrasi dan login
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Routes dengan autentikasi
Route::middleware('auth:sanctum')->group(function () {

    // Route untuk logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Route Pelatihan (Bisa diakses oleh semua pengguna yang terautentikasi)
    Route::get('/pelatihans', [PelatihanController::class, 'index']);
    Route::post('/pelatihans', [PelatihanController::class, 'store']);
    Route::get('/pelatihans/{id}', [PelatihanController::class, 'show']);
    
    Route::get('/materis/{id}', [MateriController::class, 'index']);

    Route::get('/tugas/{pelatihan_id}', [TugasController::class, 'index']);
    
    // Route untuk mengambil profil pengguna
    Route::get('/profile', [ProfileController::class, 'getProfile']);

    // Route untuk mengupdate alamat pengguna
    Route::post('/profile/update-alamat', [ProfileController::class, 'updateAlamat']);

    // Route untuk mengupdate password pengguna
    Route::post('/profile/update-password', [ProfileController::class, 'updatePassword']);

    Route::post('/profile/update-profile-picture', [ProfileController::class, 'updateProfilePicture']);

    // Route khusus untuk pengajar
    Route::middleware('isPengajar')->group(function () {
        // Route Materi - hanya pengajar yang bisa menambahkan materi
        Route::post('/materis', [MateriController::class, 'store']);
        
        Route::put('/materi/info/{id}', [MateriController::class, 'updateInfo']);
        Route::post('/materi/upload/{id}', [MateriController::class, 'uploadFile']);

        // Route Tugas - hanya pengajar yang bisa menambahkan tugas
        Route::post('/tugas', [TugasController::class, 'store']);

        // Route untuk mengambil daftar pelatihan dari seorang instruktur
        Route::get('/pelatihan/instruktur', [PelatihanController::class, 'getPelatihanByInstruktur']);

        // Route untuk mengambil daftar peserta pelatihan
        Route::get('/pelatihan/{pelatihan_id}/users', [UserTrainingController::class, 'getUsersByPelatihanId']);

        // Route untuk melihat daftar peserta yg mengumpulkan tugas
        Route::get('/tugas/{id}/pengumpulan', [PengumpulanTugasController::class, 'getPengumpulanTugas']);
    });

    // Route khusus untuk mahasiswa
    Route::middleware('isMahasiswa')->group(function () {
        // Route untuk pengumpulan tugas oleh mahasiswa
        Route::post('/pengumpulan-tugas', [PengumpulanTugasController::class, 'store']);

        // Route untuk cek apakah siswa sudah mengumpulkan tugas
        Route::get('/pengumpulan-tugas/cek', [PengumpulanTugasController::class, 'cekPengumpulanTugas']);

        Route::post('/user-training', [UserTrainingController::class, 'store']);
        Route::get('/user-training', [UserTrainingController::class, 'index']);
    });
});
