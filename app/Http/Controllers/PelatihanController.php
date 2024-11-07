<?php

namespace App\Http\Controllers;

use App\Models\Pelatihan;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User; // Make sure this line is present
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PelatihanController extends Controller
{
    public function index()
    {
        // Mendapatkan daftar pelatihan
        $pelatihans = Pelatihan::all();
        return response()->json($pelatihans, 200);
    }

    public function store(Request $request)
    {
        // Validasi input pelatihan
        $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'foto_pelatihan' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validasi foto pelatihan
            'kuota' => 'nullable|integer',
            'instruktur' => 'nullable|string',
        ]);

        // Hitung lama pelatihan (selisih tanggal selesai dan mulai)
        $tanggal_mulai = Carbon::parse($request->tanggal_mulai);
        $tanggal_selesai = Carbon::parse($request->tanggal_selesai);
        $lama_pelatihan = $tanggal_mulai->diffInDays($tanggal_selesai);

        // Simpan pelatihan baru dengan lama pelatihan
        $pelatihanData = [
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'kuota' => $request->kuota,
            'instruktur' => $request->instruktur,
            'lama_pelatihan' => $lama_pelatihan, // Menyimpan lama pelatihan
        ];

        // Simpan foto pelatihan jika ada
        if ($request->hasFile('foto_pelatihan')) {
            $filePath = $request->file('foto_pelatihan')->store('uploads/foto_pelatihan', 'public');
            $pelatihanData['foto_pelatihan'] = $filePath; // Menyimpan path file
        }

        $pelatihan = Pelatihan::create($pelatihanData);

        return response()->json($pelatihan, 201);
    }

    // Fungsi untuk mendapatkan detail pelatihan berdasarkan ID
    public function show($id)
    {
        // Cari pelatihan berdasarkan ID
        $pelatihan = Pelatihan::find($id);

        // Jika pelatihan tidak ditemukan, kembalikan respon 404
        if (!$pelatihan) {
            return response()->json(['message' => 'Pelatihan tidak ditemukan'], 404);
        }

        // Kembalikan respon dengan detail pelatihan
        return response()->json($pelatihan, 200);
    }

    // Method untuk mengambil pelatihan berdasarkan instruktur (user)
    public function getPelatihanByInstruktur()
    {
        // Mendapatkan user yang sedang login
        $user = Auth::user();

        if ($user instanceof \App\Models\User) {
            // Periksa apakah user adalah pengajar
            if ($user->isPengajar()) {
                // Ambil pelatihan berdasarkan instruktur (nama instruktur = nama user yang login)
                $pelatihans = Pelatihan::where('instruktur', $user->name)->get();

                // Jika pelatihan ditemukan, kembalikan dengan respons JSON
                return response()->json([
                    'status' => 'success',
                    'data' => $pelatihans
                ], 200);
            }

            // Jika bukan pengajar, kembalikan error
            return response()->json([
                'status' => 'error',
                'message' => 'Anda bukan pengajar'
            ], 403);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'User is not an instance of the expected model',
            ], 500);
        }
    }
}
