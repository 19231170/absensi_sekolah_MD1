<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Siswa;
use App\Models\JamSekolah;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class AbsensiController extends Controller
{
    /**
     * Tampilkan halaman scan QR
     */
    public function index()
    {
        // Ambil sesi yang berlaku untuk hari ini
        $hariIni = $this->getHariIndonesia();
        $jamSekolah = JamSekolah::aktif()
            ->untukHari($hariIni)
            ->orderBy('jenis_sesi')
            ->orderBy('jam_masuk')
            ->get();
            
        // Jika tidak ada sesi untuk hari ini, tampilkan semua sesi aktif
        if ($jamSekolah->isEmpty()) {
            $jamSekolah = JamSekolah::aktif()->get();
        }
            
        return view('absensi.index', compact('jamSekolah', 'hariIni'));
    }
    
    /**
     * Get nama hari dalam bahasa Indonesia
     */
    private function getHariIndonesia()
    {
        $hariMap = [
            'Sunday' => 'minggu',
            'Monday' => 'senin', 
            'Tuesday' => 'selasa',
            'Wednesday' => 'rabu',
            'Thursday' => 'kamis',
            'Friday' => 'jumat',
            'Saturday' => 'sabtu'
        ];
        
        $hariInggris = Carbon::now('Asia/Jakarta')->format('l');
        return $hariMap[$hariInggris] ?? 'unknown';
    }

    /**
     * Proses scan QR Code untuk absensi
     */
    public function scanQr(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'qr_code' => 'required|string',
                'jam_sekolah_id' => 'required|exists:jam_sekolah,id',
                'type' => 'required|in:masuk,keluar'
            ]);

            $qrCode = $request->qr_code;
            $jamSekolahId = $request->jam_sekolah_id;
            $type = $request->type;
            
            // Set timezone untuk memastikan menggunakan WIB
            $today = Carbon::today('Asia/Jakarta');
            $now = Carbon::now('Asia/Jakarta');

            // Cari siswa berdasarkan QR code
            $siswa = Siswa::with('kelas.jurusan')
                ->where('qr_code', $qrCode)
                ->aktif()
                ->first();

            if (!$siswa) {
                return response()->json([
                    'success' => false,
                    'message' => 'QR Code tidak ditemukan atau siswa tidak aktif!'
                ], 404);
            }

            // Ambil data jam sekolah
            $jamSekolah = JamSekolah::find($jamSekolahId);
            
            if (!$jamSekolah) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jam sekolah tidak ditemukan!'
                ], 404);
            }
            
            if ($type === 'masuk') {
                return $this->prosesAbsenMasuk($siswa, $jamSekolah, $today, $now);
            } else {
                return $this->prosesAbsenKeluar($siswa, $jamSekolah, $today, $now);
            }
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in scanQr: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.'
            ], 500);
        }
    }

    /**
     * Proses absen masuk
     */
    private function prosesAbsenMasuk($siswa, $jamSekolah, $today, $now)
    {
        // Cek apakah sudah absen masuk hari ini
        if (Absensi::sudahAbsenMasuk($siswa->nis, $today, $jamSekolah->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan absen masuk hari ini!'
            ], 400);
        }

        // Tentukan status berdasarkan waktu dengan parsing yang lebih aman
        try {
            $batasTelat = $jamSekolah->batas_telat; // Format: HH:MM:SS
            $jamSekarang = $now->format('H:i:s');

            $status = 'hadir';
            if ($jamSekarang > $batasTelat) {
                $status = 'telat';
            }
        } catch (\Exception $e) {
            // Fallback jika parsing gagal
            $status = 'hadir';
        }

        // Simpan absensi
        $absensi = Absensi::create([
            'nis' => $siswa->nis,
            'jam_sekolah_id' => $jamSekolah->id,
            'tanggal' => $today,
            'jam_masuk' => $now->format('H:i:s'),
            'status_masuk' => $status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absen masuk berhasil!',
            'data' => [
                'nis' => $siswa->nis,
                'nama' => $siswa->nama,
                'kelas' => $siswa->kelas->nama_lengkap,
                'jurusan' => $siswa->kelas->jurusan->nama_jurusan,
                'jam_masuk' => $now->format('H:i:s'),
                'status' => $status,
                'sesi' => $jamSekolah->nama_sesi
            ]
        ]);
    }

    /**
     * Proses absen keluar
     */
    private function prosesAbsenKeluar($siswa, $jamSekolah, $today, $now)
    {
        // Cek apakah sudah absen masuk hari ini
        $absensi = Absensi::where('nis', $siswa->nis)
            ->where('jam_sekolah_id', $jamSekolah->id)
            ->whereDate('tanggal', $today)
            ->whereNotNull('jam_masuk')
            ->first();

        if (!$absensi) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum melakukan absen masuk hari ini!'
            ], 400);
        }

        // Cek apakah sudah absen keluar
        if ($absensi->jam_keluar) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan absen keluar hari ini!'
            ], 400);
        }

        // Update absensi dengan jam keluar
        $absensi->update([
            'jam_keluar' => $now->format('H:i:s'),
            'status_keluar' => 'sudah_keluar'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absen keluar berhasil!',
            'data' => [
                'nis' => $siswa->nis,
                'nama' => $siswa->nama,
                'kelas' => $siswa->kelas->nama_lengkap,
                'jurusan' => $siswa->kelas->jurusan->nama_jurusan,
                'jam_masuk' => $absensi->jam_masuk,
                'jam_keluar' => $now->format('H:i:s'),
                'status' => $absensi->status_masuk,
                'sesi' => $jamSekolah->nama_sesi
            ]
        ]);
    }

    /**
     * Tampilkan laporan absensi
     */
    public function laporan(Request $request)
    {
        $tanggal = $request->get('tanggal', Carbon::today('Asia/Jakarta'));
        $jamSekolahId = $request->get('jam_sekolah_id');

        $query = Absensi::with(['siswa.kelas.jurusan', 'jamSekolah'])
            ->whereDate('tanggal', $tanggal);

        if ($jamSekolahId) {
            $query->where('jam_sekolah_id', $jamSekolahId);
        }

        $absensi = $query->orderBy('jam_masuk', 'desc')->get();
        $jamSekolah = JamSekolah::aktif()->get();

        return view('absensi.laporan', compact('absensi', 'jamSekolah', 'tanggal', 'jamSekolahId'));
    }
}
