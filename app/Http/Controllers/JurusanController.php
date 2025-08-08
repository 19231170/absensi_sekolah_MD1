<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class JurusanController extends Controller
{
    /**
     * Store a newly created jurusan via AJAX.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'nama_jurusan' => 'required|string|max:255|unique:jurusan,nama_jurusan',
                'kode_jurusan' => 'required|string|max:10|unique:jurusan,kode_jurusan',
                'deskripsi' => 'nullable|string'
            ]);

            $jurusan = Jurusan::create([
                'nama_jurusan' => $request->nama_jurusan,
                'kode_jurusan' => $request->kode_jurusan,
                'deskripsi' => $request->deskripsi
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Jurusan berhasil ditambahkan',
                'jurusan' => [
                    'id' => $jurusan->id,
                    'nama_jurusan' => $jurusan->nama_jurusan,
                    'kode_jurusan' => $jurusan->kode_jurusan
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan jurusan: ' . $e->getMessage()
            ], 500);
        }
    }
}
