@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-user-graduate text-primary"></i> 
                        Detail Siswa & Statistik Pribadi
                    </h5>
                    <div class="btn-group">
                        <a href="{{ route('statistics.student', $siswa->nis) }}" class="btn btn-sm btn-info" title="Lihat di Halaman Statistik">
                            <i class="fas fa-chart-line"></i> Statistik Detail
                        </a>
                        <a href="{{ route('siswa.edit', $siswa->nis) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('siswa.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Student Profile Section -->
                    <div class="row mb-4">
                        <div class="col-md-4 text-center">
                            <div class="p-3 border rounded mb-3 bg-light">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&bgcolor=FFFFFF&color=000000&data={{ $siswa->qr_code }}" 
                                     alt="QR Code {{ $siswa->qr_code }}" class="img-fluid">
                            </div>
                            <h5 class="mb-0">{{ $siswa->qr_code }}</h5>
                            <p class="text-muted small">QR Code Absensi</p>
                            
                            <!-- Overall Performance Badge -->
                            <div class="mt-3">
                                <span class="badge bg-{{ $statistics['indicators']['overall_performance']['color'] }} p-2">
                                    <i class="fas fa-{{ $statistics['indicators']['overall_performance']['icon'] }}"></i>
                                    {{ $statistics['indicators']['overall_performance']['level'] }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="30%">NIS</th>
                                    <td>{{ $siswa->nis }}</td>
                                </tr>
                                <tr>
                                    <th>Nama</th>
                                    <td><strong>{{ $siswa->nama }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Kelas</th>
                                    <td>{{ $siswa->kelas ? $siswa->kelas->tingkat . ' ' . $siswa->kelas->nama_kelas : 'Kelas tidak diketahui' }}</td>
                                </tr>
                                <tr>
                                    <th>Jurusan</th>
                                    <td>{{ $siswa->kelas && $siswa->kelas->jurusan ? $siswa->kelas->jurusan->nama_jurusan : 'Jurusan tidak diketahui' }}</td>
                                </tr>
                                <tr>
                                    <th>Jenis Kelamin</th>
                                    <td>{{ $siswa->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        <span class="badge bg-{{ $siswa->status_aktif ? 'success' : 'danger' }}">
                                            {{ $siswa->status_aktif ? 'Aktif' : 'Tidak Aktif' }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h6 class="border-bottom pb-2 mb-3">📊 Statistik Kehadiran Pribadi</h6>
                        </div>
                        
                        <!-- Monthly Stats -->
                        <div class="col-md-6">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0">📅 Bulan Ini ({{ $statistics['current_month'] }})</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <h4 class="text-success">{{ $statistics['monthly']['present_days'] }}</h4>
                                            <small>Hari Hadir</small>
                                        </div>
                                        <div class="col-6">
                                            <h4 class="text-primary">{{ $statistics['monthly_rate'] }}%</h4>
                                            <small>Tingkat Kehadiran</small>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row text-center small">
                                        <div class="col-4">
                                            <span class="text-warning">{{ $statistics['monthly']['late_days'] }}</span><br>
                                            <small>Terlambat</small>
                                        </div>
                                        <div class="col-4">
                                            <span class="text-danger">{{ $statistics['monthly']['absent_days'] }}</span><br>
                                            <small>Tidak Hadir</small>
                                        </div>
                                        <div class="col-4">
                                            <span class="text-info">{{ $statistics['monthly']['total_days'] }}</span><br>
                                            <small>Total Hari</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Semester Stats -->
                        <div class="col-md-6">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0">🎓 {{ $statistics['current_semester'] }}</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <h4 class="text-success">{{ $statistics['semester']['present_days'] }}</h4>
                                            <small>Hari Hadir</small>
                                        </div>
                                        <div class="col-6">
                                            <h4 class="text-success">{{ $statistics['semester_rate'] }}%</h4>
                                            <small>Tingkat Kehadiran</small>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row text-center small">
                                        <div class="col-4">
                                            <span class="text-warning">{{ $statistics['semester']['late_days'] }}</span><br>
                                            <small>Terlambat</small>
                                        </div>
                                        <div class="col-4">
                                            <span class="text-danger">{{ $statistics['semester']['absent_days'] }}</span><br>
                                            <small>Tidak Hadir</small>
                                        </div>
                                        <div class="col-4">
                                            <span class="text-info">{{ $statistics['semester']['total_days'] }}</span><br>
                                            <small>Total Hari</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Indicators -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h6 class="border-bottom pb-2 mb-3">🎯 Indikator Performa</h6>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-{{ $statistics['indicators']['punctuality']['color'] }}">
                                    ⏰ {{ $statistics['indicators']['punctuality']['level'] }}
                                </h6>
                                <small>{{ round($statistics['indicators']['punctuality']['percentage'], 1) }}% Keterlambatan</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-{{ $statistics['indicators']['consistency']['color'] }}">
                                    📈 {{ $statistics['indicators']['consistency']['level'] }}
                                </h6>
                                <small>Konsistensi Kehadiran</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-{{ $statistics['indicators']['improvement_trend']['color'] }}">
                                    <i class="fas fa-{{ $statistics['indicators']['improvement_trend']['icon'] }}"></i>
                                    {{ $statistics['indicators']['improvement_trend']['trend'] }}
                                </h6>
                                <small>Trend Perkembangan</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-{{ $statistics['indicators']['overall_performance']['color'] }}">
                                    <i class="fas fa-{{ $statistics['indicators']['overall_performance']['icon'] }}"></i>
                                    {{ $statistics['indicators']['overall_performance']['level'] }}
                                </h6>
                                <small>Performa Keseluruhan</small>
                            </div>
                        </div>
                    </div>

                    <!-- Weekly Pattern Chart -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">📊 Pola Kehadiran Mingguan (4 Minggu Terakhir)</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="weeklyPatternChart" height="100"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Subject-wise Attendance -->
                    @if(!$statistics['subject_attendance']->isEmpty())
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">📚 Kehadiran Per Mata Pelajaran (Semester Ini)</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Mata Pelajaran</th>
                                                    <th class="text-center">Hadir</th>
                                                    <th class="text-center">Terlambat</th>
                                                    <th class="text-center">Tidak Hadir</th>
                                                    <th class="text-center">Total</th>
                                                    <th class="text-center">Tingkat Kehadiran</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($statistics['subject_attendance'] as $subject)
                                                <tr>
                                                    <td><strong>{{ $subject['subject'] }}</strong></td>
                                                    <td class="text-center">
                                                        <span class="badge bg-success">{{ $subject['present'] }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-warning">{{ $subject['late'] }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-danger">{{ $subject['absent'] }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-info">{{ $subject['total'] }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="progress" style="width: 80px; height: 20px;">
                                                            <div class="progress-bar 
                                                                @if($subject['attendance_rate'] >= 90) bg-success
                                                                @elseif($subject['attendance_rate'] >= 80) bg-primary  
                                                                @elseif($subject['attendance_rate'] >= 70) bg-warning
                                                                @else bg-danger
                                                                @endif" 
                                                                style="width: {{ $subject['attendance_rate'] }}%">
                                                                {{ $subject['attendance_rate'] }}%
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Recent Attendance History -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">🕒 Riwayat Kehadiran Terbaru</h6>
                                </div>
                                <div class="card-body">
                                    @if($statistics['recent_attendance']->isEmpty())
                                        <p class="text-muted text-center py-4">Belum ada data absensi</p>
                                    @else
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Tanggal</th>
                                                        <th>Mata Pelajaran</th>
                                                        <th>Jam Masuk</th>
                                                        <th>Jam Keluar</th>
                                                        <th>Status</th>
                                                        <th>Jenis</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($statistics['recent_attendance'] as $absensi)
                                                    <tr>
                                                        <td>{{ \Carbon\Carbon::parse($absensi->tanggal)->format('d M Y') }}</td>
                                                        <td>{{ $absensi->jadwalKelas->mata_pelajaran ?? 'N/A' }}</td>
                                                        <td>{{ $absensi->jam_masuk ? \Carbon\Carbon::parse($absensi->jam_masuk)->format('H:i') : '-' }}</td>
                                                        <td>{{ $absensi->jam_keluar ? \Carbon\Carbon::parse($absensi->jam_keluar)->format('H:i') : '-' }}</td>
                                                        <td>
                                                            @if($absensi->jam_masuk)
                                                                <span class="badge bg-success">Hadir</span>
                                                            @else
                                                                <span class="badge bg-danger">Tidak Hadir</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($absensi->status_masuk == 'telat')
                                                                <span class="badge bg-warning">Terlambat</span>
                                                            @elseif($absensi->status_masuk == 'hadir')
                                                                <span class="badge bg-success">Tepat Waktu</span>
                                                            @else
                                                                <span class="badge bg-secondary">{{ ucfirst($absensi->status_masuk) }}</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Weekly Pattern Chart
    const weeklyPatternCtx = document.getElementById('weeklyPatternChart').getContext('2d');
    const weeklyPatternChart = new Chart(weeklyPatternCtx, {
        type: 'line',
        data: {
            labels: @json(array_column($statistics['weekly_pattern'], 'week_label')),
            datasets: [{
                label: 'Tingkat Kehadiran (%)',
                data: @json(array_column($statistics['weekly_pattern'], 'percentage')),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true
                },
                title: {
                    display: true,
                    text: 'Trend Kehadiran Mingguan'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });
});
</script>
@endsection
