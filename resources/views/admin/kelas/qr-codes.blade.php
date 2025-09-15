@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">QR Codes Kelas: {{ $kelas->tingkat }} {{ $kelas->nama_kelas }}</h5>
                    <div>
                        <button onclick="window.print()" class="btn btn-primary btn-sm">
                            <i class="fas fa-print"></i> Print
                        </button>
                        <a href="{{ route('kelas.show', $kelas->id) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Petunjuk:</strong> Klik kanan pada QR code untuk menyimpan gambar, atau gunakan fitur print untuk mencetak semua QR code.
                    </div>

                    <div class="row">
                        @foreach($siswaList as $siswa)
                            @if($siswa->qr_code)
                            <div class="col-md-3 col-sm-4 col-6 mb-4">
                                <div class="card h-100 text-center">
                                    <div class="card-body">
                                        <canvas id="qr-{{ $siswa->nis }}" class="mb-2"></canvas>
                                        <h6 class="card-title mb-1">{{ $siswa->nama }}</h6>
                                        <p class="card-text text-muted small">NIS: {{ $siswa->nis }}</p>
                                        <a href="{{ route('kelas.siswa.qr', [$kelas->id, $siswa->nis]) }}" 
                                           class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>

                    @if($siswaList->where('qr_code', '!=', null)->count() == 0)
                        <div class="text-center py-5">
                            <i class="fas fa-qrcode fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Tidak ada QR Code</h5>
                            <p class="text-muted">Tidak ada siswa dengan QR code di kelas ini.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    @media print {
        .btn, .alert, .card-header .btn-group {
            display: none !important;
        }
        
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        
        .col-md-3 {
            width: 25% !important;
            float: left;
        }
        
        .card-body {
            padding: 10px !important;
        }
    }
    
    canvas {
        max-width: 100%;
        height: auto;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrious@4.0.2/dist/qrious.min.js"></script>
<script>
    $(document).ready(function() {
        // Generate QR codes for all students
        @foreach($siswaList as $siswa)
            @if($siswa->qr_code)
                new QRious({
                    element: document.getElementById('qr-{{ $siswa->nis }}'),
                    value: '{{ $siswa->qr_code }}',
                    size: 150,
                    background: 'white',
                    foreground: 'black',
                    level: 'M'
                });
            @endif
        @endforeach
    });
</script>
@endpush
