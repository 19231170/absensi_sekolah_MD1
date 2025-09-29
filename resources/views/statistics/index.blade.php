@extends('layouts.app')

@section('title', 'Statistik Kehadiran Siswa')

@push('styles')
<style>
    .stats-card {
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s;
    }
    .stats-card:hover {
        transform: translateY(-5px);
    }
    .stats-number {
        font-size: 2.5rem;
        font-weight: bold;
    }
    .stats-label {
        font-size: 0.9rem;
        color: #6c757d;
        margin-top: -5px;
    }
    .progress-circle {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: white;
        font-size: 0.9rem;
    }
    .bg-gradient-primary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    }
    .bg-gradient-success {
        background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
    }
    .bg-gradient-warning {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
    }
    .bg-gradient-danger {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    }
    .table-responsive {
        border-radius: 10px;
        overflow: hidden;
    }
    .filter-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 15px;
    }
    .student-card {
        border-left: 4px solid;
        transition: all 0.3s ease;
    }
    .student-card:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    .top-student { border-left-color: #28a745; }
    .problem-student { border-left-color: #dc3545; }
    .chart-container {
        height: 300px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-chart-bar text-primary me-2"></i>Statistik Kehadiran Siswa
                </h1>
                <div class="d-flex gap-2">
                    <a href="{{ route('statistics.class-comparison') }}" class="btn btn-outline-primary">
                        <i class="fas fa-balance-scale"></i> Perbandingan Kelas
                    </a>
                    <button class="btn btn-primary" onclick="window.print()">
                        <i class="fas fa-print"></i> Cetak Laporan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card filter-card">
                <div class="card-body">
                    <form method="GET" action="{{ route('statistics.index') }}" id="filterForm">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="form-label"><strong>Kelas</strong></label>
                                <select name="kelas_id" class="form-select" onchange="this.form.submit()">
                                    <option value="">Semua Kelas</option>
                                    @foreach($kelasList as $kelas)
                                        <option value="{{ $kelas->id }}" {{ $selectedKelas == $kelas->id ? 'selected' : '' }}>
                                            {{ $kelas->tingkat }} {{ $kelas->nama_kelas }} 
                                            @if($kelas->jurusan) - {{ $kelas->jurusan->nama_jurusan }} @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label"><strong>Periode</strong></label>
                                <select name="period" class="form-select" id="periodSelect" onchange="togglePeriodInputs(); this.form.submit()">
                                    <option value="month" {{ $selectedPeriod == 'month' ? 'selected' : '' }}>Bulanan</option>
                                    <option value="semester" {{ $selectedPeriod == 'semester' ? 'selected' : '' }}>Semester</option>
                                    <option value="year" {{ $selectedPeriod == 'year' ? 'selected' : '' }}>Tahunan</option>
                                </select>
                            </div>
                            <div class="col-md-2" id="monthInput" style="{{ $selectedPeriod != 'month' ? 'display:none' : '' }}">
                                <label class="form-label"><strong>Bulan</strong></label>
                                <input type="month" name="month" class="form-control" value="{{ $selectedMonth }}" onchange="this.form.submit()">
                            </div>
                            <div class="col-md-2" id="semesterInput" style="{{ $selectedPeriod != 'semester' ? 'display:none' : '' }}">
                                <label class="form-label"><strong>Semester</strong></label>
                                <select name="semester" class="form-select" onchange="this.form.submit()">
                                    <option value="1" {{ $selectedSemester == 1 ? 'selected' : '' }}>Ganjil (Juli-Des)</option>
                                    <option value="2" {{ $selectedSemester == 2 ? 'selected' : '' }}>Genap (Jan-Jun)</option>
                                </select>
                            </div>
                            <div class="col-md-2" id="yearInput" style="{{ $selectedPeriod == 'month' ? 'display:none' : '' }}">
                                <label class="form-label"><strong>Tahun</strong></label>
                                <select name="year" class="form-select" onchange="this.form.submit()">
                                    @for($year = date('Y'); $year >= date('Y') - 3; $year--)
                                        <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-secondary" onclick="resetFilter()">
                                    <i class="fas fa-redo"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Overall Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stats-card bg-gradient-primary text-white h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <div class="stats-number">{{ number_format($overallStats['total_records']) }}</div>
                            <div class="stats-label text-light">Total Sesi Pembelajaran</div>
                        </div>
                        <div class="col-4 text-center">
                            <i class="fas fa-book-open fa-3x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stats-card bg-gradient-success text-white h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <div class="stats-number">{{ $overallStats['hadir_percentage'] }}%</div>
                            <div class="stats-label text-light">Tingkat Kehadiran</div>
                            <small class="text-light">{{ number_format($overallStats['hadir_count']) }} dari {{ number_format($overallStats['total_records']) }}</small>
                        </div>
                        <div class="col-4 text-center">
                            <i class="fas fa-check-circle fa-3x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stats-card bg-gradient-warning text-white h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <div class="stats-number">{{ $overallStats['telat_percentage'] }}%</div>
                            <div class="stats-label text-white">Tingkat Keterlambatan</div>
                            <small class="text-white">{{ number_format($overallStats['telat_count']) }} dari {{ number_format($overallStats['total_records']) }}</small>
                        </div>
                        <div class="col-4 text-center">
                            <i class="fas fa-clock fa-3x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stats-card bg-gradient-danger text-white h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <div class="stats-number">{{ $overallStats['alpha_percentage'] }}%</div>
                            <div class="stats-label text-light">Tingkat Absen</div>
                            <small class="text-light">{{ number_format($overallStats['alpha_count']) }} dari {{ number_format($overallStats['total_records']) }}</small>
                        </div>
                        <div class="col-4 text-center">
                            <i class="fas fa-user-times fa-3x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Lists Row -->
    <div class="row mb-4">
        <!-- Attendance Trend Chart -->
        <div class="col-lg-8 mb-4">
            <div class="card stats-card h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-line me-2"></i>Tren Kehadiran Harian
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="attendanceTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subject Statistics -->
        <div class="col-lg-4 mb-4">
            <div class="card stats-card h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-book me-2"></i>Statistik Per Mata Pelajaran
                    </h6>
                </div>
                <div class="card-body">
                    @if($subjectStats->count() > 0)
                        <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Mata Pelajaran</th>
                                        <th class="text-center">Kehadiran</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($subjectStats as $subject)
                                        <tr>
                                            <td class="small">{{ Str::limit($subject->mata_pelajaran, 20) }}</td>
                                            <td class="text-center">
                                                <span class="badge badge-{{ $subject->hadir_percentage >= 80 ? 'success' : ($subject->hadir_percentage >= 60 ? 'warning' : 'danger') }}">
                                                    {{ $subject->hadir_percentage }}%
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-chart-bar fa-3x mb-3 opacity-50"></i>
                            <p>Belum ada data mata pelajaran</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Top Students and Problem Students -->
    <div class="row">
        <!-- Top Performing Students -->
        <div class="col-lg-6 mb-4">
            <div class="card stats-card">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-medal me-2"></i>Siswa Berprestasi (Kehadiran Tertinggi)
                    </h6>
                </div>
                <div class="card-body">
                    @if($topStudents->count() > 0)
                        @foreach($topStudents as $index => $student)
                            <div class="student-card top-student card mb-3">
                                <div class="card-body py-2">
                                    <div class="row align-items-center">
                                        <div class="col-1">
                                            <div class="text-center">
                                                @if($index == 0)
                                                    <i class="fas fa-trophy text-warning fa-lg"></i>
                                                @elseif($index == 1)
                                                    <i class="fas fa-medal text-secondary fa-lg"></i>
                                                @elseif($index == 2)
                                                    <i class="fas fa-medal text-warning fa-lg"></i>
                                                @else
                                                    <span class="badge badge-primary">{{ $index + 1 }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <strong>{{ $student->siswa->nama }}</strong><br>
                                            <small class="text-muted">
                                                NIS: {{ $student->nis }} | 
                                                {{ $student->siswa->kelas->tingkat }} {{ $student->siswa->kelas->nama_kelas }}
                                            </small>
                                        </div>
                                        <div class="col-3 text-center">
                                            <div class="progress-circle bg-success">
                                                {{ $student->attendance_percentage }}%
                                            </div>
                                        </div>
                                        <div class="col-2 text-end">
                                            <a href="{{ route('statistics.student', $student->nis) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-12">
                                            <div class="row text-center small text-muted">
                                                <div class="col-4">
                                                    <i class="fas fa-check-circle text-success"></i> {{ $student->hadir_count }} Hadir
                                                </div>
                                                <div class="col-4">
                                                    <i class="fas fa-clock text-warning"></i> {{ $student->telat_count }} Telat
                                                </div>
                                                <div class="col-4">
                                                    <i class="fas fa-times-circle text-danger"></i> {{ $student->alpha_count }} Alpha
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-user-graduate fa-3x mb-3 opacity-50"></i>
                            <p>Belum ada data siswa berprestasi</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Students with Issues -->
        <div class="col-lg-6 mb-4">
            <div class="card stats-card">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>Siswa Bermasalah (Perlu Perhatian)
                    </h6>
                </div>
                <div class="card-body">
                    @if($problemStudents->count() > 0)
                        @foreach($problemStudents as $student)
                            <div class="student-card problem-student card mb-3">
                                <div class="card-body py-2">
                                    <div class="row align-items-center">
                                        <div class="col-1">
                                            <i class="fas fa-exclamation-circle text-danger"></i>
                                        </div>
                                        <div class="col-6">
                                            <strong>{{ $student->siswa->nama }}</strong><br>
                                            <small class="text-muted">
                                                NIS: {{ $student->nis }} | 
                                                {{ $student->siswa->kelas->tingkat }} {{ $student->siswa->kelas->nama_kelas }}
                                            </small>
                                        </div>
                                        <div class="col-3 text-center">
                                            <div class="progress-circle bg-danger">
                                                {{ $student->alpha_percentage }}%
                                            </div>
                                            <small class="text-muted">Alpha</small>
                                        </div>
                                        <div class="col-2 text-end">
                                            <a href="{{ route('statistics.student', $student->nis) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-12">
                                            <div class="row text-center small text-muted">
                                                <div class="col-4">
                                                    <i class="fas fa-check-circle text-success"></i> {{ $student->hadir_count }} Hadir
                                                </div>
                                                <div class="col-4">
                                                    <i class="fas fa-clock text-warning"></i> {{ $student->telat_count }} Telat
                                                </div>
                                                <div class="col-4">
                                                    <i class="fas fa-times-circle text-danger"></i> {{ $student->alpha_count }} Alpha
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-smile fa-3x mb-3 text-success opacity-50"></i>
                            <p>Tidak ada siswa bermasalah! 👏</p>
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
    const period = document.getElementById('periodSelect').value;
    const monthInput = document.getElementById('monthInput');
    const semesterInput = document.getElementById('semesterInput');
    const yearInput = document.getElementById('yearInput');
    
    monthInput.style.display = period === 'month' ? 'block' : 'none';
    semesterInput.style.display = period === 'semester' ? 'block' : 'none';
    yearInput.style.display = period !== 'month' ? 'block' : 'none';
}

function resetFilter() {
    window.location.href = '{{ route("statistics.index") }}';
}

// Attendance Trend Chart
const ctx = document.getElementById('attendanceTrendChart').getContext('2d');
const attendanceData = {!! json_encode($attendanceTrend) !!};

const trendChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: attendanceData.map(item => {
            const date = new Date(item.date);
            return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });
        }),
        datasets: [
            {
                label: 'Hadir',
                data: attendanceData.map(item => item.hadir),
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                fill: true,
                tension: 0.4
            },
            {
                label: 'Terlambat',
                data: attendanceData.map(item => item.telat),
                borderColor: '#ffc107',
                backgroundColor: 'rgba(255, 193, 7, 0.1)',
                fill: true,
                tension: 0.4
            },
            {
                label: 'Alpha',
                data: attendanceData.map(item => item.alpha),
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                fill: true,
                tension: 0.4
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            intersect: false,
            mode: 'index'
        },
        plugins: {
            legend: {
                position: 'top',
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

// Auto refresh setiap 5 menit
setTimeout(() => {
    location.reload();
}, 300000);
</script>
@endpush
