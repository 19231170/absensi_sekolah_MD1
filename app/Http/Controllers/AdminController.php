<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\JadwalKelas;
use App\Models\Absensi;
use App\Models\Siswa;
use App\Models\User;
use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\JamSekolah;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Generate admin QR code for authentication
     */
    public function generateAdminQr()
    {
        // Generate unique admin token (valid for 1 hour)
        $adminToken = 'ADMIN_' . Hash::make(now()->format('Y-m-d-H') . config('app.key'));
        $adminToken = substr($adminToken, 0, 32); // Limit length
        
        // Store in session for verification
        session(['admin_token' => $adminToken, 'admin_token_expires' => now()->addHour()]);
        
        return view('admin.qr-auth', compact('adminToken'));
    }

    /**
     * Show admin dashboard after QR scan
     */
    public function dashboard(Request $request)
    {
        $token = $request->get('token');
        
        // Verify admin token
        if (!$this->verifyAdminToken($token)) {
            return redirect()->route('absensi.index')
                ->with('error', 'Token admin tidak valid atau sudah expired!');
        }
        
        // Get statistics
        $stats = $this->getSystemStats();
        
        return view('admin.dashboard', compact('stats'));
    }

    /**
     * Delete all dummy data
     */
    public function deleteDummyData(Request $request)
    {
        $token = $request->get('token');
        
        // Verify admin token
        if (!$this->verifyAdminToken($token)) {
            return response()->json([
                'success' => false,
                'message' => 'Token admin tidak valid atau sudah expired!'
            ], 403);
        }

        try {
            DB::beginTransaction();
            
            // Disable foreign key checks to allow truncation
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            
            $deletedCounts = [];
            
            // Delete ALL data from all tables (DANGEROUS!)
            
            // 1. Delete all absensi records (child table first)
            $deletedAbsensi = Absensi::count();
            DB::table('absensi')->truncate();
            $deletedCounts['absensi'] = $deletedAbsensi;
            
            // 2. Delete all jadwal kelas
            $deletedJadwal = JadwalKelas::count();
            DB::table('jadwal_kelas')->truncate();
            $deletedCounts['jadwal_kelas'] = $deletedJadwal;
            
            // 3. Delete all siswa records
            $deletedSiswa = Siswa::count();
            DB::table('siswa')->truncate();
            $deletedCounts['siswa'] = $deletedSiswa;
            
            // 4. Delete all kelas records
            $deletedKelas = Kelas::count();
            DB::table('kelas')->truncate();
            $deletedCounts['kelas'] = $deletedKelas;
            
            // 5. Delete all jurusan records
            $deletedJurusan = Jurusan::count();
            DB::table('jurusan')->truncate();
            $deletedCounts['jurusan'] = $deletedJurusan;
            
            // 6. Delete all jam sekolah records
            $deletedJamSekolah = JamSekolah::count();
            DB::table('jam_sekolah')->truncate();
            $deletedCounts['jam_sekolah'] = $deletedJamSekolah;
            
            // 7. Optional: Delete non-admin users (keep admin users if any)
            $deletedUsers = User::where('role', '!=', 'admin')->count();
            User::where('role', '!=', 'admin')->delete();
            $deletedCounts['users'] = $deletedUsers;

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            DB::commit();
            
            // Clear admin token after use
            session()->forget(['admin_token', 'admin_token_expires']);
            
            return response()->json([
                'success' => true,
                'message' => 'SEMUA DATA berhasil dihapus! Database telah direset.',
                'deleted_counts' => $deletedCounts,
                'total_deleted' => array_sum($deletedCounts),
                'warning' => 'SEMUA DATA TELAH DIHAPUS PERMANEN!',
                'redirect_url' => route('absensi.index')
            ]);
            
        } catch (\Exception $e) {
            // Re-enable foreign key checks on error
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify admin token
     */
    private function verifyAdminToken($token)
    {
        $sessionToken = session('admin_token');
        $tokenExpires = session('admin_token_expires');
        
        if (!$sessionToken || !$tokenExpires) {
            return false;
        }
        
        if (now()->gt($tokenExpires)) {
            session()->forget(['admin_token', 'admin_token_expires']);
            return false;
        }
        
        return $token === $sessionToken;
    }

    /**
     * Get system statistics
     */
    private function getSystemStats()
    {
        return [
            'total_absensi' => Absensi::count(),
            'absensi_today' => Absensi::whereDate('created_at', today())->count(),
            'absensi_old' => Absensi::where('created_at', '<', now()->subDay())->count(),
            'total_jadwal' => JadwalKelas::count(),
            'jadwal_aktif' => JadwalKelas::where('is_active', true)->count(),
            'jadwal_nonaktif' => JadwalKelas::where('is_active', false)->count(),
            'total_mahasiswa' => Siswa::count(),
            'total_kelas' => Kelas::count(),
            'total_jurusan' => Jurusan::count(),
            'total_jam_sekolah' => JamSekolah::count(),
            'total_users' => User::count(),
            'dummy_data_count' => Absensi::count() + JadwalKelas::count() + Siswa::count() + 
                                Kelas::count() + Jurusan::count() + JamSekolah::count(),
            'test_absensi' => Absensi::where('keterangan', 'like', '%test%')
                ->orWhere('keterangan', 'like', '%dummy%')
                ->count()
        ];
    }

    /**
     * Generate QR image for download
     */
    public function downloadQr()
    {
        $adminToken = session('admin_token');
        
        if (!$adminToken) {
            return redirect()->route('admin.qr-auth')
                ->with('error', 'Silakan generate QR code terlebih dahulu!');
        }
        
        $adminUrl = route('admin.dashboard', ['token' => $adminToken]);
        
        // Use external QR service for now
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($adminUrl);
        
        return redirect($qrUrl);
    }
}
