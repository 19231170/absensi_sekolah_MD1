@extends('layouts.app')

@section('title', 'QR Code Staff - ' . $user->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-custom">
            <div class="card-header bg-{{ $user->role === 'admin' ? 'danger' : 'info' }} text-white text-center">
                <h4 class="mb-0">
                    <i class="fas fa-qrcode me-2"></i>
                    QR Code {{ $user->role === 'admin' ? 'Administrator' : 'Guru' }}
                </h4>
                <small class="mt-1 d-block opacity-75">
                    {{ $user->name }} - {{ $user->nip ?? 'NIP tidak tersedia' }}
                </small>
            </div>
            <div class="card-body">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <div class="row">
                    <!-- User Info -->
                    <div class="col-md-6">
                        <div class="user-info-card">
                            <h5 class="text-{{ $user->role === 'admin' ? 'danger' : 'info' }} mb-3">
                                <i class="fas fa-{{ $user->role === 'admin' ? 'user-shield' : 'chalkboard-teacher' }} me-2"></i>
                                Informasi {{ $user->role === 'admin' ? 'Administrator' : 'Guru' }}
                            </h5>
                            
                            <div class="info-item">
                                <span class="info-label">Nama:</span>
                                <span class="info-value">{{ $user->name }}</span>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">Role:</span>
                                <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : 'info' }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">NIP:</span>
                                <span class="info-value">{{ $user->nip ?? 'Belum diatur' }}</span>
                            </div>
                            
                            @if($user->mata_pelajaran)
                            <div class="info-item">
                                <span class="info-label">Mata Pelajaran:</span>
                                <span class="info-value">{{ $user->mata_pelajaran }}</span>
                            </div>
                            @endif
                            
                            <div class="info-item">
                                <span class="info-label">QR Code:</span>
                                <span class="info-value">
                                    <code class="bg-light px-2 py-1 rounded">{{ $user->qr_code }}</code>
                                </span>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">PIN:</span>
                                <span class="info-value">
                                    <span class="text-muted">••••</span>
                                    <small class="text-muted">(4 digit rahasia)</small>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- QR Code Display -->
                    <div class="col-md-6">
                        <div class="qr-code-display text-center">
                            <h6 class="text-muted mb-3">Scan QR Code ini untuk login</h6>
                            
                            <div class="qr-container">
                                @php
                                    $backgroundColor = $user->role === 'admin' ? '4CAF50' : '2196F3'; // Green for admin, Blue for guru
                                @endphp
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&bgcolor={{ $backgroundColor }}&color=FFFFFF&data={{ urlencode($user->qr_code) }}" 
                                     alt="QR Code {{ $user->name }}" 
                                     class="qr-image img-fluid">
                                <div class="qr-overlay">
                                    <div class="qr-corners">
                                        <div class="corner top-left"></div>
                                        <div class="corner top-right"></div>
                                        <div class="corner bottom-left"></div>
                                        <div class="corner bottom-right"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="qr-code-text mt-3">
                                <strong>{{ $user->qr_code }}</strong>
                            </div>
                            
                            <div class="mt-4">
                                <a href="{{ route('qr.staff.download') }}" class="btn btn-{{ $user->role === 'admin' ? 'danger' : 'info' }} btn-custom">
                                    <i class="fas fa-download me-2"></i>
                                    Download QR Code
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Usage Instructions -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Cara Menggunakan QR Code
                                </h6>
                                <ol class="mb-0">
                                    <li class="mb-2">
                                        <strong>Login ke Sistem:</strong> 
                                        Kunjungi halaman <a href="{{ route('qr.login.form') }}" target="_blank">login staff</a>
                                    </li>
                                    <li class="mb-2">
                                        <strong>Scan QR Code:</strong> 
                                        Gunakan kamera atau masukkan kode manual: <code>{{ $user->qr_code }}</code>
                                    </li>
                                    <li class="mb-2">
                                        <strong>Masukkan PIN:</strong> 
                                        Ketik PIN 4 digit rahasia Anda
                                    </li>
                                    <li class="mb-0">
                                        <strong>Akses Dashboard:</strong> 
                                        Setelah login berhasil, Anda akan diarahkan ke dashboard sesuai role
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Back to Dashboard -->
                <div class="text-center mt-4">
                    <a href="{{ $user->role === 'admin' ? route('admin.dashboard') : route('guru.dashboard') }}" class="btn btn-secondary btn-custom">
                        <i class="fas fa-arrow-left me-2"></i>
                        Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* User Info Card */
.user-info-card {
    background: rgba(0,0,0,0.02);
    padding: 25px;
    border-radius: 15px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid rgba(0,0,0,0.1);
}

.info-item:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 600;
    color: #6c757d;
}

.info-value {
    font-weight: 500;
}

/* QR Code Display */
.qr-code-display {
    background: rgba(0,0,0,0.02);
    padding: 25px;
    border-radius: 15px;
}

.qr-container {
    position: relative;
    display: inline-block;
    margin: 0 auto;
}

.qr-image {
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    border: 4px solid white;
}

.qr-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    pointer-events: none;
}

.qr-corners {
    position: relative;
    width: 100%;
    height: 100%;
}

.corner {
    position: absolute;
    width: 30px;
    height: 30px;
    border: 3px solid #007bff;
}

.top-left {
    top: -8px;
    left: -8px;
    border-right: none;
    border-bottom: none;
}

.top-right {
    top: -8px;
    right: -8px;
    border-left: none;
    border-bottom: none;
}

.bottom-left {
    bottom: -8px;
    left: -8px;
    border-right: none;
    border-top: none;
}

.bottom-right {
    bottom: -8px;
    right: -8px;
    border-left: none;
    border-top: none;
}

/* QR Code Text */
.qr-code-text {
    font-family: 'Courier New', monospace;
    font-size: 1.2rem;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 2px dashed #dee2e6;
}

/* Button Custom */
.btn-custom {
    border-radius: 25px;
    padding: 12px 25px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-custom:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

/* Card Custom */
.card-custom {
    border: none;
    border-radius: 15px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
}

/* Responsive */
@media (max-width: 768px) {
    .qr-image {
        max-width: 250px;
    }
    
    .corner {
        width: 20px;
        height: 20px;
    }
    
    .info-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Add some hover effects
    $('.qr-container').hover(
        function() {
            $(this).find('.corner').addClass('animate__animated animate__pulse');
        },
        function() {
            $(this).find('.corner').removeClass('animate__animated animate__pulse');
        }
    );
});
</script>
@endpush
