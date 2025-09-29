@extends('layouts.app')

@section('title', 'Detail Statistik Siswa')

@push('styles')
<style>
    .student-header {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        border-radius: 15px;
        color: white;
        padding: 2rem;
        margin-bottom: 2rem;
    }
    .stats-card {
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s;
    }
    .stats-card:hover {
        transform: translateY(-3px);
    }
    .progress-ring {
        width: 100px;
        height: 100px;
        margin: 0 auto;
    }
    .progress-ring circle {
        transform-origin: 50% 50%;
        transform: rotate(-90deg);
        transition: stroke-dasharray 0.6s ease;
    }
    .attendance-item {
        border-left: 4px solid;
        transition: all 0.3s ease;
        margin-bottom: 0.5rem;
    }
    .attendance-item:hover {
        transform: translateX(5px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    .status-hadir { border-left-color: #28a745; }
    .status-telat { border-left-color: #ffc107; }
    .status-alpha { border-left-color: #dc3545; }
    .chart-container {
        height: 300px;
        position: relative;
    }
    .weekly-pattern {
        height: 200px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Student Header -->
    <div class="student-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center mb-3">
                    <a href="{{ route('statistics.index') }}" class="btn btn-light me-3">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <div>
                        <h1 class="mb-1">{{ $siswa->nama }}</h1>
                        <p class="mb-0 opacity-75">
                            NIS: {{ $siswa->nis }} | 
                            {{ $siswa->kelas->tingkat }} {{ $siswa->kelas->nama_kelas }}
                            @if($siswa->kelas->jurusan) - {{ $siswa->kelas->jurusan->nama_jurusan }} @endif
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-end">
                <div class="d-flex gap-2 justify-content-end">
                    <button class="btn btn-light" onclick="window.print()">
                        <i class="fas fa-print"></i> Cetak
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-download"></i> Export
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-file-pdf"></i> PDF</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-file-excel"></i> Excel</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('statistics.student', $siswa->nis) }}">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="form-label"><strong>Periode</strong></label>
                                <select name="period" class="form-select" onchange="togglePeriodInputs(); this.form.submit()">
                                    <option value="month" {{ $selectedPeriod == 'month' ? 'selected' : '' }}>Bulanan</option>
                                    <option value="semester" {{ $selectedPeriod == 'semester' ? 'selected' : '' }}>Semester</option>
                                    <option value="year" {{ $selectedPeriod == 'year' ? 'selected' : '' }}>Tahunan</option>
                                </select>
                            </div>
                            <div class="col-md-3" id="monthInput" style="{{ $selectedPeriod != 'month' ? 'display:none' : '' }}">
                                <label class="form-label"><strong>Bulan</strong></label>
                                <input type="month" name="month" class="form-control" value="{{ $selectedMonth }}" onchange="this.form.submit()">
                            </div>
                            <div class="col-md-3" id="semesterInput" style="{{ $selectedPeriod != 'semester' ? 'display:none' : '' }}">
                                <label class="form-label"><strong>Semester & Tahun</strong></label>
                                <div class="row">
                                    <div class="col-6">
                                        <select name="semester" class="form-select" onchange="this.form.submit()">
                                            <option value="1" {{ $selectedSemester == 1 ? 'selected' : '' }}>Ganjil</option>
                                            <option value="2" {{ $selectedSemester == 2 ? 'selected' : '' }}>Genap</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <select name="year" class="form-select" onchange="this.form.submit()">
                                            @for($year = date('Y'); $year >= date('Y') - 3; $year--)
                                                <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3" id="yearInput" style="{{ $selectedPeriod != 'year' ? 'display:none' : '' }}">
                                <label class="form-label"><strong>Tahun</strong></label>
                                <select name="year" class="form-select" onchange="this.form.submit()">
                                    @for($year = date('Y'); $year >= date('Y') - 3; $year--)
                                        <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Overview -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card stats-card text-center h-100">
                <div class="card-body">
                    <h5 class="card-title text-primary">Total Sesi</h5>
                    <div class="display-4 text-primary mb-2">{{ $studentStats->total_sessions }}</div>
                    <p class="text-muted">Pembelajaran yang diikuti</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card stats-card text-center h-100">
                <div class="card-body">
                    <h5 class="card-title text-success">Kehadiran</h5>
                    <div class="progress-ring">
                        <svg width="100" height="100">
                            <circle cx="50" cy="50" r="40" stroke="#e9ecef" stroke-width="8" fill="transparent"></circle>
                            <circle cx="50" cy="50" r="40" stroke="#28a745" stroke-width="8" fill="transparent"
                                    stroke-dasharray="{{ 2 * pi() * 40 }}" 
                                    stroke-dashoffset="{{ 2 * pi() * 40 * (1 - $studentStats->hadir_percentage/100) }}">
                            </circle>
                        </svg>
                        <div class="position-absolute" style="top: 50%; left: 50%; transform: translate(-50%, -50%);">
                            <strong class="text-success">{{ $studentStats->hadir_percentage }}%</strong>
                        </div>
                    </div>
                    <p class="text-muted mt-2">{{ $studentStats->hadir_count }} dari {{ $studentStats->total_sessions }} sesi</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card stats-card text-center h-100">
                <div class="card-body">
                    <h5 class="card-title text-warning">Keterlambatan</h5>
                    <div class="display-4 text-warning mb-2">{{ $studentStats->telat_percentage }}%</div>
                    <p class="text-muted">{{ $studentStats->telat_count }} kali terlambat</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card stats-card text-center h-100">
                <div class="card-body">
                    <h5 class="card-title text-danger">Absen</h5>
                    <div class="display-4 text-danger mb-2">{{ $studentStats->alpha_percentage }}%</div>
                    <p class="text-muted">{{ $studentStats->alpha_count }} kali tidak hadir</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Weekly Pattern -->
        <div class="col-lg-6 mb-4">
            <div class="card stats-card">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-calendar-week me-2"></i>Pola Kehadiran Mingguan
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-container weekly-pattern">
                        <canvas id="weeklyPatternChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subject Performance -->
        <div class="col-lg-6 mb-4">
            <div class="card stats-card">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-book me-2"></i>Performa Per Mata Pelajaran
                    </h6>
                </div>
                <div class="card-body">
                    @if($subjectPerformance->count() > 0)
                        <div style="max-height: 250px; overflow-y: auto;">
                            @foreach($subjectPerformance as $subject)
                                <div class="d-flex justify-content-between align-items-center mb-3 p-2 border rounded">
                                    <div>
                                        <strong>{{ Str::limit($subject->mata_pelajaran, 25) }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            {{ $subject->total_sessions }} sesi | 
                                            H: {{ $subject->hadir_count }} | 
                                            T: {{ $subject->telat_count }} | 
                                            A: {{ $subject->alpha_count }}
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge badge-{{ $subject->hadir_percentage >= 80 ? 'success' : ($subject->hadir_percentage >= 60 ? 'warning' : 'danger') }}">
                                            {{ $subject->hadir_percentage }}%
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-book-open fa-3x mb-3 opacity-50"></i>
                            <p>Belum ada data mata pelajaran</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance History -->
    <div class="row">
        <div class="col-12">
            <div class="card stats-card">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history me-2"></i>Riwayat Kehadiran Terbaru
                    </h6>
                </div>
                <div class="card-body">
                    @if($attendanceHistory->count() > 0)
                        <div class="row">
                            @foreach($attendanceHistory as $attendance)
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="attendance-item card status-{{ $attendance->jam_masuk ? ($attendance->status_masuk == 'telat' ? 'telat' : 'hadir') : 'alpha' }}">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div>
                                                    <strong>{{ $attendance->jadwalKelas->mata_pelajaran }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $attendance->tanggal->format('d/m/Y') }}</small>
                                                </div>
                                                <span class="badge badge-{{ $attendance->jam_masuk ? ($attendance->status_masuk == 'telat' ? 'warning' : 'success') : 'danger' }}">
                                                    {{ $attendance->jam_masuk ? ($attendance->status_masuk == 'telat' ? 'Telat' : 'Hadir') : 'Alpha' }}
                                                </span>
                                            </div>
                                            @if($attendance->jam_masuk)
                                                <div class="row text-center small">
                                                    <div class="col-6">
                                                        <i class="fas fa-sign-in-alt text-success"></i>
                                                        <br>{{ Carbon\Carbon::parse($attendance->jam_masuk)->format('H:i') }}
                                                    </div>
                                                    <div class="col-6">
                                                        <i class="fas fa-sign-out-alt text-info"></i>
                                                        <br>{{ $attendance->jam_keluar ? Carbon\Carbon::parse($attendance->jam_keluar)->format('H:i') : '-' }}
                                                    </div>
                                                </div>
                                            @else
                                                <div class="text-center text-muted small">
                                                    <i class="fas fa-times-circle"></i>
                                                    <br>Tidak hadir
                                                </div>
                                            @endif
                                            @if($attendance->keterangan)
                                                <div class="mt-2">
                                                    <small class="text-muted">
                                                        <i class="fas fa-sticky-note"></i> {{ $attendance->keterangan }}
                                                    </small>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $attendanceHistory->links() }}
                        </div>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-calendar-times fa-3x mb-3 opacity-50"></i>
                            <h5>Belum Ada Data Kehadiran</h5>
                            <p>Belum ada record kehadiran untuk periode yang dipilih</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function togglePeriodInputs() {
    const period = document.querySelector('select[name="period"]').value;
    const monthInput = document.getElementById('monthInput');
    const semesterInput = document.getElementById('semesterInput');
    const yearInput = document.getElementById('yearInput');
    
    monthInput.style.display = period === 'month' ? 'block' : 'none';
    semesterInput.style.display = period === 'semester' ? 'block' : 'none';  
    yearInput.style.display = period === 'year' ? 'block' : 'none';
}

// Weekly Pattern Chart
const weeklyCtx = document.getElementById('weeklyPatternChart').getContext('2d');
const weeklyData = {!! json_encode($weeklyPattern) !!};

const weeklyChart = new Chart(weeklyCtx, {
    type: 'bar',
    data: {
        labels: weeklyData.map(item => item.day_name),
        datasets: [
            {
                label: 'Hadir',
                data: weeklyData.map(item => item.hadir),
                backgroundColor: 'rgba(40, 167, 69, 0.8)',
                borderColor: '#28a745',
                borderWidth: 1
            },
            {
                label: 'Terlambat', 
                data: weeklyData.map(item => item.telat),
                backgroundColor: 'rgba(255, 193, 7, 0.8)',
                borderColor: '#ffc107',
                borderWidth: 1
            },
            {
                label: 'Alpha',
                data: weeklyData.map(item => item.alpha),
                backgroundColor: 'rgba(220, 53, 69, 0.8)',
                borderColor: '#dc3545',
                borderWidth: 1
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top'
            }
        },
        scales: {
            x: {
                stacked: false
            },
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
</script>
@endpush
