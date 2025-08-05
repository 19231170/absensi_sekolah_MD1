<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JamSekolah;
use Carbon\Carbon;

class JadwalController extends Controller
{
    /**
     * Tampilkan jadwal lengkap
     */
    public function index()
    {
        $jamSekolah = JamSekolah::aktif()
            ->orderBy('jenis_sesi')
            ->orderBy('jam_masuk')
            ->get();
            
        // Group by jenis sesi
        $jadwalPagi = $jamSekolah->where('jenis_sesi', 'pagi');
        $jadwalSiang = $jamSekolah->where('jenis_sesi', 'siang');
        
        // Get hari ini
        $hariIni = $this->getHariIndonesia();
        
        return view('jadwal.index', compact('jadwalPagi', 'jadwalSiang', 'hariIni'));
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
