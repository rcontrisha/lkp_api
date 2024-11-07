<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengumpulanTugas;
use App\Models\Tugas; // Import model Tugas
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PengumpulanTugasController extends Controller
{
    public function store(Request $request)
    {
        // Validasi file dan tugas_id
        $request->validate([
            'tugas_id' => 'required|exists:tugas,id',
            'file_path' => 'required|mimes:pdf,doc,docx,zip,rar|max:10240', // Maks 10 MB
        ]);

        // Ambil tugas untuk mendapatkan pelatihan_id
        $tugas = Tugas::find($request->tugas_id);
        if (!$tugas) {
            return response()->json(['message' => 'Tugas tidak ditemukan'], 404);
        }

        // Simpan file tugas
        if ($request->hasFile('file_path')) {
            // Dapatkan nama asli dari file
            $originalName = $request->file('file_path')->getClientOriginalName();
            
            // Tentukan path penyimpanan dengan nama asli
            $path = $request->file('file_path')->storeAs('tugas_pengumpulan', $originalName);

            // Simpan informasi pengumpulan ke database
            $pengumpulan = new PengumpulanTugas();
            $pengumpulan->user_id = Auth::id(); // ID mahasiswa yang mengumpulkan tugas
            $pengumpulan->tugas_id = $request->tugas_id; // ID tugas
            $pengumpulan->file_path = $path; // Path file yang diupload
            $pengumpulan->pelatihan_id = $tugas->pelatihan_id; // Menyimpan pelatihan_id dari tugas
            $pengumpulan->save();

            return response()->json(['message' => 'Tugas berhasil dikumpulkan'], 201);
        }

        return response()->json(['message' => 'Gagal mengupload file'], 400);
    }

    public function cekPengumpulanTugas(Request $request)
    {
        // Validasi ID tugas dan pelatihan
        $request->validate([
            'tugas_id' => 'required|exists:tugas,id',
            'pelatihan_id' => 'required|integer', // Pastikan pelatihan_id adalah integer
        ]);

        // Ambil ID pengguna yang sedang login
        $userId = Auth::id();

        // Cek apakah pengguna sudah mengumpulkan tugas
        $pengumpulanTugas = PengumpulanTugas::where('tugas_id', $request->tugas_id)
            ->where('pelatihan_id', $request->pelatihan_id)
            ->where('user_id', $userId) // Pastikan hanya mengambil pengumpulan dari pengguna yang sedang login
            ->first(); // Ambil satu record pertama jika ada

        // Cek jika pengumpulan tugas ditemukan
        if ($pengumpulanTugas) {
            return response()->json(['message' => 'Anda sudah mengumpulkan tugas.', 'data' => $pengumpulanTugas], 200);
        }

        return response()->json(['message' => 'Anda belum mengumpulkan tugas.'], 404);
    }
    
    public function getPengumpulanTugas($tugas_id)
    {
        // Validasi ID tugas, hanya memeriksa parameter URL
        $validatedData = validator(['tugas_id' => $tugas_id], [
            'tugas_id' => 'required|exists:tugas,id',
        ]);
    
        if ($validatedData->fails()) {
            return response()->json(['message' => 'Tugas tidak ditemukan.'], 404);
        }
    
        // Ambil semua pengumpulan tugas untuk tugas tertentu beserta informasi user
        $pengumpulanTugasList = PengumpulanTugas::with('user')->where('tugas_id', $tugas_id)->get();
    
        // Cek jika tidak ada pengumpulan yang ditemukan
        if ($pengumpulanTugasList->isEmpty()) {
            return response()->json(['message' => 'Belum ada siswa yang mengumpulkan tugas ini.'], 404);
        }
    
        // Mengambil informasi siswa, file, detail pengguna, dan tanggal pengumpulan
        $result = $pengumpulanTugasList->map(function ($pengumpulan) {
            return [
                'user_id' => $pengumpulan->user->id, // ID pengguna
                'nama' => $pengumpulan->user->name, // Nama pengguna
                'email' => $pengumpulan->user->email, // Email pengguna
                'profile' => $pengumpulan->user->profile_picture,
                'original_name' => basename($pengumpulan->file_path), // Nama asli file
                'download_link' => Storage::url($pengumpulan->file_path), // Link download
                'tanggal_pengumpulan' => $pengumpulan->created_at->format('Y-m-d H:i:s'), // Tanggal pengumpulan
            ];
        });
    
        return response()->json(['data' => $result], 200);
    }
}
