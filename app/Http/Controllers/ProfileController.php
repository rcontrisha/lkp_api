<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    /**
     * Mengambil profil pengguna yang sedang login.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProfile()
    {
        // Mendapatkan pengguna yang sedang login
        $user = Auth::user();

        if ($user) {
            // Mengembalikan respon dalam bentuk JSON
            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'alamat' => $user->alamat,
                    'role' => $user->role,
                    'profile_picture' => $user->profile_picture
                ]
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'User not authenticated',
            ], 401);
        }
    }

    /**
     * Mengubah alamat pengguna.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAlamat(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'alamat' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Mendapatkan pengguna yang sedang login
        $user = Auth::user(); // atau gunakan auth()->user()

        if ($user) {
            // Pastikan $user adalah instance dari model User
            if ($user instanceof \App\Models\User) {
                // Update alamat
                $user->alamat = $request->input('alamat');
                $user->save();  // Simpan perubahan ke database

                return response()->json([
                    'status' => 'success',
                    'message' => 'Alamat updated successfully',
                    'data' => $user,
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User is not an instance of the expected model',
                ], 500);
            }
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'User not authenticated',
            ], 401);
        }
    }
    /**
     * Mengubah password pengguna.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePassword(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Mendapatkan pengguna yang sedang login
        $user = Auth::user(); // Perubahan di sini

        if ($user instanceof \App\Models\User) {
           // Cek apakah password lama sesuai
            if ($user && Hash::check($request->input('current_password'), $user->password)) {
                // Update password
                $user->password = Hash::make($request->input('new_password'));
                $user->save();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Password updated successfully',
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Current password is incorrect',
                ], 401);
            }
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'User is not an instance of the expected model',
            ], 500);
        }
    }

    /**
     * Mengubah foto profil pengguna.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfilePicture(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Validasi untuk gambar
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Mendapatkan pengguna yang sedang login
        $user = Auth::user();

        if ($user instanceof \App\Models\User) {
            // Proses upload file gambar
            if ($request->hasFile('profile_picture')) {
                // Hapus file lama jika ada
                if ($user->profile_picture) {
                    $oldFilePath = public_path($user->profile_picture);
                    if (file_exists($oldFilePath)) {
                        unlink($oldFilePath); // Hapus file lama
                    }
                }

                // Simpan file baru dengan nama "profile_(nama user)"
                $filename = 'profile_' . strtolower(str_replace(' ', '_', $user->name)) . '.' . $request->file('profile_picture')->getClientOriginalExtension(); // Menghasilkan nama file unik
                $file = $request->file('profile_picture');
                $file->move(public_path('uploads/profile_pictures'), $filename); // Pindahkan file ke folder uploads/profile_pictures

                // Update path foto profil di database
                $user->profile_picture = 'uploads/profile_pictures/' . $filename;
                $user->save();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Profile picture updated successfully',
                    'data' => [
                        'profile_picture' => $user->profile_picture,
                    ],
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No file uploaded',
                ], 400);
            }
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'User is not an instance of the expected model',
            ], 500);
        }
    }
}