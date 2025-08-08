<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\Jurusan;
use Illuminate\Support\Facades\Validator;

class KelasController extends Controller
{
    /**
     * Store a newly created kelas in storage.
     */
    public function store(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'jurusan_id' => 'required|exists:jurusan,id',
            'tingkat' => 'required|in:X,XI,XII',
            'nama_kelas' => 'required|string|max:255',
            'kapasitas' => 'nullable|integer|min:1|max:50',
            'keterangan' => 'nullable|string'
        ], [
            'jurusan_id.required' => 'Jurusan harus dipilih',
            'jurusan_id.exists' => 'Jurusan tidak valid',
            'tingkat.required' => 'Tingkat harus dipilih',
            'tingkat.in' => 'Tingkat harus X, XI, atau XII',
            'nama_kelas.required' => 'Nama kelas harus diisi',
            'nama_kelas.max' => 'Nama kelas maksimal 255 karakter',
            'kapasitas.integer' => 'Kapasitas harus berupa angka',
            'kapasitas.min' => 'Kapasitas minimal 1 siswa',
            'kapasitas.max' => 'Kapasitas maksimal 50 siswa'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Check if kelas already exists
            $existingKelas = Kelas::where('jurusan_id', $request->jurusan_id)
                                  ->where('tingkat', $request->tingkat)
                                  ->where('nama_kelas', $request->nama_kelas)
                                  ->first();

            if ($existingKelas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kelas dengan nama tersebut sudah ada!'
                ], 422);
            }

            // Create new kelas
            $kelas = Kelas::create([
                'jurusan_id' => $request->jurusan_id,
                'tingkat' => $request->tingkat,
                'nama_kelas' => $request->nama_kelas,
                'kapasitas' => $request->kapasitas,
                'keterangan' => $request->keterangan,
                'is_active' => true
            ]);

            // Load jurusan relationship
            $kelas->load('jurusan');

            return response()->json([
                'success' => true,
                'message' => 'Kelas berhasil ditambahkan!',
                'kelas' => [
                    'id' => $kelas->id,
                    'display_name' => $kelas->tingkat . ' ' . $kelas->nama_kelas . ' - ' . $kelas->jurusan->nama_jurusan
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan kelas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all jurusan for dropdown
     */
    public function getJurusan()
    {
        try {
            $jurusan = Jurusan::where('is_active', true)
                             ->orderBy('nama_jurusan')
                             ->get(['id', 'nama_jurusan']);

            return response()->json([
                'success' => true,
                'jurusan' => $jurusan
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data jurusan: ' . $e->getMessage()
            ], 500);
        }
    }
}
