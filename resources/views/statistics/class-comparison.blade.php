@extends('layouts.app')

@section('title', 'Perbandingan Kelas - Statistik Kehadiran')

@push('styles')
<style>
    .comparison-card {
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s;
        margin-bottom: 1rem;
    }
    .comparison-card:hover {
        transform: translateY(-3px);
    }
    .class-header {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
        border-radius: 15px 15px 0 0;
        padding: 1rem;
    }
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        margin-top: 1rem;
    }
    .stat-item {
        text-align: center;
        padding: 0.5rem;
        border-radius: 8px;
        background: #f8f9fa;
    }
    .chart-container {
        height: 400px;
        position: relative;
    }
    .ranking-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        font-size: 0.8rem;
        padding: 0.25rem 0.5rem;
    }
    .top-rank { background: linear-gradient(135deg, #28a745, #20c997); }
    .mid-rank { background: linear-gradient(135deg, #ffc107, #fd7e14); }
    .low-rank { background: linear-gradient(135deg, #dc3545, #e83e8c); }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-balance-scale text-primary me-2"></i>Perbandingan Kehadiran Antar Kelas
                </h1>
                <div class="d-flex gap-2">
                    <a href="{{ route('statistics.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-chart-bar"></i> Statistik Umum
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
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('statistics.class-comparison') }}">
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

    <!-- Period Info -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Periode Analisis:</strong>
                @if($selectedPeriod == 'month')
                    {{ \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->format('F Y') }}
                @elseif($selectedPeriod == 'semester')
                    Semester {{ $selectedSemester == 1 ? 'Ganjil (Juli - Desember)' : 'Genap (Januari - Juni)' }} {{ $selectedYear }}
                @else
                    Tahun {{ $selectedYear }}
                @endif
                ({{ $dateRange['start']->format('d/m/Y') }} - {{ $dateRange['end']->format('d/m/Y') }})
            </div>
        </div>
    </div>

    @if(count($classComparison) > 0)
        <!-- Overall Comparison Chart -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card comparison-card">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-chart-bar me-2"></i>Perbandingan Tingkat Kehadiran Semua Kelas
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="classComparisonChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ranking Summary -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card comparison-card">
                    <div class="card-body text-center">
                        <i class="fas fa-trophy fa-3x text-warning mb-3"></i>
                        <h5>Kelas Terbaik</h5>
                        @php
                            $bestClass = collect($classComparison)->sortByDesc('stats.hadir_percentage')->first();
                        @endphp
                        @if($bestClass)
                            <h4 class="text-primary">{{ $bestClass['kelas']->tingkat }} {{ $bestClass['kelas']->nama_kelas }}</h4>
                            <p class="text-success">{{ $bestClass['stats']['hadir_percentage'] }}% Kehadiran</p>
                            <small class="text-muted">
                                @if($bestClass['kelas']->jurusan)
                                    {{ $bestClass['kelas']->jurusan->nama_jurusan }}
                                @endif
                            </small>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card comparison-card">
                    <div class="card-body text-center">
                        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                        <h5>Perlu Perhatian</h5>
                        @php
                            $needsAttention = collect($classComparison)->where('stats.hadir_percentage', '<', 75)->first();
                        @endphp
                        @if($needsAttention)
                            <h4 class="text-danger">{{ $needsAttention['kelas']->tingkat }} {{ $needsAttention['kelas']->nama_kelas }}</h4>
                            <p class="text-danger">{{ $needsAttention['stats']['hadir_percentage'] }}% Kehadiran</p>
                            <small class="text-muted">
                                @if($needsAttention['kelas']->jurusan)
                                    {{ $needsAttention['kelas']->jurusan->nama_jurusan }}
                                @endif
                            </small>
                        @else
                            <p class="text-success"><i class="fas fa-check-circle"></i> Semua kelas dalam kondisi baik!</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card comparison-card">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-3x text-primary mb-3"></i>
                        <h5>Rata-rata Keseluruhan</h5>
                        @php
                            $totalSessions = collect($classComparison)->sum('stats.total_records');
                            $totalHadir = collect($classComparison)->sum('stats.hadir_count');
                            $overallAverage = $totalSessions > 0 ? round(($totalHadir / $totalSessions) * 100, 1) : 0;
                        @endphp
                        <h4 class="text-info">{{ $overallAverage }}%</h4>
                        <p class="text-muted">Dari {{ number_format($totalSessions) }} total sesi</p>
                        <small class="text-muted">{{ count($classComparison) }} kelas dibandingkan</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Class Comparison -->
        <div class="row">
            @foreach($classComparison as $index => $comparison)
                @php
                    $stats = $comparison['stats'];
                    $kelas = $comparison['kelas'];
                    $rank = $index + 1;
                    $rankClass = $rank <= 3 ? 'top-rank' : ($rank <= count($classComparison) / 2 ? 'mid-rank' : 'low-rank');
                @endphp
                <div class="col-lg-6 col-xl-4 mb-4">
                    <div class="card comparison-card h-100">
                        <div class="class-header position-relative">
                            <div class="ranking-badge badge {{ $rankClass }} text-white">
                                Peringkat #{{ $rank }}
                            </div>
                            <h5 class="mb-1">{{ $kelas->tingkat }} {{ $kelas->nama_kelas }}</h5>
                            <p class="mb-0 opacity-75">
                                @if($kelas->jurusan)
                                    {{ $kelas->jurusan->nama_jurusan }}
                                @else
                                    Umum
                                @endif
                            </p>
                        </div>
                        <div class="card-body">
                            <!-- Main Stats -->
                            <div class="row text-center mb-3">
                                <div class="col-4">
                                    <div class="stat-item bg-primary text-white rounded">
                                        <div class="h4 mb-1">{{ number_format($stats['total_records']) }}</div>
                                        <small>Total Sesi</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-item bg-success text-white rounded">
                                        <div class="h4 mb-1">{{ $stats['hadir_percentage'] }}%</div>
                                        <small>Kehadiran</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-item bg-{{ $stats['alpha_percentage'] > 20 ? 'danger' : ($stats['alpha_percentage'] > 10 ? 'warning' : 'success') }} text-white rounded">
                                        <div class="h4 mb-1">{{ $stats['alpha_percentage'] }}%</div>
                                        <small>Absen</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Progress Bars -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small text-success"><i class="fas fa-check-circle"></i> Hadir</span>
                                    <span class="small">{{ number_format($stats['hadir_count']) }}</span>
                                </div>
                                <div class="progress mb-2" style="height: 8px;">
                                    <div class="progress-bar bg-success" style="width: {{ $stats['hadir_percentage'] }}%"></div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small text-warning"><i class="fas fa-clock"></i> Terlambat</span>
                                    <span class="small">{{ number_format($stats['telat_count']) }}</span>
                                </div>
                                <div class="progress mb-2" style="height: 8px;">
                                    <div class="progress-bar bg-warning" style="width: {{ $stats['telat_percentage'] }}%"></div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small text-danger"><i class="fas fa-times-circle"></i> Absen</span>
                                    <span class="small">{{ number_format($stats['alpha_count']) }}</span>
                                </div>
                                <div class="progress mb-2" style="height: 8px;">
                                    <div class="progress-bar bg-danger" style="width: {{ $stats['alpha_percentage'] }}%"></div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="text-center">
                                <a href="{{ route('statistics.index', ['kelas_id' => $kelas->id, 'period' => $selectedPeriod, 'month' => $selectedMonth, 'semester' => $selectedSemester, 'year' => $selectedYear]) }}" 
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-chart-line"></i> Detail Statistik
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- Empty State -->
        <div class="row">
            <div class="col-12">
                <div class="card comparison-card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-chart-bar fa-5x text-muted mb-4 opacity-50"></i>
                        <h3 class="text-muted">Tidak Ada Data untuk Dibandingkan</h3>
                        <p class="text-muted">Belum ada data kehadiran untuk periode yang dipilih, atau Anda tidak memiliki akses ke kelas manapun.</p>
                        <a href="{{ route('statistics.index') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Kembali ke Statistik Umum
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
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

@if(count($classComparison) > 0)
// Class Comparison Chart
const ctx = document.getElementById('classComparisonChart').getContext('2d');
const comparisonData = {!! json_encode($classComparison) !!};

const classComparisonChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: comparisonData.map(item => item.kelas.tingkat + ' ' + item.kelas.nama_kelas),
        datasets: [
            {
                label: 'Kehadiran (%)',
                data: comparisonData.map(item => item.stats.hadir_percentage),
                backgroundColor: 'rgba(40, 167, 69, 0.8)',
                borderColor: '#28a745',
                borderWidth: 1
            },
            {
                label: 'Terlambat (%)',
                data: comparisonData.map(item => item.stats.telat_percentage),
                backgroundColor: 'rgba(255, 193, 7, 0.8)',
                borderColor: '#ffc107',
                borderWidth: 1
            },
            {
                label: 'Absen (%)',
                data: comparisonData.map(item => item.stats.alpha_percentage),
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
            },
            title: {
                display: true,
                text: 'Perbandingan Kehadiran Antar Kelas (%)'
            }
        },
        scales: {
            x: {
                beginAtZero: true,
                ticks: {
                    maxRotation: 45,
                    minRotation: 45
                }
            },
            y: {
                beginAtZero: true,
                max: 100,
                ticks: {
                    callback: function(value) {
                        return value + '%';
                    }
                }
            }
        },
        interaction: {
            intersect: false,
            mode: 'index'
        }
    }
});
@endif
</script>
@endpush
