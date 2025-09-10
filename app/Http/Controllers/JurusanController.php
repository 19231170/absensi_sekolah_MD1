<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class JurusanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $jurusan = Jurusan::withCount('kelas', 'siswa')->orderBy('nama_jurusan')->get();
            return view('admin.jurusan.index', compact('jurusan'));
        } catch (\Exception $e) {
            Log::error('Error saat mengambil data jurusan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat data jurusan: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.jurusan.create');
    }

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

    /**
     * Store a newly created jurusan via web form.
     */
    public function storeForm(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_jurusan' => 'required|string|max:255|unique:jurusan,nama_jurusan',
            'kode_jurusan' => 'required|string|max:10|unique:jurusan,kode_jurusan',
            'deskripsi' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->route('jurusan.create')
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $jurusan = Jurusan::create([
                'nama_jurusan' => $request->nama_jurusan,
                'kode_jurusan' => $request->kode_jurusan,
                'deskripsi' => $request->deskripsi
            ]);

            return redirect()->route('jurusan.index')
                ->with('success', 'Jurusan berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Error saat menambahkan jurusan: ' . $e->getMessage());
            return redirect()->route('jurusan.create')
                ->with('error', 'Gagal menambahkan jurusan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Jurusan $jurusan)
    {
        try {
            $jurusan->load('kelas.siswa');
            return view('admin.jurusan.show', compact('jurusan'));
        } catch (\Exception $e) {
            Log::error('Error saat menampilkan detail jurusan: ' . $e->getMessage());
            return redirect()->route('jurusan.index')
                ->with('error', 'Gagal memuat detail jurusan: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Jurusan $jurusan)
    {
        return view('admin.jurusan.edit', compact('jurusan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Jurusan $jurusan)
    {
        $validator = Validator::make($request->all(), [
            'nama_jurusan' => 'required|string|max:255|unique:jurusan,nama_jurusan,' . $jurusan->id,
            'kode_jurusan' => 'required|string|max:10|unique:jurusan,kode_jurusan,' . $jurusan->id,
            'deskripsi' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->route('jurusan.edit', $jurusan->id)
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $jurusan->update([
                'nama_jurusan' => $request->nama_jurusan,
                'kode_jurusan' => $request->kode_jurusan,
                'deskripsi' => $request->deskripsi
            ]);

            return redirect()->route('jurusan.index')
                ->with('success', 'Jurusan berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Error saat memperbarui jurusan: ' . $e->getMessage());
            return redirect()->route('jurusan.edit', $jurusan->id)
                ->with('error', 'Gagal memperbarui jurusan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Jurusan $jurusan)
    {
        try {
            // Check if jurusan has associated kelas
            $kelasCount = $jurusan->kelas()->count();
            if ($kelasCount > 0) {
                return redirect()->route('jurusan.index')
                    ->with('error', "Jurusan tidak dapat dihapus karena masih memiliki {$kelasCount} kelas.");
            }

            $jurusan->delete();
            return redirect()->route('jurusan.index')
                ->with('success', 'Jurusan berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Error saat menghapus jurusan: ' . $e->getMessage());
            return redirect()->route('jurusan.index')
                ->with('error', 'Gagal menghapus jurusan: ' . $e->getMessage());
        }
    }

    /**
     * Get all jurusan for dropdown via AJAX
     */
    public function getAllJurusan(): JsonResponse
    {
        try {
            $jurusan = Jurusan::orderBy('nama_jurusan')->get(['id', 'nama_jurusan', 'kode_jurusan']);
            
            return response()->json([
                'success' => true,
                'jurusan' => $jurusan
            ]);
        } catch (\Exception $e) {
            Log::error('Error saat mengambil data jurusan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data jurusan: ' . $e->getMessage()
            ], 500);
        }
    }
}
