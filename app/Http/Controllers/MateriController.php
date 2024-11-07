<?php

namespace App\Http\Controllers;

use App\Models\Materi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MateriController extends Controller
{
    // Fungsi untuk menyimpan materi baru
    public function store(Request $request)
    {
        // Validasi input materi
        $request->validate([
            'file_path' => 'nullable|mimes:pdf,docx|max:10240', // File bisa tidak wajib
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'link_video' => 'nullable|url',
            'pelatihan_id' => 'required|exists:pelatihans,id',
        ]);
 
        // Inisialisasi filePath sebagai null jika tidak ada file di-upload
        $filePath = null;

        // Simpan file materi jika ada
        if ($request->hasFile('file_path')) {
            $file = $request->file('file_path');

            // Ambil nama asli file
            $originalFileName = $file->getClientOriginalName();
 
            // Tambahkan timestamp atau ID unik untuk mencegah bentrok nama file
            $timestamp = time(); // Bisa juga diganti dengan uniqid() atau ID lainnya
            $fileName = $timestamp . '_' . $originalFileName;
             
            // Simpan file dengan nama yang tidak diacak
            $filePath = $file->storeAs('uploads/materi', $fileName, 'public');
        }
 
        // Simpan materi ke database
        $materi = Materi::create([
            'file_path' => $filePath,
            'pelatihan_id' => $request->pelatihan_id,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'link_video' => $request->link_video,
        ]);
 
        return response()->json($materi, 201);
    }
  
    // Fungsi untuk menampilkan materi berdasarkan id pelatihan
    public function index($pelatihan_id)
    {
        // Ambil materi yang berkaitan dengan pelatihan tertentu
        $materis = Materi::where('pelatihan_id', $pelatihan_id)->get();

        // Jika tidak ada materi, kembalikan response kosong
        if ($materis->isEmpty()) {
            return response()->json(['message' => 'No materials found for this training.'], 404);
        }

        // Mapping untuk menambahkan URL file ke dalam response
        $materis = $materis->map(function($materi) {
            $materi->file_url = Storage::url($materi->file_path); // Membuat URL akses file
            return $materi;
        });

        return response()->json($materis, 200);
    }

    // Fungsi untuk mengedit materi yang sudah ada
    public function updateInfo(Request $request, $id)
    {
        Log::info($request->all());  // Melihat semua data yang diterima
        Log::info('Judul Field: ' . $request->input('judul'));

        // Validasi input yang masuk
        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'link_video' => 'nullable|url',
        ], [
            'judul.required' => 'Field judul tidak boleh kosong!',
        ]);        

        // Ambil data teks
        $judul = $request->get('judul');
        $deskripsi = $request->get('deskripsi');

        // Cari materi berdasarkan ID
        $materi = Materi::find($id);

        // Jika materi tidak ditemukan, kembalikan pesan error
        if (!$materi) {
            return response()->json(['message' => 'Materi not found.'], 404);
        }

        // Update data materi di database
        $materi->judul = $judul;
        $materi->deskripsi = $deskripsi;
        $materi->link_video = $request->link_video;
        $materi->save(); // Simpan perubahan

        return response()->json(['message' => 'Materi info berhasil diupdate', 'materi' => $materi], 200);
    }

    public function uploadFile(Request $request, $id)
    {
        Log::info($request->all());  // Melihat semua data yang diterima

        // Validasi input file
        $request->validate([
            'file_path' => 'required|file|mimes:pdf,docx|max:10240', // Validasi file upload
        ], [
            'file_path.required' => 'File tidak boleh kosong!',
            'file_path.mimes' => 'File harus berupa pdf atau docx!',
            'file_path.max' => 'Ukuran file tidak boleh lebih dari 10MB!',
        ]);        

        // Cari materi berdasarkan ID
        $materi = Materi::find($id);

        // Jika materi tidak ditemukan, kembalikan pesan error
        if (!$materi) {
            return response()->json(['message' => 'Materi not found.'], 404);
        }

        // Hapus file lama jika ada
        if ($materi->file_path && Storage::exists($materi->file_path)) {
            Storage::delete($materi->file_path);
        }

        // Ambil file dari request
        $file = $request->file('file_path');

        // Ambil nama asli file
        $originalFileName = $file->getClientOriginalName();

        // Tambahkan timestamp untuk mencegah bentrok nama file
        $timestamp = time();
        $fileName = $timestamp . '_' . $originalFileName;

        // Simpan file di storage
        $filePath = $file->storeAs('uploads/materi', $fileName, 'public');
        $materi->file_path = $filePath; // Update file path di database
        $materi->save(); // Simpan perubahan

        return response()->json(['message' => 'File materi berhasil diupload', 'materi' => $materi], 200);
    }
}
