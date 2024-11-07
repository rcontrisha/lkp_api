<?php

namespace App\Http\Controllers;

use App\Models\Tugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TugasController extends Controller
{
    public function store(Request $request)
    {
        // Validasi input tugas
        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'pelatihan_id' => 'required|exists:pelatihans,id',
            'batas_waktu' => 'required|date',  // Validasi batas waktu sebagai tanggal
        ]);

        // Simpan tugas ke database
        $tugas = Tugas::create([
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'pelatihan_id' => $request->pelatihan_id,
            'pengajar_id' => Auth::id(),
            'batas_waktu' => $request->batas_waktu,  // Menyimpan batas waktu
        ]);

        return response()->json($tugas, 201);
    }

    // Fungsi untuk mengambil daftar tugas berdasarkan pelatihan
    public function index($pelatihan_id)
    {
        // Validasi bahwa pelatihan_id ada dalam tabel pelatihans
        $validated = validator(['pelatihan_id' => $pelatihan_id], [
            'pelatihan_id' => 'exists:pelatihans,id'
        ]);

        if ($validated->fails()) {
            return response()->json(['message' => 'Invalid pelatihan_id'], 400);
        }

        // Ambil semua tugas yang berkaitan dengan pelatihan_id
        $tugas = Tugas::where('pelatihan_id', $pelatihan_id)->get();

        // Jika tidak ada tugas, kembalikan response kosong
        if ($tugas->isEmpty()) {
            return response()->json(['message' => 'No tasks found for this training.'], 404);
        }

        return response()->json($tugas, 200);
    }
}
