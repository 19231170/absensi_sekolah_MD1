@extends('layouts.app')

@section('title', 'Dashboard Administrator')

@section('content')
<div class="row">
    <!-- Welcome Card -->
    <div class="col-12">
        <div class="card card-custom bg-gradient-admin text-white mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 class="mb-2">
                            <i class="fas fa-user-shield me-2"></i>
                            Administrator Dashboard
                        </h3>
                        <p class="mb-1">
                            <i class="fas fa-user me-2"></i>
                            {{ $user->name }}
                        </p>
                        <p class="mb-1">
                            <i class="fas fa-id-badge me-2"></i>
                            NIP: {{ $user->nip ?? 'Belum diatur' }}
                        </p>
                        <p class="mb-0">
                            <i class="fas fa-crown me-2"></i>
                            Full System Access
                        </p>
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

    <!-- Management Cards -->
    <div class="col-md-4">
        <div class="card card-custom h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-plus me-2"></i>
                    Kelola Jadwal
                </h5>
            </div>
            <div class="card-body text-center">
                <div class="action-icon mb-3">
                    <i class="fas fa-calendar-plus fa-3x text-primary"></i>
                </div>
                <p class="text-muted mb-3">
                    CRUD lengkap untuk jadwal persesi kelas
                </p>
                <a href="{{ route('jadwal-kelas.index') }}" class="btn btn-primary btn-custom">
                    <i class="fas fa-cogs me-2"></i>
                    Kelola Jadwal
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-custom h-100">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">
                    <i class="fas fa-trash-alt me-2"></i>
                    Hapus Data
                </h5>
            </div>
            <div class="card-body text-center">
                <div class="action-icon mb-3">
                    <i class="fas fa-database fa-3x text-danger"></i>
                </div>
                <p class="text-muted mb-3">
                    Hapus semua data dummy untuk reset sistem
                </p>
                <button type="button" class="btn btn-danger btn-custom" onclick="confirmDeleteAllData()">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Reset Sistem
                </button>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-custom h-100">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="fas fa-qrcode me-2"></i>
                    QR Management
                </h5>
            </div>
            <div class="card-body text-center">
                <div class="action-icon mb-3">
                    <i class="fas fa-qrcode fa-3x text-info"></i>
                </div>
                <p class="text-muted mb-3">
                    Generate dan download QR Code staff & siswa
                </p>
                <div class="d-grid gap-2">
                    <a href="{{ route('qr.staff.generate') }}" class="btn btn-info btn-sm">
                        <i class="fas fa-user-shield me-2"></i>
                        QR Staff Saya
                    </a>
                    <a href="{{ route('qr.index') }}" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-users me-2"></i>
                        QR Siswa
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reporting Section -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card card-custom">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="fas fa-chart-bar me-2"></i>
                    Laporan & Analisis
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <a href="{{ route('absensi.laporan') }}" class="btn btn-success btn-sm w-100 mb-2">
                            <i class="fas fa-file-alt me-2"></i>
                            Laporan Absensi
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('absensi.index') }}" class="btn btn-success btn-sm w-100 mb-2">
                            <i class="fas fa-eye me-2"></i>
                            Monitor Real-time
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card card-custom">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0">
                    <i class="fas fa-download me-2"></i>
                    Download Center
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <a href="{{ route('qr.download.all') }}" class="btn btn-warning btn-sm w-100 mb-2">
                            <i class="fas fa-file-archive me-2"></i>
                            QR ZIP
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('qr.download.all.pdf') }}" class="btn btn-warning btn-sm w-100 mb-2">
                            <i class="fas fa-file-pdf me-2"></i>
                            QR PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Section -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card card-custom">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    Statistik Sistem
                </h5>
            </div>
            <div class="card-body">
                @php
                    $today = \Carbon\Carbon::today();
                    $todayAbsensi = \App\Models\Absensi::whereDate('tanggal', $today)->count();
                    $totalSiswa = \App\Models\Siswa::count();
                    $totalGuru = \App\Models\User::where('role', 'guru')->count();
                    $totalKelas = \App\Models\Kelas::count();
                    $totalJadwal = \App\Models\JadwalKelas::count();
                    $persentaseAbsensi = $totalSiswa > 0 ? round(($todayAbsensi / $totalSiswa) * 100, 1) : 0;
                @endphp
                
                <div class="row text-center">
                    <div class="col-md-2">
                        <div class="stat-card">
                            <div class="stat-icon text-primary">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                            <h4 class="stat-number">{{ $totalSiswa }}</h4>
                            <p class="stat-label">Total Siswa</p>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="stat-card">
                            <div class="stat-icon text-info">
                                <i class="fas fa-chalkboard-teacher fa-2x"></i>
                            </div>
                            <h4 class="stat-number">{{ $totalGuru }}</h4>
                            <p class="stat-label">Total Guru</p>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="stat-card">
                            <div class="stat-icon text-success">
                                <i class="fas fa-school fa-2x"></i>
                            </div>
                            <h4 class="stat-number">{{ $totalKelas }}</h4>
                            <p class="stat-label">Total Kelas</p>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="stat-card">
                            <div class="stat-icon text-warning">
                                <i class="fas fa-calendar fa-2x"></i>
                            </div>
                            <h4 class="stat-number">{{ $totalJadwal }}</h4>
                            <p class="stat-label">Total Jadwal</p>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="stat-card">
                            <div class="stat-icon text-success">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                            <h4 class="stat-number">{{ $todayAbsensi }}</h4>
                            <p class="stat-label">Hadir Hari Ini</p>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="stat-card">
                            <div class="stat-icon text-danger">
                                <i class="fas fa-percentage fa-2x"></i>
                            </div>
                            <h4 class="stat-number">{{ $persentaseAbsensi }}%</h4>
                            <p class="stat-label">Kehadiran</p>
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
                <small class="text-muted">10 aktivitas terakhir</small>
            </div>
            <div class="card-body">
                @php
                    $recentAbsensi = \App\Models\Absensi::with(['siswa', 'jamSekolah'])
                                                       ->latest()
                                                       ->take(10)
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
                    Selesai mengadministrasi?
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

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Konfirmasi Reset Sistem
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Peringatan!</strong> Tindakan ini akan menghapus SEMUA data dummy dari sistem.
                </div>
                <p>Data yang akan dihapus:</p>
                <ul>
                    <li>Semua data absensi</li>
                    <li>Data siswa dummy</li>
                    <li>Jadwal kelas dummy</li>
                </ul>
                <p class="text-danger"><strong>Tindakan ini tidak dapat dibatalkan!</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>
                    Batal
                </button>
                <form method="POST" action="{{ route('admin.delete-dummy') }}" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>
                        Ya, Hapus Semua Data
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Admin Gradient */
.bg-gradient-admin {
    background: linear-gradient(135deg, #dc3545 0%, #6f42c1 100%);
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
    font-size: 1.8rem;
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
function confirmDeleteAllData() {
    $('#deleteModal').modal('show');
}

$(document).ready(function() {
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
