<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\AbsensiPelajaran;
use App\Models\Siswa;
use App\Models\JadwalKelas;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class AbsensiPelajaranController extends Controller
{
    /**
     * Tampilkan halaman scan QR untuk pelajaran tertentu
     */
    public function index($jadwalKelasId)
    {
        // Ambil jadwal kelas
        $jadwalKelas = JadwalKelas::with('kelas.jurusan')->findOrFail($jadwalKelasId);
        
        // Validasi apakah jadwal aktif
        if (!$jadwalKelas->is_active) {
            return redirect()->route('jadwal-kelas.index')
                ->with('error', 'Jadwal pelajaran ini sedang tidak aktif!');
        }
        
        // Validasi waktu pelajaran
        $now = Carbon::now('Asia/Jakarta');
        $hariSekarang = $this->getHariIndonesia();
        
        if (strtolower($jadwalKelas->hari) !== $hariSekarang) {
            return redirect()->route('jadwal-kelas.index')
                ->with('error', 'Jadwal pelajaran ini tidak berlaku untuk hari ini!');
        }
        
        $jamMasuk = Carbon::parse($jadwalKelas->jam_masuk);
        $jamKeluar = Carbon::parse($jadwalKelas->jam_keluar);
        $isWaktuPelajaran = $now->between($jamMasuk->copy()->subMinutes(15), $jamKeluar->copy()->addMinutes(15));
        
        if (!$isWaktuPelajaran) {
            return redirect()->route('jadwal-kelas.index')
                ->with('error', 'Bukan waktu untuk pelajaran ini!');
        }
        
        // Tentukan jenis absen berdasarkan waktu
        $isMasukTime = $now->between($jamMasuk->copy()->subMinutes(15), $jamMasuk->copy()->addMinutes(15));
        $isKeluarTime = $now->between($jamKeluar->copy()->subMinutes(15), $jamKeluar->copy()->addMinutes(15));
        $isDuringClass = $now->between($jamMasuk, $jamKeluar);
        
        $absenType = 'masuk'; // default
        if ($isKeluarTime || $isDuringClass) {
            $absenType = 'keluar';
        }
        
        // Informasi tambahan untuk view
        $waktuDisplay = $now->format('H:i');
        $hariDisplay = ucfirst($hariSekarang);
        
        return view('absensi.pelajaran', compact(
            'jadwalKelas',
            'absenType',
            'waktuDisplay',
            'hariDisplay'
        ));
    }
    
    /**
     * Proses scan QR Code untuk absensi pelajaran
     */
    public function scanQr(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'qr_code' => 'required|string',
                'jadwal_kelas_id' => 'required|exists:jadwal_kelas,id',
                'type' => 'required|in:masuk,keluar'
            ]);

            $qrCode = $request->qr_code;
            $jadwalKelasId = $request->jadwal_kelas_id;
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

            // Ambil data jadwal kelas
            $jadwalKelas = JadwalKelas::with('kelas.jurusan')->find($jadwalKelasId);
            
            if (!$jadwalKelas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jadwal kelas tidak ditemukan!'
                ], 404);
            }
            
            // Validasi kelas siswa dengan jadwal
            if ($siswa->kelas_id !== $jadwalKelas->kelas_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Siswa tidak terdaftar di kelas untuk jadwal pelajaran ini!'
                ], 400);
            }
            
            // Validasi apakah jadwal ini valid untuk waktu sekarang
            $validationResult = $this->validatePelajaranTime($jadwalKelas, $now, $type);
            if (!$validationResult['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $validationResult['message']
                ], 400);
            }
            
            if ($type === 'masuk') {
                return $this->prosesAbsenMasuk($siswa, $jadwalKelas, $today, $now);
            } else {
                return $this->prosesAbsenKeluar($siswa, $jadwalKelas, $today, $now);
            }
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in scanQr Pelajaran: ' . $e->getMessage(), [
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
     * Validasi apakah jadwal pelajaran valid untuk waktu sekarang
     */
    private function validatePelajaranTime($jadwalKelas, $now, $type)
    {
        $hariSekarang = $this->getHariIndonesia();
        $jamSekarang = $now->format('H:i:s');
        
        // Cek apakah hari ini sesuai dengan jadwal
        if (strtolower($jadwalKelas->hari) !== $hariSekarang) {
            return [
                'valid' => false,
                'message' => 'Pelajaran ini tidak berlaku untuk hari ' . ucfirst($hariSekarang) . '. Pelajaran berlaku untuk: ' . ucfirst($jadwalKelas->hari)
            ];
        }
        
        // Cek apakah jadwal aktif
        if (!$jadwalKelas->is_active) {
            return [
                'valid' => false,
                'message' => 'Jadwal pelajaran ini sedang tidak aktif.'
            ];
        }
        
        // Validasi waktu untuk absen masuk
        if ($type === 'masuk') {
            // Waktu mulai: 15 menit sebelum jam masuk
            $waktuMulai = Carbon::createFromFormat('H:i:s', $jadwalKelas->jam_masuk)->subMinutes(15)->format('H:i:s');
            // Waktu selesai: 15 menit setelah jam masuk (atau batas telat jika ada)
            $waktuSelesai = $jadwalKelas->batas_telat 
                ? Carbon::createFromFormat('H:i:s', $jadwalKelas->batas_telat)->format('H:i:s')
                : Carbon::createFromFormat('H:i:s', $jadwalKelas->jam_masuk)->addMinutes(15)->format('H:i:s');
            
            if ($jamSekarang < $waktuMulai) {
                $waktuMulaiDisplay = Carbon::createFromFormat('H:i:s', $waktuMulai)->format('H:i');
                return [
                    'valid' => false,
                    'message' => "Absen masuk untuk pelajaran {$jadwalKelas->mata_pelajaran} belum dimulai. Waktu mulai: {$waktuMulaiDisplay}"
                ];
            }
            
            if ($jamSekarang > $waktuSelesai) {
                $waktuSelesaiDisplay = Carbon::createFromFormat('H:i:s', $waktuSelesai)->format('H:i');
                return [
                    'valid' => false,
                    'message' => "Waktu absen masuk untuk pelajaran {$jadwalKelas->mata_pelajaran} telah berakhir. Batas waktu: {$waktuSelesaiDisplay}"
                ];
            }
        }
        
        // Validasi waktu untuk absen keluar
        if ($type === 'keluar') {
            // Waktu mulai: 15 menit sebelum jam keluar
            $waktuMulai = Carbon::createFromFormat('H:i:s', $jadwalKelas->jam_keluar)->subMinutes(15)->format('H:i:s');
            // Waktu selesai: 15 menit setelah jam keluar
            $waktuSelesai = Carbon::createFromFormat('H:i:s', $jadwalKelas->jam_keluar)->addMinutes(15)->format('H:i:s');
            
            if ($jamSekarang < $waktuMulai) {
                $waktuMulaiDisplay = Carbon::createFromFormat('H:i:s', $waktuMulai)->format('H:i');
                return [
                    'valid' => false,
                    'message' => "Absen keluar untuk pelajaran {$jadwalKelas->mata_pelajaran} belum dimulai. Waktu mulai: {$waktuMulaiDisplay}"
                ];
            }
            
            if ($jamSekarang > $waktuSelesai) {
                $waktuSelesaiDisplay = Carbon::createFromFormat('H:i:s', $waktuSelesai)->format('H:i');
                return [
                    'valid' => false,
                    'message' => "Waktu absen keluar untuk pelajaran {$jadwalKelas->mata_pelajaran} telah berakhir. Batas waktu: {$waktuSelesaiDisplay}"
                ];
            }
        }
        
        return ['valid' => true, 'message' => ''];
    }

    /**
     * Proses absen masuk
     */
    private function prosesAbsenMasuk($siswa, $jadwalKelas, $today, $now)
    {
        // Cek apakah sudah absen masuk untuk pelajaran ini hari ini
        $existingAbsensi = AbsensiPelajaran::where('nis', $siswa->nis)
            ->where('jadwal_kelas_id', $jadwalKelas->id)
            ->whereDate('tanggal', $today)
            ->first();
            
        if ($existingAbsensi && $existingAbsensi->jam_masuk) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan absen masuk untuk pelajaran ini hari ini!'
            ], 400);
        }

        // Tentukan status berdasarkan waktu
        $status = 'hadir';
        if ($jadwalKelas->batas_telat) {
            $batasTelat = Carbon::createFromFormat('H:i:s', $jadwalKelas->batas_telat)->format('H:i:s');
            $jamSekarang = $now->format('H:i:s');
            
            if ($jamSekarang > $batasTelat) {
                $status = 'telat';
            }
        }

        // Simpan atau update absensi
        $absensiData = [
            'nis' => $siswa->nis,
            'jadwal_kelas_id' => $jadwalKelas->id,
            'tanggal' => $today,
            'jam_masuk' => $now->format('H:i:s'),
            'status_masuk' => $status
        ];
        
        if ($existingAbsensi) {
            $existingAbsensi->update($absensiData);
            $absensi = $existingAbsensi;
        } else {
            $absensi = AbsensiPelajaran::create($absensiData);
        }

        return response()->json([
            'success' => true,
            'message' => 'Absen masuk berhasil!',
            'data' => [
                'nis' => $siswa->nis,
                'nama' => $siswa->nama,
                'kelas' => $siswa->kelas->nama_lengkap,
                'jurusan' => $siswa->kelas->jurusan->nama_jurusan,
                'mata_pelajaran' => $jadwalKelas->mata_pelajaran,
                'guru_pengampu' => $jadwalKelas->guru_pengampu,
                'jam_masuk' => $now->format('H:i:s'),
                'status' => $status
            ]
        ]);
    }

    /**
     * Proses absen keluar
     */
    private function prosesAbsenKeluar($siswa, $jadwalKelas, $today, $now)
    {
        // Cek apakah sudah absen masuk untuk pelajaran ini hari ini
        $absensi = AbsensiPelajaran::where('nis', $siswa->nis)
            ->where('jadwal_kelas_id', $jadwalKelas->id)
            ->whereDate('tanggal', $today)
            ->whereNotNull('jam_masuk')
            ->first();

        if (!$absensi) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum melakukan absen masuk untuk pelajaran ini hari ini!'
            ], 400);
        }

        // Cek apakah sudah absen keluar
        if ($absensi->jam_keluar) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan absen keluar untuk pelajaran ini hari ini!'
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
                'mata_pelajaran' => $jadwalKelas->mata_pelajaran,
                'guru_pengampu' => $jadwalKelas->guru_pengampu,
                'jam_masuk' => $absensi->jam_masuk,
                'jam_keluar' => $now->format('H:i:s'),
                'status' => $absensi->status_masuk
            ]
        ]);
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
}
