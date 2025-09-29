<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AbsensiPelajaran;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\JadwalKelas;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StatisticsController extends Controller
{
    /**
     * Display main statistics dashboard
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Filter berdasarkan role
        $kelasQuery = Kelas::query();
        if ($user->role !== 'admin') {
            // Jika guru, hanya tampilkan kelas yang mereka ampu
            $kelasIds = JadwalKelas::where('guru_pengampu', $user->name)
                ->distinct()
                ->pluck('kelas_id')
                ->toArray();
            $kelasQuery->whereIn('id', $kelasIds);
        }
        
        $kelasList = $kelasQuery->with('jurusan')->get();
        
        // Get selected filters
        $selectedKelas = $request->get('kelas_id');
        $selectedPeriod = $request->get('period', 'month'); // month, semester, year
        $selectedMonth = $request->get('month', date('Y-m'));
        $selectedSemester = $request->get('semester', date('n') <= 6 ? 1 : 2);
        $selectedYear = $request->get('year', date('Y'));
        
        // Calculate date range based on period
        $dateRange = $this->getDateRange($selectedPeriod, $selectedMonth, $selectedSemester, $selectedYear);
        
        // Get overall statistics
        $overallStats = $this->getOverallStatistics($selectedKelas, $dateRange, $user);
        
        // Get top performing students
        $topStudents = $this->getTopPerformingStudents($selectedKelas, $dateRange, $user);
        
        // Get students with attendance issues
        $problemStudents = $this->getStudentsWithIssues($selectedKelas, $dateRange, $user);
        
        // Get daily attendance trend
        $attendanceTrend = $this->getAttendanceTrend($selectedKelas, $dateRange, $user);
        
        // Get subject-wise statistics
        $subjectStats = $this->getSubjectStatistics($selectedKelas, $dateRange, $user);
        
        return view('statistics.index', compact(
            'kelasList',
            'selectedKelas',
            'selectedPeriod',
            'selectedMonth', 
            'selectedSemester',
            'selectedYear',
            'overallStats',
            'topStudents',
            'problemStudents',
            'attendanceTrend',
            'subjectStats',
            'dateRange'
        ));
    }
    
    /**
     * Display detailed statistics for a specific student
     */
    public function student(Request $request, $nis)
    {
        $user = Auth::user();
        $siswa = Siswa::with('kelas.jurusan')->findOrFail($nis);
        
        // Check access permissions
        if ($user->role !== 'admin') {
            $hasAccess = JadwalKelas::where('kelas_id', $siswa->kelas_id)
                ->where('guru_pengampu', $user->name)
                ->exists();
            
            if (!$hasAccess) {
                abort(403, 'Anda tidak memiliki akses untuk melihat data siswa ini.');
            }
        }
        
        // Get selected period
        $selectedPeriod = $request->get('period', 'month');
        $selectedMonth = $request->get('month', date('Y-m'));
        $selectedSemester = $request->get('semester', date('n') <= 6 ? 1 : 2);
        $selectedYear = $request->get('year', date('Y'));
        
        $dateRange = $this->getDateRange($selectedPeriod, $selectedMonth, $selectedSemester, $selectedYear);
        
        // Get student detailed statistics
        $studentStats = $this->getStudentDetailedStats($nis, $dateRange);
        
        // Get student attendance history
        $attendanceHistory = $this->getStudentAttendanceHistory($nis, $dateRange);
        
        // Get subject-wise performance for this student
        $subjectPerformance = $this->getStudentSubjectPerformance($nis, $dateRange);
        
        // Get attendance pattern (by day of week)
        $weeklyPattern = $this->getStudentWeeklyPattern($nis, $dateRange);
        
        return view('statistics.student', compact(
            'siswa',
            'selectedPeriod',
            'selectedMonth',
            'selectedSemester', 
            'selectedYear',
            'studentStats',
            'attendanceHistory',
            'subjectPerformance',
            'weeklyPattern',
            'dateRange'
        ));
    }
    
    /**
     * Display class-wise statistics comparison
     */
    public function classComparison(Request $request)
    {
        $user = Auth::user();
        
        // Filter berdasarkan role
        $kelasQuery = Kelas::query();
        if ($user->role !== 'admin') {
            $kelasIds = JadwalKelas::where('guru_pengampu', $user->name)
                ->distinct()
                ->pluck('kelas_id')
                ->toArray();
            $kelasQuery->whereIn('id', $kelasIds);
        }
        
        $kelasList = $kelasQuery->with('jurusan')->get();
        
        // Get selected period
        $selectedPeriod = $request->get('period', 'month');
        $selectedMonth = $request->get('month', date('Y-m'));
        $selectedSemester = $request->get('semester', date('n') <= 6 ? 1 : 2);
        $selectedYear = $request->get('year', date('Y'));
        
        $dateRange = $this->getDateRange($selectedPeriod, $selectedMonth, $selectedSemester, $selectedYear);
        
        // Get class comparison data
        $classComparison = [];
        foreach ($kelasList as $kelas) {
            $classStats = $this->getOverallStatistics($kelas->id, $dateRange, $user);
            $classComparison[] = [
                'kelas' => $kelas,
                'stats' => $classStats
            ];
        }
        
        return view('statistics.class-comparison', compact(
            'kelasList',
            'selectedPeriod',
            'selectedMonth',
            'selectedSemester',
            'selectedYear',
            'classComparison',
            'dateRange'
        ));
    }
    
    /**
     * Get date range based on selected period
     */
    private function getDateRange($period, $month = null, $semester = null, $year = null)
    {
        $startDate = null;
        $endDate = null;
        
        switch ($period) {
            case 'month':
                $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
                $endDate = Carbon::createFromFormat('Y-m', $month)->endOfMonth();
                break;
                
            case 'semester':
                if ($semester == 1) {
                    $startDate = Carbon::create($year, 7, 1)->startOfDay(); // Juli
                    $endDate = Carbon::create($year, 12, 31)->endOfDay();   // Desember
                } else {
                    $startDate = Carbon::create($year, 1, 1)->startOfDay(); // Januari
                    $endDate = Carbon::create($year, 6, 30)->endOfDay();    // Juni
                }
                break;
                
            case 'year':
                $startDate = Carbon::create($year, 1, 1)->startOfDay();
                $endDate = Carbon::create($year, 12, 31)->endOfDay();
                break;
        }
        
        return ['start' => $startDate, 'end' => $endDate];
    }
    
    /**
     * Get overall attendance statistics
     */
    private function getOverallStatistics($kelasId = null, $dateRange = null, $user = null)
    {
        $query = AbsensiPelajaran::query()
            ->whereBetween('tanggal', [$dateRange['start'], $dateRange['end']]);
        
        if ($kelasId) {
            $query->whereHas('jadwalKelas', function($q) use ($kelasId) {
                $q->where('kelas_id', $kelasId);
            });
        }
        
        // Filter by user role if not admin
        if ($user && $user->role !== 'admin') {
            $query->whereHas('jadwalKelas', function($q) use ($user) {
                $q->where('guru_pengampu', $user->name);
            });
        }
        
        $totalRecords = $query->count();
        $hadirCount = $query->clone()->where('status_masuk', 'hadir')->count();
        $telatCount = $query->clone()->where('status_masuk', 'telat')->count();
        $alphaCount = $query->clone()->whereNull('jam_masuk')->count();
        
        // Calculate percentages
        $hadirPercentage = $totalRecords > 0 ? round(($hadirCount / $totalRecords) * 100, 1) : 0;
        $telatPercentage = $totalRecords > 0 ? round(($telatCount / $totalRecords) * 100, 1) : 0;
        $alphaPercentage = $totalRecords > 0 ? round(($alphaCount / $totalRecords) * 100, 1) : 0;
        
        return [
            'total_records' => $totalRecords,
            'hadir_count' => $hadirCount,
            'telat_count' => $telatCount,
            'alpha_count' => $alphaCount,
            'hadir_percentage' => $hadirPercentage,
            'telat_percentage' => $telatPercentage,
            'alpha_percentage' => $alphaPercentage
        ];
    }
    
    /**
     * Get top performing students (highest attendance rate)
     */
    private function getTopPerformingStudents($kelasId = null, $dateRange = null, $user = null, $limit = 10)
    {
        $query = AbsensiPelajaran::query()
            ->select('nis', 
                DB::raw('COUNT(*) as total_sessions'),
                DB::raw('SUM(CASE WHEN status_masuk = "hadir" THEN 1 ELSE 0 END) as hadir_count'),
                DB::raw('SUM(CASE WHEN status_masuk = "telat" THEN 1 ELSE 0 END) as telat_count'),
                DB::raw('SUM(CASE WHEN jam_masuk IS NULL THEN 1 ELSE 0 END) as alpha_count'),
                DB::raw('ROUND((SUM(CASE WHEN status_masuk IN ("hadir", "telat") THEN 1 ELSE 0 END) / COUNT(*)) * 100, 1) as attendance_percentage')
            )
            ->with('siswa.kelas')
            ->whereBetween('tanggal', [$dateRange['start'], $dateRange['end']])
            ->groupBy('nis')
            ->having('total_sessions', '>=', 5) // At least 5 sessions to be considered
            ->orderBy('attendance_percentage', 'desc')
            ->limit($limit);
        
        if ($kelasId) {
            $query->whereHas('jadwalKelas', function($q) use ($kelasId) {
                $q->where('kelas_id', $kelasId);
            });
        }
        
        if ($user && $user->role !== 'admin') {
            $query->whereHas('jadwalKelas', function($q) use ($user) {
                $q->where('guru_pengampu', $user->name);
            });
        }
        
        return $query->get();
    }
    
    /**
     * Get students with attendance issues
     */
    private function getStudentsWithIssues($kelasId = null, $dateRange = null, $user = null, $limit = 10)
    {
        $query = AbsensiPelajaran::query()
            ->select('nis',
                DB::raw('COUNT(*) as total_sessions'),
                DB::raw('SUM(CASE WHEN status_masuk = "hadir" THEN 1 ELSE 0 END) as hadir_count'),
                DB::raw('SUM(CASE WHEN status_masuk = "telat" THEN 1 ELSE 0 END) as telat_count'),
                DB::raw('SUM(CASE WHEN jam_masuk IS NULL THEN 1 ELSE 0 END) as alpha_count'),
                DB::raw('ROUND((SUM(CASE WHEN jam_masuk IS NULL THEN 1 ELSE 0 END) / COUNT(*)) * 100, 1) as alpha_percentage'),
                DB::raw('ROUND((SUM(CASE WHEN status_masuk = "telat" THEN 1 ELSE 0 END) / COUNT(*)) * 100, 1) as telat_percentage')
            )
            ->with('siswa.kelas')
            ->whereBetween('tanggal', [$dateRange['start'], $dateRange['end']])
            ->groupBy('nis')
            ->having('total_sessions', '>=', 3)
            ->having(DB::raw('(alpha_count + telat_count)'), '>', DB::raw('total_sessions * 0.3')) // More than 30% problems
            ->orderBy('alpha_percentage', 'desc')
            ->limit($limit);
        
        if ($kelasId) {
            $query->whereHas('jadwalKelas', function($q) use ($kelasId) {
                $q->where('kelas_id', $kelasId);
            });
        }
        
        if ($user && $user->role !== 'admin') {
            $query->whereHas('jadwalKelas', function($q) use ($user) {
                $q->where('guru_pengampu', $user->name);
            });
        }
        
        return $query->get();
    }
    
    /**
     * Get daily attendance trend
     */
    private function getAttendanceTrend($kelasId = null, $dateRange = null, $user = null)
    {
        $query = AbsensiPelajaran::query()
            ->select(
                DB::raw('DATE(tanggal) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status_masuk = "hadir" THEN 1 ELSE 0 END) as hadir'),
                DB::raw('SUM(CASE WHEN status_masuk = "telat" THEN 1 ELSE 0 END) as telat'),
                DB::raw('SUM(CASE WHEN jam_masuk IS NULL THEN 1 ELSE 0 END) as alpha')
            )
            ->whereBetween('tanggal', [$dateRange['start'], $dateRange['end']])
            ->groupBy(DB::raw('DATE(tanggal)'))
            ->orderBy('date');
        
        if ($kelasId) {
            $query->whereHas('jadwalKelas', function($q) use ($kelasId) {
                $q->where('kelas_id', $kelasId);
            });
        }
        
        if ($user && $user->role !== 'admin') {
            $query->whereHas('jadwalKelas', function($q) use ($user) {
                $q->where('guru_pengampu', $user->name);
            });
        }
        
        return $query->get();
    }
    
    /**
     * Get subject-wise statistics
     */
    private function getSubjectStatistics($kelasId = null, $dateRange = null, $user = null)
    {
        $query = AbsensiPelajaran::query()
            ->select(
                'jadwal_kelas.mata_pelajaran',
                DB::raw('COUNT(*) as total_sessions'),
                DB::raw('SUM(CASE WHEN absensi_pelajaran.status_masuk = "hadir" THEN 1 ELSE 0 END) as hadir_count'),
                DB::raw('SUM(CASE WHEN absensi_pelajaran.status_masuk = "telat" THEN 1 ELSE 0 END) as telat_count'),
                DB::raw('SUM(CASE WHEN absensi_pelajaran.jam_masuk IS NULL THEN 1 ELSE 0 END) as alpha_count'),
                DB::raw('ROUND((SUM(CASE WHEN absensi_pelajaran.status_masuk = "hadir" THEN 1 ELSE 0 END) / COUNT(*)) * 100, 1) as hadir_percentage')
            )
            ->join('jadwal_kelas', 'absensi_pelajaran.jadwal_kelas_id', '=', 'jadwal_kelas.id')
            ->whereBetween('absensi_pelajaran.tanggal', [$dateRange['start'], $dateRange['end']])
            ->groupBy('jadwal_kelas.mata_pelajaran')
            ->orderBy('hadir_percentage', 'desc');
        
        if ($kelasId) {
            $query->where('jadwal_kelas.kelas_id', $kelasId);
        }
        
        if ($user && $user->role !== 'admin') {
            $query->where('jadwal_kelas.guru_pengampu', $user->name);
        }
        
        return $query->get();
    }
    
    /**
     * Get detailed statistics for a specific student
     */
    private function getStudentDetailedStats($nis, $dateRange)
    {
        $stats = AbsensiPelajaran::where('nis', $nis)
            ->whereBetween('tanggal', [$dateRange['start'], $dateRange['end']])
            ->selectRaw('
                COUNT(*) as total_sessions,
                SUM(CASE WHEN status_masuk = "hadir" THEN 1 ELSE 0 END) as hadir_count,
                SUM(CASE WHEN status_masuk = "telat" THEN 1 ELSE 0 END) as telat_count,
                SUM(CASE WHEN jam_masuk IS NULL THEN 1 ELSE 0 END) as alpha_count,
                AVG(CASE WHEN jam_masuk IS NOT NULL AND jam_keluar IS NOT NULL 
                    THEN TIMESTAMPDIFF(MINUTE, jam_masuk, jam_keluar) ELSE NULL END) as avg_duration_minutes
            ')
            ->first();
        
        if ($stats->total_sessions > 0) {
            $stats->hadir_percentage = round(($stats->hadir_count / $stats->total_sessions) * 100, 1);
            $stats->telat_percentage = round(($stats->telat_count / $stats->total_sessions) * 100, 1);
            $stats->alpha_percentage = round(($stats->alpha_count / $stats->total_sessions) * 100, 1);
        } else {
            $stats->hadir_percentage = 0;
            $stats->telat_percentage = 0;
            $stats->alpha_percentage = 0;
        }
        
        return $stats;
    }
    
    /**
     * Get student attendance history
     */
    private function getStudentAttendanceHistory($nis, $dateRange)
    {
        return AbsensiPelajaran::with('jadwalKelas')
            ->where('nis', $nis)
            ->whereBetween('tanggal', [$dateRange['start'], $dateRange['end']])
            ->orderBy('tanggal', 'desc')
            ->orderBy('jam_masuk', 'desc')
            ->paginate(20);
    }
    
    /**
     * Get student performance by subject
     */
    private function getStudentSubjectPerformance($nis, $dateRange)
    {
        return AbsensiPelajaran::query()
            ->select(
                'jadwal_kelas.mata_pelajaran',
                DB::raw('COUNT(*) as total_sessions'),
                DB::raw('SUM(CASE WHEN absensi_pelajaran.status_masuk = "hadir" THEN 1 ELSE 0 END) as hadir_count'),
                DB::raw('SUM(CASE WHEN absensi_pelajaran.status_masuk = "telat" THEN 1 ELSE 0 END) as telat_count'),
                DB::raw('SUM(CASE WHEN absensi_pelajaran.jam_masuk IS NULL THEN 1 ELSE 0 END) as alpha_count'),
                DB::raw('ROUND((SUM(CASE WHEN absensi_pelajaran.status_masuk = "hadir" THEN 1 ELSE 0 END) / COUNT(*)) * 100, 1) as hadir_percentage')
            )
            ->join('jadwal_kelas', 'absensi_pelajaran.jadwal_kelas_id', '=', 'jadwal_kelas.id')
            ->where('absensi_pelajaran.nis', $nis)
            ->whereBetween('absensi_pelajaran.tanggal', [$dateRange['start'], $dateRange['end']])
            ->groupBy('jadwal_kelas.mata_pelajaran')
            ->orderBy('hadir_percentage', 'desc')
            ->get();
    }
    
    /**
     * Get student weekly attendance pattern
     */
    private function getStudentWeeklyPattern($nis, $dateRange)
    {
        return AbsensiPelajaran::query()
            ->select(
                DB::raw('DAYOFWEEK(tanggal) as day_of_week'),
                DB::raw('CASE 
                    WHEN DAYOFWEEK(tanggal) = 1 THEN "Minggu"
                    WHEN DAYOFWEEK(tanggal) = 2 THEN "Senin"
                    WHEN DAYOFWEEK(tanggal) = 3 THEN "Selasa"
                    WHEN DAYOFWEEK(tanggal) = 4 THEN "Rabu"
                    WHEN DAYOFWEEK(tanggal) = 5 THEN "Kamis"
                    WHEN DAYOFWEEK(tanggal) = 6 THEN "Jumat"
                    WHEN DAYOFWEEK(tanggal) = 7 THEN "Sabtu"
                END as day_name'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status_masuk = "hadir" THEN 1 ELSE 0 END) as hadir'),
                DB::raw('SUM(CASE WHEN status_masuk = "telat" THEN 1 ELSE 0 END) as telat'),
                DB::raw('SUM(CASE WHEN jam_masuk IS NULL THEN 1 ELSE 0 END) as alpha'),
                DB::raw('ROUND((SUM(CASE WHEN status_masuk = "hadir" THEN 1 ELSE 0 END) / COUNT(*)) * 100, 1) as hadir_percentage')
            )
            ->where('nis', $nis)
            ->whereBetween('tanggal', [$dateRange['start'], $dateRange['end']])
            ->groupBy(DB::raw('DAYOFWEEK(tanggal)'), DB::raw('CASE 
                WHEN DAYOFWEEK(tanggal) = 1 THEN "Minggu"
                WHEN DAYOFWEEK(tanggal) = 2 THEN "Senin"
                WHEN DAYOFWEEK(tanggal) = 3 THEN "Selasa"
                WHEN DAYOFWEEK(tanggal) = 4 THEN "Rabu"
                WHEN DAYOFWEEK(tanggal) = 5 THEN "Kamis"
                WHEN DAYOFWEEK(tanggal) = 6 THEN "Jumat"
                WHEN DAYOFWEEK(tanggal) = 7 THEN "Sabtu"
            END'))
            ->orderBy('day_of_week')
            ->get();
    }
}
