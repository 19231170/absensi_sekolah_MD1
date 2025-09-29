@extends('layouts.app')

@section('title', 'QR Code Siswa')

@push('styles')
<style>
    mark {
        background-color: #fff3cd;
        color: #856404;
        padding: 2px 4px;
        border-radius: 3px;
        font-weight: bold;
    }
    
    .search-info {
        background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
        border: none;
        border-left: 4px solid #2196f3;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-custom">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="fas fa-qrcode me-2"></i>
                    QR Code Siswa
                </h4>
                <div class="d-flex gap-2">
                    <div class="dropdown">
                        <button class="btn btn-success dropdown-toggle" type="button" id="dropdownDownloadAll" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-download me-1"></i>
                            Download Semua QR
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownDownloadAll">
                            <li>
                                <a class="dropdown-item" href="{{ route('qr.download.all.pdf') }}">
                                    <i class="fas fa-file-pdf me-2 text-danger"></i>
                                    Download PDF
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('qr.download.all') }}">
                                    <i class="fas fa-file-zipper me-2 text-warning"></i>
                                    Download ZIP/HTML
                                </a>
                            </li>
                        </ul>
                    </div>
                    <a href="{{ route('jadwal-kelas.index') }}" class="btn btn-outline-light">
                        <i class="fas fa-calendar-alt me-1"></i>
                        Lihat Jadwal
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Search Form -->
                <div class="row mb-4">
                    <div class="col-12">
                        <form method="GET" class="d-flex gap-2">
                            <div class="flex-grow-1">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control" 
                                           name="search" 
                                           value="{{ request('search') }}" 
                                           placeholder="Cari berdasarkan NIS atau Nama Siswa... (contoh: 00001001, Eko Prasetyo)">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i>
                                Cari
                            </button>
                            @if(request('search'))
                            <a href="{{ route('qr.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>
                                Reset
                            </a>
                            @endif
                        </form>
                    </div>
                </div>

                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Error:</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                @if(request('search'))
                <div class="alert search-info">
                    <i class="fas fa-search me-2"></i>
                    <strong>Hasil Pencarian:</strong> "{{ request('search') }}" - Ditemukan {{ $siswa->total() }} siswa
                </div>
                @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Petunjuk:</strong> Klik tombol "Lihat Detail" untuk melihat QR code besar atau "Download" untuk mengunduh QR code siswa. Total: {{ $siswa->total() }} siswa aktif.
                </div>
                @endif

                <div class="row">
                    @forelse($siswa as $s)
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body text-center">
                                <h6 class="card-title text-primary">
                                    @if(request('search'))
                                        {!! str_ireplace(request('search'), '<mark>' . request('search') . '</mark>', $s->nama) !!}
                                    @else
                                        {{ $s->nama }}
                                    @endif
                                </h6>
                                <p class="card-text">
                                    <small class="text-muted">
                                        NIS: 
                                        @if(request('search'))
                                            {!! str_ireplace(request('search'), '<mark>' . request('search') . '</mark>', $s->nis) !!}
                                        @else
                                            {{ $s->nis }}
                                        @endif
                                        <br>
                                        @if(request('search'))
                                            @php
                                                $kelasText = $s->kelas->tingkat . ' ' . $s->kelas->nama_kelas . ' - ' . $s->kelas->jurusan->nama_jurusan;
                                                $highlighted = str_ireplace(request('search'), '<mark>' . request('search') . '</mark>', $kelasText);
                                            @endphp
                                            {!! $highlighted !!}
                                        @else
                                            {{ $s->kelas->nama_lengkap }}
                                        @endif
                                    </small>
                                </p>
                                <div class="qr-preview mb-3">
                                    <img src="{{ route('qr.image', $s->nis) }}" 
                                         alt="QR Code {{ $s->nama }}" 
                                         class="img-fluid rounded shadow-sm"
                                         style="max-width: 120px;">
                                </div>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('qr.show', $s->nis) }}" class="btn btn-outline-primary btn-sm flex-fill">
                                        <i class="fas fa-eye"></i>
                                        Lihat Detail
                                    </a>
                                    <a href="{{ route('qr.download', $s->nis) }}" class="btn btn-success btn-sm flex-fill">
                                        <i class="fas fa-download"></i>
                                        Download
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                        @if(request('search'))
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Tidak ditemukan hasil pencarian</h5>
                                <p class="text-muted">Tidak ada siswa dengan kata kunci "<strong>{{ request('search') }}</strong>"</p>
                                <a href="{{ route('qr.index') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-arrow-left me-1"></i>
                                    Kembali ke Semua Data
                                </a>
                            </div>
                        </div>
                        @else
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Belum ada data siswa</h5>
                                <p class="text-muted">Data siswa akan muncul setelah ada yang terdaftar di sistem</p>
                            </div>
                        </div>
                        @endif
                    @endforelse
                </div>
                
                <!-- Pagination Links -->
                @if ($siswa->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted">
                            Menampilkan {{ $siswa->firstItem() }} - {{ $siswa->lastItem() }} 
                            dari {{ $siswa->total() }} hasil
                        </div>
                        {{ $siswa->links('pagination::bootstrap-4') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
