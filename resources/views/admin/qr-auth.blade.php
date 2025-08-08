@extends('layouts.app')

@section('title', 'Admin QR Authentication')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-custom">
            <div class="card-header bg-danger text-white text-center">
                <h4 class="mb-0">
                    <i class="fas fa-shield-alt me-2"></i>
                    Admin Authentication
                </h4>
                <small class="mt-1 d-block opacity-75">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    Area Terbatas - Khusus Administrator
                </small>
            </div>
            <div class="card-body text-center">
                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <!-- Warning Section -->
                <div class="alert alert-warning mb-4">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <h5>⚠️ PERINGATAN PENTING ⚠️</h5>
                    <p class="mb-0">
                        QR Code ini memberikan akses ke fungsi admin berbahaya termasuk <strong>penghapusan data dummy</strong>. 
                        Jangan bagikan QR Code ini kepada siapa pun!
                    </p>
                </div>

                <!-- QR Code Display -->
                <div class="card bg-light mb-4">
                    <div class="card-body">
                        <h5 class="card-title text-primary">
                            <i class="fas fa-qrcode me-2"></i>
                            QR Code Admin
                        </h5>
                        
                        <div class="qr-code-container mb-3">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data={{ urlencode(route('admin.dashboard', ['token' => $adminToken])) }}" 
                                 alt="Admin QR Code" 
                                 class="img-fluid border rounded shadow"
                                 style="max-width: 250px;">
                        </div>
                        
                        <div class="token-info">
                            <p class="text-muted mb-2">
                                <i class="fas fa-key me-1"></i>
                                <strong>Token:</strong> 
                                <code class="bg-light px-2 py-1 rounded">{{ Str::limit($adminToken, 20) }}...</code>
                            </p>
                            <p class="text-danger mb-0">
                                <i class="fas fa-clock me-1"></i>
                                <strong>Expires:</strong> {{ session('admin_token_expires')->format('H:i:s d/m/Y') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="row mb-4">
                    <div class="col-md-6 mb-2">
                        <a href="{{ route('admin.download-qr') }}" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-download me-2"></i>
                            Download QR Code
                        </a>
                    </div>
                    <div class="col-md-6 mb-2">
                        <a href="{{ route('admin.dashboard', ['token' => $adminToken]) }}" class="btn btn-success btn-lg w-100">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            Akses Dashboard
                        </a>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="fas fa-info-circle me-2"></i>
                            Cara Menggunakan
                        </h6>
                        <ol class="text-start mb-0">
                            <li><strong>Scan QR Code</strong> menggunakan aplikasi QR scanner di perangkat lain</li>
                            <li><strong>Buka URL</strong> yang muncul setelah scan</li>
                            <li><strong>Akses Dashboard Admin</strong> akan terbuka otomatis</li>
                            <li><strong>Gunakan fungsi admin</strong> seperti hapus data dummy</li>
                        </ol>
                    </div>
                </div>

                <!-- Security Notice -->
                <div class="mt-4">
                    <small class="text-muted">
                        <i class="fas fa-lock me-1"></i>
                        Token ini akan expired dalam <strong>1 jam</strong> untuk keamanan.
                        Silakan generate ulang jika diperlukan.
                    </small>
                </div>

                <!-- Back Button -->
                <div class="mt-3">
                    <a href="{{ route('absensi.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        Kembali ke Beranda
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.qr-code-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 280px;
}

.token-info code {
    font-family: 'Courier New', monospace;
    font-size: 0.9em;
}

.card-custom {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.alert-warning {
    border-left: 4px solid #ffc107;
}

.btn-lg {
    padding: 12px 24px;
    font-size: 1.1em;
}
</style>
@endpush
