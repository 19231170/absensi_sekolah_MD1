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
        $jamMasuk = Carbon::parse($jadwalKelas->jam_masuk);
        $jamKeluar = Carbon::parse($jadwalKelas->jam_keluar);
        
        // Debug informasi untuk membantu troubleshooting
        Log::info('Debug AbsensiPelajaran index:', [
            'now' => $now->format('H:i:s'),
            'jam_masuk' => $jamMasuk->format('H:i:s'),
            'jam_keluar' => $jamKeluar->format('H:i:s'),
            'batas_telat' => $jadwalKelas->batas_telat
        ]);
        
        // Logika untuk menentukan jenis absensi:
        // 1. Jika dalam 30 menit sebelum jam masuk sampai dengan jam masuk + batas telat = MASUK
        // 2. Jika dari jam masuk + batas telat sampai jam keluar + 15 menit = KELUAR
        
        $batasTelat = $jadwalKelas->batas_telat 
            ? Carbon::parse($jadwalKelas->batas_telat)
            : $jamMasuk->copy()->addMinutes(15);
        
        $waktuMulaiMasuk = $jamMasuk->copy()->subMinutes(30);
        $waktuSelesaiMasuk = $batasTelat->copy();
        $waktuMulaiKeluar = $jamKeluar->copy()->subMinutes(15);
        $waktuSelesaiKeluar = $jamKeluar->copy()->addMinutes(15);
        
        Log::info('Debug waktu absensi:', [
            'waktu_mulai_masuk' => $waktuMulaiMasuk->format('H:i:s'),
            'waktu_selesai_masuk' => $waktuSelesaiMasuk->format('H:i:s'),
            'waktu_mulai_keluar' => $waktuMulaiKeluar->format('H:i:s'),
            'waktu_selesai_keluar' => $waktuSelesaiKeluar->format('H:i:s'),
        ]);
        
        $absenType = 'masuk'; // default
        
        // Cek apakah sudah ada absensi masuk hari ini
        $existingAbsensi = AbsensiPelajaran::where('jadwal_kelas_id', $jadwalKelasId)
            ->whereDate('tanggal', $now->toDateString())
            ->whereNotNull('jam_masuk')
            ->exists();
        
        Log::info('Existing absensi masuk:', ['exists' => $existingAbsensi]);
        
        // Jika sudah ada absensi masuk dan waktu sekarang untuk keluar, maka keluar
        if ($existingAbsensi && $now->between($waktuMulaiKeluar, $waktuSelesaiKeluar)) {
            $absenType = 'keluar';
        }
        // Jika waktu sekarang masih dalam periode masuk
        elseif ($now->between($waktuMulaiMasuk, $waktuSelesaiMasuk)) {
            $absenType = 'masuk';
        }
        // Jika waktu sekarang dalam periode keluar tapi belum ada absensi masuk
        elseif ($now->between($waktuMulaiKeluar, $waktuSelesaiKeluar)) {
            $absenType = 'keluar';
        }
        
        Log::info('Determined absen type:', ['type' => $absenType]);
        
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
        Log::info('AbsensiPelajaranController::scanQr called', [
            'request_data' => $request->all(),
            'timestamp' => now()->toDateTimeString()
        ]);

        try {
            $request->validate([
                'qr_code' => 'required|string',
                'jadwal_kelas_id' => 'required|exists:jadwal_kelas,id',
                'type' => 'required|in:masuk,keluar'
            ]);

            $qrCode = $request->qr_code;
            $jadwalKelasId = $request->jadwal_kelas_id;
            $type = $request->type;
            
            Log::info('Validation passed', [
                'qr_code' => $qrCode,
                'jadwal_kelas_id' => $jadwalKelasId,
                'type' => $type
            ]);
            
            // Set timezone untuk memastikan menggunakan WIB
            $today = Carbon::today('Asia/Jakarta');
            $now = Carbon::now('Asia/Jakarta');

            // Cari siswa berdasarkan QR code
            $siswa = Siswa::with('kelas.jurusan')
                ->where('qr_code', $qrCode)
                ->aktif()
                ->first();

            if (!$siswa) {
                Log::warning('Siswa not found with QR code', ['qr_code' => $qrCode]);
                return response()->json([
                    'success' => false,
                    'message' => 'QR Code tidak ditemukan atau siswa tidak aktif!'
                ], 404);
            }

            Log::info('Siswa found', [
                'siswa_id' => $siswa->id,
                'siswa_nama' => $siswa->nama,
                'siswa_nis' => $siswa->nis
            ]);

            // Validasi relasi siswa
            if (!$siswa->kelas) {
                Log::warning('Siswa kelas not found', [
                    'siswa_id' => $siswa->id,
                    'kelas_id' => $siswa->kelas_id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Data kelas siswa tidak ditemukan!'
                ], 400);
            }

            if (!$siswa->kelas->jurusan) {
                Log::warning('Siswa jurusan not found', [
                    'siswa_id' => $siswa->id,
                    'kelas_id' => $siswa->kelas_id,
                    'jurusan_id' => $siswa->kelas->jurusan_id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Data jurusan siswa tidak ditemukan!'
                ], 400);
            }

            // Ambil data jadwal kelas
            $jadwalKelas = JadwalKelas::with('kelas.jurusan')->find($jadwalKelasId);
            
            if (!$jadwalKelas) {
                Log::warning('JadwalKelas not found', ['jadwal_kelas_id' => $jadwalKelasId]);
                return response()->json([
                    'success' => false,
                    'message' => 'Jadwal kelas tidak ditemukan!'
                ], 404);
            }
            
            Log::info('JadwalKelas found', [
                'jadwal_id' => $jadwalKelas->id,
                'mata_pelajaran' => $jadwalKelas->mata_pelajaran,
                'jadwal_kelas_id' => $jadwalKelas->kelas_id,
                'siswa_kelas_id' => $siswa->kelas_id
            ]);
            
            // Validasi kelas siswa dengan jadwal
            if ($siswa->kelas_id !== $jadwalKelas->kelas_id) {
                Log::warning('Class mismatch', [
                    'siswa_kelas_id' => $siswa->kelas_id,
                    'jadwal_kelas_id' => $jadwalKelas->kelas_id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Siswa tidak terdaftar di kelas untuk jadwal pelajaran ini!'
                ], 400);
            }
            
            // Validasi apakah jadwal ini valid untuk waktu sekarang
            $validationResult = $this->validatePelajaranTime($jadwalKelas, $now, $type);
            if (!$validationResult['valid']) {
                Log::warning('Time validation failed', [
                    'type' => $type,
                    'current_time' => $now->format('H:i:s'),
                    'validation_message' => $validationResult['message']
                ]);
                return response()->json([
                    'success' => false,
                    'message' => $validationResult['message']
                ], 400);
            }
            
            Log::info('Processing absensi', [
                'type' => $type,
                'siswa_nis' => $siswa->nis,
                'jadwal_id' => $jadwalKelas->id
            ]);
            
            if ($type === 'masuk') {
                return $this->prosesAbsenMasuk($siswa, $jadwalKelas, $today, $now);
            } else {
                return $this->prosesAbsenKeluar($siswa, $jadwalKelas, $today, $now);
            }
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error in scanQr:', [
                'errors' => $e->validator->errors()->all(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in scanQr Pelajaran: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
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
            // Waktu mulai: 30 menit sebelum jam masuk (sesuai setting sebelumnya)
            $waktuMulai = Carbon::createFromFormat('H:i:s', $jadwalKelas->jam_masuk)->subMinutes(30)->format('H:i:s');
            
            // Jika admin mengosongkan batas_telat, berarti absen bisa dilakukan kapan saja
            if (empty($jadwalKelas->batas_telat)) {
                // Hanya validasi waktu mulai saja
                if ($jamSekarang < $waktuMulai) {
                    $waktuMulaiDisplay = Carbon::createFromFormat('H:i:s', $waktuMulai)->format('H:i');
                    return [
                        'valid' => false,
                        'message' => "Absen masuk untuk pelajaran {$jadwalKelas->mata_pelajaran} belum dimulai. Waktu mulai: {$waktuMulaiDisplay}"
                    ];
                }
                // Tidak ada batasan waktu selesai jika batas_telat kosong
            } else {
                // Ada batas telat, gunakan sebagai waktu selesai
                $waktuSelesai = Carbon::createFromFormat('H:i:s', $jadwalKelas->batas_telat)->format('H:i:s');
                
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
        
        Log::info('Saving absensi data', [
            'absensi_data' => $absensiData,
            'existing_absensi' => $existingAbsensi ? $existingAbsensi->id : null
        ]);
        
        if ($existingAbsensi) {
            $existingAbsensi->update($absensiData);
            $absensi = $existingAbsensi;
            Log::info('Updated existing absensi', ['id' => $absensi->id]);
        } else {
            $absensi = AbsensiPelajaran::create($absensiData);
            Log::info('Created new absensi', ['id' => $absensi->id]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Absen masuk berhasil!',
            'jenis_absensi' => 'Masuk',
            'data' => [
                'nis' => $siswa->nis,
                'nama' => $siswa->nama,
                'kelas' => ($siswa->kelas ? "{$siswa->kelas->tingkat} {$siswa->kelas->nama_kelas}" : 'Kelas tidak diketahui'),
                'jurusan' => ($siswa->kelas && $siswa->kelas->jurusan ? $siswa->kelas->jurusan->nama_jurusan : 'Jurusan tidak diketahui'),
                'mata_pelajaran' => $jadwalKelas->mata_pelajaran,
                'guru_pengampu' => $jadwalKelas->guru_pengampu,
                'jam_masuk' => $now->format('H:i:s'),
                'jam_keluar' => null,
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
            ->first();

        // Jika belum ada absensi sama sekali, buat baru dengan status langsung keluar
        if (!$absensi) {
            Log::info('Creating new absensi keluar without masuk', [
                'nis' => $siswa->nis,
                'jadwal_kelas_id' => $jadwalKelas->id,
                'tanggal' => $today,
                'jam_keluar' => $now->format('H:i:s')
            ]);
            
            $absensi = AbsensiPelajaran::create([
                'nis' => $siswa->nis,
                'jadwal_kelas_id' => $jadwalKelas->id,
                'tanggal' => $today,
                'jam_masuk' => null, // Tidak ada jam masuk
                'jam_keluar' => $now->format('H:i:s'),
                'status_masuk' => 'tidak_hadir', // Tidak hadir karena tidak absen masuk
                'status_keluar' => 'keluar_tanpa_masuk'
            ]);
            
            Log::info('Created new absensi keluar without masuk', ['absensi_id' => $absensi->id]);
            
            return response()->json([
                'success' => true,
                'message' => 'Absen keluar berhasil! (Tanpa absen masuk)',
                'jenis_absensi' => 'Keluar',
                'data' => [
                    'nis' => $siswa->nis,
                    'nama' => $siswa->nama,
                    'kelas' => ($siswa->kelas ? "{$siswa->kelas->tingkat} {$siswa->kelas->nama_kelas}" : 'Kelas tidak diketahui'),
                    'jurusan' => ($siswa->kelas && $siswa->kelas->jurusan ? $siswa->kelas->jurusan->nama_jurusan : 'Jurusan tidak diketahui'),
                    'mata_pelajaran' => $jadwalKelas->mata_pelajaran,
                    'guru_pengampu' => $jadwalKelas->guru_pengampu,
                    'jam_masuk' => 'Tidak absen masuk',
                    'jam_keluar' => $now->format('H:i:s'),
                    'status' => 'keluar_tanpa_masuk'
                ]
            ]);
        }

        // Jika sudah ada absensi tapi belum absen masuk
        if (!$absensi->jam_masuk) {
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
        Log::info('Updating absensi keluar', [
            'absensi_id' => $absensi->id,
            'jam_keluar' => $now->format('H:i:s')
        ]);
        
        $absensi->update([
            'jam_keluar' => $now->format('H:i:s'),
            'status_keluar' => 'sudah_keluar'
        ]);
        
        Log::info('Absensi keluar updated successfully', ['absensi_id' => $absensi->id]);

        return response()->json([
            'success' => true,
            'message' => 'Absen keluar berhasil!',
            'jenis_absensi' => 'Keluar',
            'data' => [
                'nis' => $siswa->nis,
                'nama' => $siswa->nama,
                'kelas' => ($siswa->kelas ? "{$siswa->kelas->tingkat} {$siswa->kelas->nama_kelas}" : 'Kelas tidak diketahui'),
                'jurusan' => ($siswa->kelas && $siswa->kelas->jurusan ? $siswa->kelas->jurusan->nama_jurusan : 'Jurusan tidak diketahui'),
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
