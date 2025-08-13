@extends('layouts.app')

@section('title', 'Dashboard Guru')

@section('content')
<div class="row">
    <!-- Welcome Card -->
    <div class="col-12">
        <div class="card card-custom bg-gradient-primary text-white mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 class="mb-2">
                            <i class="fas fa-chalkboard-teacher me-2"></i>
                            Selamat datang, {{ $user->name }}!
                        </h3>
                        <p class="mb-1">
                            <i class="fas fa-id-badge me-2"></i>
                            NIP: {{ $user->nip ?? 'Belum diatur' }}
                        </p>
                        @if($user->mata_pelajaran)
                        <p class="mb-0">
                            <i class="fas fa-book me-2"></i>
                            Mata Pelajaran: {{ $user->mata_pelajaran }}
                        </p>
                        @endif
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="user-avatar-large">
                            <span class="initials">{{ $user->initials }}</span>
                        </div>
                        <small class="d-block mt-2 opacity-75">
                            Login terakhir: {{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Pertama kali' }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-md-4">
        <div class="card card-custom h-100">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="fas fa-qrcode me-2"></i>
                    Scan Absensi
                </h5>
            </div>
            <div class="card-body text-center">
                <div class="action-icon mb-3">
                    <i class="fas fa-qrcode fa-3x text-info"></i>
                </div>
                <p class="text-muted mb-3">
                    Scan QR Code siswa untuk membuka pelajaran
                </p>
                <a href="{{ route('jadwal-kelas.index') }}" class="btn btn-info btn-custom">
                    <i class="fas fa-eye me-2"></i>
                    Buka Absensi
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-custom h-100">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-alt me-2"></i>
                    Jadwal Kelas
                </h5>
            </div>
            <div class="card-body text-center">
                <div class="action-icon mb-3">
                    <i class="fas fa-calendar-alt fa-3x text-success"></i>
                </div>
                <p class="text-muted mb-3">
                    Lihat jadwal persesi kelas (read-only)
                </p>
                <a href="{{ route('jadwal-kelas.index') }}" class="btn btn-success btn-custom">
                    <i class="fas fa-calendar me-2"></i>
                    Lihat Jadwal
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-custom h-100">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0">
                    <i class="fas fa-chart-bar me-2"></i>
                    Laporan Kelas
                </h5>
            </div>
            <div class="card-body text-center">
                <div class="action-icon mb-3">
                    <i class="fas fa-chart-bar fa-3x text-warning"></i>
                </div>
                <p class="text-muted mb-3">
                    Laporan absensi untuk mata pelajaran Anda
                </p>
                <a href="{{ route('absensi.laporan') }}" class="btn btn-warning btn-custom">
                    <i class="fas fa-file-alt me-2"></i>
                    Lihat Laporan
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Export & QR Section -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card card-custom">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-download me-2"></i>
                    Export Laporan
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Download laporan absensi dalam format PDF atau Excel</p>
                <div class="row text-center">
                    <div class="col-6">
                        <a href="{{ route('absensi.laporan') }}?export=pdf" class="btn btn-danger btn-sm w-100 mb-2">
                            <i class="fas fa-file-pdf me-2"></i>
                            Export PDF
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('absensi.laporan') }}?export=excel" class="btn btn-success btn-sm w-100 mb-2">
                            <i class="fas fa-file-excel me-2"></i>
                            Export Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card card-custom">
            <div class="card-header bg-purple text-white">
                <h5 class="mb-0">
                    <i class="fas fa-qrcode me-2"></i>
                    QR Code Management
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Download QR Code untuk keperluan mengajar</p>
                <div class="row text-center">
                    <div class="col-6">
                        <a href="{{ route('qr.staff.generate') }}" class="btn btn-purple btn-sm w-100 mb-2">
                            <i class="fas fa-user-shield me-2"></i>
                            QR Staff Saya
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('qr.download.all') }}?guru={{ $user->id }}" class="btn btn-info btn-sm w-100 mb-2">
                            <i class="fas fa-file-archive me-2"></i>
                            QR Siswa ZIP
                        </a>
                    </div>
                    <div class="col-12">
                        <a href="{{ route('qr.download.all.pdf') }}?guru={{ $user->id }}" class="btn btn-secondary btn-sm w-100 mb-2">
                            <i class="fas fa-file-pdf me-2"></i>
                            QR Siswa PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Row -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card card-custom">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    Statistik Hari Ini
                </h5>
            </div>
            <div class="card-body">
                @php
                    $today = \Carbon\Carbon::today();
                    $todayAbsensi = \App\Models\Absensi::whereDate('tanggal', $today)->count();
                    $totalSiswa = \App\Models\Siswa::count();
                    $persentaseAbsensi = $totalSiswa > 0 ? round(($todayAbsensi / $totalSiswa) * 100, 1) : 0;
                @endphp
                
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon text-primary">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                            <h4 class="stat-number">{{ $totalSiswa }}</h4>
                            <p class="stat-label">Total Siswa</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon text-success">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                            <h4 class="stat-number">{{ $todayAbsensi }}</h4>
                            <p class="stat-label">Hadir Hari Ini</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon text-warning">
                                <i class="fas fa-percentage fa-2x"></i>
                            </div>
                            <h4 class="stat-number">{{ $persentaseAbsensi }}%</h4>
                            <p class="stat-label">Persentase Kehadiran</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon text-info">
                                <i class="fas fa-calendar-day fa-2x"></i>
                            </div>
                            <h4 class="stat-number">{{ $today->format('d') }}</h4>
                            <p class="stat-label">{{ $today->format('M Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card card-custom">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-clock me-2"></i>
                    Aktivitas Terbaru
                </h5>
                <small class="text-muted">5 absensi terakhir</small>
            </div>
            <div class="card-body">
                @php
                    $recentAbsensi = \App\Models\Absensi::with(['siswa', 'jamSekolah'])
                                                       ->latest()
                                                       ->take(5)
                                                       ->get();
                @endphp

                @if($recentAbsensi->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($recentAbsensi as $absensi)
                        <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                            <div class="d-flex align-items-center">
                                <div class="activity-icon me-3">
                                    <i class="fas fa-user-check text-success"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">{{ $absensi->siswa->nama ?? 'Siswa tidak ditemukan' }}</h6>
                                    <small class="text-muted">
                                        {{ $absensi->jamSekolah->nama_sesi ?? 'Sesi tidak ditemukan' }} - 
                                        {{ $absensi->jam_masuk ?? 'Waktu tidak tercatat' }}
                                    </small>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-success">Hadir</span>
                                <small class="text-muted d-block">
                                    {{ $absensi->created_at->diffForHumans() }}
                                </small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Belum ada aktivitas absensi hari ini.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Logout Card -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card card-custom border-danger">
            <div class="card-body text-center">
                <h6 class="text-danger mb-3">
                    <i class="fas fa-sign-out-alt me-2"></i>
                    Selesai bekerja?
                </h6>
                <form method="POST" action="{{ route('qr.login.logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-custom" onclick="return confirm('Apakah Anda yakin ingin logout?')">
                        <i class="fas fa-power-off me-2"></i>
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Purple Background for QR Card */
.bg-purple {
    background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);
}

/* User Avatar */
.user-avatar-large {
    width: 80px;
    height: 80px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    backdrop-filter: blur(10px);
}

.user-avatar-large .initials {
    font-size: 28px;
    font-weight: bold;
    color: white;
}

/* Gradient Background */
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

/* Action Icons */
.action-icon {
    padding: 20px;
    border-radius: 50%;
    background: rgba(0,0,0,0.05);
    display: inline-block;
}

/* Statistics Cards */
.stat-card {
    padding: 20px;
    border-radius: 10px;
    background: rgba(0,0,0,0.02);
    transition: all 0.3s ease;
}

.stat-card:hover {
    background: rgba(0,0,0,0.05);
    transform: translateY(-2px);
}

.stat-icon {
    margin-bottom: 15px;
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 5px;
}

.stat-label {
    color: #6c757d;
    font-size: 0.9rem;
    margin-bottom: 0;
}

/* Activity Icons */
.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(40, 167, 69, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Button Styles */
.btn-custom {
    border-radius: 25px;
    padding: 10px 25px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-custom:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

/* Card Hover Effects */
.card-custom {
    border: none;
    border-radius: 15px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.card-custom:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 25px rgba(0,0,0,0.15);
}

/* Responsive Design */
@media (max-width: 768px) {
    .user-avatar-large {
        width: 60px;
        height: 60px;
    }
    
    .user-avatar-large .initials {
        font-size: 20px;
    }
    
    .stat-number {
        font-size: 1.5rem;
    }
    
    .action-icon {
        padding: 15px;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Auto refresh stats every 5 minutes
    setInterval(function() {
        location.reload();
    }, 300000); // 5 minutes
    
    // Add some interactivity
    $('.stat-card').hover(
        function() {
            $(this).find('.stat-icon').addClass('animate__animated animate__pulse');
        },
        function() {
            $(this).find('.stat-icon').removeClass('animate__animated animate__pulse');
        }
    );
});
</script>
@endpush
