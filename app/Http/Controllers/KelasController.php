<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\Jurusan;
use Illuminate\Support\Facades\Validator;

class KelasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $kelas = Kelas::with('jurusan')->orderBy('tingkat')->orderBy('nama_kelas')->get();
            return view('admin.kelas.index', compact('kelas'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memuat data kelas: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $jurusans = Jurusan::orderBy('nama_jurusan')->get();
        $tingkatOptions = [10, 11, 12, 13];
        return view('admin.kelas.create', compact('jurusans', 'tingkatOptions'));
    }

    /**
     * Store a newly created kelas in storage.
     */
    public function store(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'jurusan_id' => 'required|exists:jurusan,id',
            'tingkat' => 'required|integer|in:10,11,12,13',
            'nama_kelas' => 'required|string|max:255',
            'kapasitas' => 'nullable|integer|min:1|max:50',
            'keterangan' => 'nullable|string'
        ], [
            'jurusan_id.required' => 'Jurusan harus dipilih',
            'jurusan_id.exists' => 'Jurusan tidak valid',
            'tingkat.required' => 'Tingkat harus dipilih',
            'tingkat.integer' => 'Tingkat harus berupa angka',
            'tingkat.in' => 'Tingkat harus 10, 11, 12, atau 13',
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
     * Display the specified resource.
     */
    public function show(Kelas $kelas)
    {
        try {
            $kelas->load('jurusan', 'siswa');
            return view('admin.kelas.show', compact('kelas'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memuat detail kelas: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Kelas $kelas)
    {
        $jurusans = Jurusan::orderBy('nama_jurusan')->get();
        $tingkatOptions = [10, 11, 12, 13];
        return view('admin.kelas.edit', compact('kelas', 'jurusans', 'tingkatOptions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Kelas $kelas)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'jurusan_id' => 'required|exists:jurusan,id',
            'tingkat' => 'required|integer|in:10,11,12,13',
            'nama_kelas' => 'required|string|max:255',
            'kapasitas' => 'nullable|integer|min:1|max:50',
            'keterangan' => 'nullable|string',
            'is_active' => 'boolean'
        ], [
            'jurusan_id.required' => 'Jurusan harus dipilih',
            'jurusan_id.exists' => 'Jurusan tidak valid',
            'tingkat.required' => 'Tingkat harus dipilih',
            'tingkat.integer' => 'Tingkat harus berupa angka',
            'tingkat.in' => 'Tingkat harus 10, 11, 12, atau 13',
            'nama_kelas.required' => 'Nama kelas harus diisi',
            'nama_kelas.max' => 'Nama kelas maksimal 255 karakter',
            'kapasitas.integer' => 'Kapasitas harus berupa angka',
            'kapasitas.min' => 'Kapasitas minimal 1 siswa',
            'kapasitas.max' => 'Kapasitas maksimal 50 siswa'
        ]);

        if ($validator->fails()) {
            return redirect()->route('kelas.edit', $kelas->id)
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Check if updated class name already exists for another record
            $existingKelas = Kelas::where('jurusan_id', $request->jurusan_id)
                                ->where('tingkat', $request->tingkat)
                                ->where('nama_kelas', $request->nama_kelas)
                                ->where('id', '!=', $kelas->id)
                                ->first();

            if ($existingKelas) {
                return redirect()->route('kelas.edit', $kelas->id)
                    ->with('error', 'Kelas dengan nama tersebut sudah ada!')
                    ->withInput();
            }

            // Update kelas
            $kelas->update([
                'jurusan_id' => $request->jurusan_id,
                'tingkat' => $request->tingkat,
                'nama_kelas' => $request->nama_kelas,
                'kapasitas' => $request->kapasitas ?? 40,
                'keterangan' => $request->keterangan,
                'is_active' => $request->has('is_active')
            ]);

            return redirect()->route('kelas.index')
                ->with('success', 'Kelas berhasil diperbarui!');

        } catch (\Exception $e) {
            return redirect()->route('kelas.edit', $kelas->id)
                ->with('error', 'Gagal memperbarui kelas: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kelas $kelas)
    {
        try {
            // Check if kelas has associated siswa
            $siswaCount = $kelas->siswa()->count();
            if ($siswaCount > 0) {
                return redirect()->route('kelas.index')
                    ->with('error', "Kelas tidak dapat dihapus karena masih memiliki {$siswaCount} siswa.");
            }

            $kelas->delete();
            return redirect()->route('kelas.index')
                ->with('success', 'Kelas berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('kelas.index')
                ->with('error', 'Gagal menghapus kelas: ' . $e->getMessage());
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
