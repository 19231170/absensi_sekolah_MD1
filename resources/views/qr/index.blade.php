@extends('layouts.app')

@section('title', 'QR Code Siswa')

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
                    <a href="{{ route('absensi.scan') }}" class="btn btn-outline-light">
                        <i class="fas fa-camera me-1"></i>
                        Kembali ke Scan
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Petunjuk:</strong> Klik tombol "Lihat Detail" untuk melihat QR code besar atau "Download" untuk mengunduh QR code siswa.
                </div>

                <div class="row">
                    @foreach($siswa as $s)
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body text-center">
                                <h6 class="card-title text-primary">{{ $s->nama }}</h6>
                                <p class="card-text">
                                    <small class="text-muted">
                                        NIS: {{ $s->nis }}<br>
                                        {{ $s->kelas->nama_lengkap }}
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
                    @endforeach
                </div>

                @if($siswa->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Belum ada data siswa</h5>
                    <p class="text-muted">Data siswa akan muncul setelah ada yang terdaftar di sistem</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
