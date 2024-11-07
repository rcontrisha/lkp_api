<?php

namespace App\Http\Controllers;

use App\Models\UserTraining;
use App\Models\Pelatihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserTrainingController extends Controller
{
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'pelatihan_id' => 'required|exists:pelatihans,id',
            'full_name' => 'required|string|max:255',
            'nik' => 'required|string|max:20',
            'address' => 'required|string',
            'phone_number' => 'required|string|max:15',
        ]);

        // Simpan data ke database
        $userTraining = UserTraining::create([
            'user_id' => Auth::id(), // Ambil ID pengguna yang sedang login
            'pelatihan_id' => $request->pelatihan_id,
            'full_name' => $request->full_name,
            'nik' => $request->nik,
            'address' => $request->address,
            'phone_number' => $request->phone_number,
        ]);

        return response()->json($userTraining, 201);
    }

    public function index()
    {
        // Ambil semua data pelatihan yang diikuti oleh pengguna, beserta detail pelatihannya
        $userTrainings = UserTraining::where('user_id', Auth::id())
            ->with('pelatihan') // Load data pelatihan yang terkait
            ->get();

        return response()->json($userTrainings, 200);
    }

    // Tambahan function untuk mengambil data peserta pelatihan berdasarkan pelatihan_id
    public function getUsersByPelatihanId($pelatihan_id)
    {
        // Validasi apakah pelatihan_id ada
        $pelatihan = Pelatihan::findOrFail($pelatihan_id);

        // Ambil data user yang mengikuti pelatihan ini
        $userTrainings = UserTraining::where('pelatihan_id', $pelatihan_id)
            ->with('user') // Memuat data user terkait
            ->get();

        return response()->json($userTrainings, 200);
    }
}
