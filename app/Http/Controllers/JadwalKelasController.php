<?php

namespace App\Http\Controllers;

use App\Models\JadwalKelas;
use App\Models\Kelas;
use App\Models\Jurusan;
use Illuminate\Http\Request;
use Carbon\Carbon;

class JadwalKelasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $hari = $request->get('hari');
        $kelasId = $request->get('kelas_id');
        
        $query = JadwalKelas::with('kelas.jurusan');
        
        if ($hari) {
            $query->untukHari($hari);
        }
        
        if ($kelasId) {
            $query->untukKelas($kelasId);
        }
        
        $jadwal = $query->orderBy('hari')
                       ->orderBy('jam_masuk')
                       ->get();
        
        // Kelompokkan jadwal berdasarkan hari
        $jadwalPerHari = $jadwal->groupBy('hari');
        
        // Urutan hari yang benar
        $urutanHari = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];
        
        // Pisahkan jadwal berdasarkan sesi untuk setiap hari dengan urutan yang benar
        $jadwalTerorganisir = [];
        foreach ($urutanHari as $namaHari) {
            if (isset($jadwalPerHari[$namaHari])) {
                $jadwalHari = $jadwalPerHari[$namaHari];
                $jadwalTerorganisir[$namaHari] = [
                    'pagi' => $jadwalHari->filter(function($item) {
                        $jamMasuk = Carbon::parse($item->jam_masuk)->format('H:i');
                        return $jamMasuk < '12:00';
                    }),
                    'siang' => $jadwalHari->filter(function($item) {
                        $jamMasuk = Carbon::parse($item->jam_masuk)->format('H:i');
                        return $jamMasuk >= '12:00';
                    })
                ];
            }
        }
        
        // Untuk backward compatibility, tetap sediakan variabel lama
        $jadwalPagi = $jadwal->filter(function($item) {
            $jamMasuk = Carbon::parse($item->jam_masuk)->format('H:i');
            return $jamMasuk < '12:00';
        });
        
        $jadwalSiang = $jadwal->filter(function($item) {
            $jamMasuk = Carbon::parse($item->jam_masuk)->format('H:i');
            return $jamMasuk >= '12:00';
        });
        
        $kelas = Kelas::with('jurusan')->get();
        $hariOptions = [
            'senin' => 'Senin',
            'selasa' => 'Selasa',
            'rabu' => 'Rabu', 
            'kamis' => 'Kamis',
            'jumat' => 'Jumat',
            'sabtu' => 'Sabtu'
        ];
        
        // Dapatkan hari saat ini dalam bahasa Indonesia
        $hariMapping = [
            'Monday' => 'senin',
            'Tuesday' => 'selasa', 
            'Wednesday' => 'rabu',
            'Thursday' => 'kamis',
            'Friday' => 'jumat',
            'Saturday' => 'sabtu',
            'Sunday' => 'minggu'
        ];
        
        $hariInggris = Carbon::now('Asia/Jakarta')->format('l'); // Monday, Tuesday, etc.
        $hariHariIni = $hariMapping[$hariInggris] ?? 'senin';
        
        return view('jadwal-kelas.index', compact(
            'jadwal', 
            'jadwalPagi', 
            'jadwalSiang',
            'jadwalTerorganisir',
            'kelas', 
            'hariOptions', 
            'hari', 
            'kelasId',
            'hariHariIni'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kelas = Kelas::with('jurusan')->get();
        $jurusan = Jurusan::orderBy('nama_jurusan')->get();
        $hariOptions = [
            'senin' => 'Senin',
            'selasa' => 'Selasa',
            'rabu' => 'Rabu',
            'kamis' => 'Kamis', 
            'jumat' => 'Jumat',
            'sabtu' => 'Sabtu'
        ];
        
        return view('jadwal-kelas.create', compact('kelas', 'jurusan', 'hariOptions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'hari' => 'required|in:senin,selasa,rabu,kamis,jumat,sabtu',
            'jam_masuk' => 'required|date_format:H:i',
            'jam_keluar' => 'required|date_format:H:i|after:jam_masuk',
            'batas_telat' => 'nullable|date_format:H:i',
            'mata_pelajaran' => 'nullable|string|max:255',
            'guru_pengampu' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string'
        ]);

        // Check for time conflicts
        $conflicts = JadwalKelas::where('kelas_id', $request->kelas_id)
            ->where('hari', $request->hari)
            ->where('is_active', true)
            ->where(function ($query) use ($request) {
                $query->whereBetween('jam_masuk', [$request->jam_masuk, $request->jam_keluar])
                      ->orWhereBetween('jam_keluar', [$request->jam_masuk, $request->jam_keluar])
                      ->orWhere(function ($q) use ($request) {
                          $q->where('jam_masuk', '<=', $request->jam_masuk)
                            ->where('jam_keluar', '>=', $request->jam_keluar);
                      });
            })
            ->exists();

        if ($conflicts) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['jam_masuk' => 'Terdapat konflik waktu dengan jadwal yang sudah ada.']);
        }

        JadwalKelas::create($request->all());

        return redirect()->route('jadwal-kelas.index')
            ->with('success', 'Jadwal persesi berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(JadwalKelas $jadwalKelas)
    {
        $jadwalKelas->load('kelas.jurusan');
        return view('jadwal-kelas.show', compact('jadwalKelas'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(JadwalKelas $jadwalKelas)
    {
        $kelas = Kelas::with('jurusan')->get();
        $hariOptions = [
            'senin' => 'Senin',
            'selasa' => 'Selasa',
            'rabu' => 'Rabu',
            'kamis' => 'Kamis',
            'jumat' => 'Jumat', 
            'sabtu' => 'Sabtu'
        ];
        
        return view('jadwal-kelas.edit', compact('jadwalKelas', 'kelas', 'hariOptions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, JadwalKelas $jadwalKelas)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'hari' => 'required|in:senin,selasa,rabu,kamis,jumat,sabtu',
            'jam_masuk' => 'required|date_format:H:i',
            'jam_keluar' => 'required|date_format:H:i|after:jam_masuk',
            'batas_telat' => 'nullable|date_format:H:i',
            'mata_pelajaran' => 'nullable|string|max:255',
            'guru_pengampu' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string'
        ]);

        // Check for time conflicts (exclude current record)
        $conflicts = JadwalKelas::where('kelas_id', $request->kelas_id)
            ->where('hari', $request->hari)
            ->where('is_active', true)
            ->where('id', '!=', $jadwalKelas->id)
            ->where(function ($query) use ($request) {
                $query->whereBetween('jam_masuk', [$request->jam_masuk, $request->jam_keluar])
                      ->orWhereBetween('jam_keluar', [$request->jam_masuk, $request->jam_keluar])
                      ->orWhere(function ($q) use ($request) {
                          $q->where('jam_masuk', '<=', $request->jam_masuk)
                            ->where('jam_keluar', '>=', $request->jam_keluar);
                      });
            })
            ->exists();

        if ($conflicts) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['jam_masuk' => 'Terdapat konflik waktu dengan jadwal yang sudah ada.']);
        }

        $jadwalKelas->update($request->all());

        return redirect()->route('jadwal-kelas.index')
            ->with('success', 'Jadwal persesi berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JadwalKelas $jadwalKelas)
    {
        $jadwalKelas->delete();

        return redirect()->route('jadwal-kelas.index')
            ->with('success', 'Jadwal persesi berhasil dihapus.');
    }

    /**
     * Toggle active status
     */
    public function toggleActive(JadwalKelas $jadwalKelas)
    {
        $jadwalKelas->update(['is_active' => !$jadwalKelas->is_active]);

        $status = $jadwalKelas->is_active ? 'diaktifkan' : 'dinonaktifkan';
        
        return redirect()->route('jadwal-kelas.index')
            ->with('success', "Jadwal persesi berhasil {$status}.");
    }
}
