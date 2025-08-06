@extends('layouts.app')

@section('title', 'Jadwal Sekolah')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card card-custom">
            <div class="card-header bg-info text-white text-center">
                <h4 class="mb-0">
                    <i class="fas fa-calendar-alt me-2"></i>
                    Jadwal Sekolah
                </h4>
                <small class="mt-1 d-block opacity-75">
                    <i class="fas fa-clock me-1"></i>
                    Hari {{ ucfirst($hariIni) }} - {{ Carbon\Carbon::now('Asia/Jakarta')->format('d/m/Y') }}
                </small>
            </div>
            <div class="card-body">
                
                <!-- Sesi Pagi -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="text-primary mb-3">
                            <i class="fas fa-sun me-2"></i>
                            Sesi Pagi
                        </h5>
                        <div class="row">
                            @foreach($jadwalPagi as $sesi)
                            <div class="col-md-4 mb-3">
                                <div class="card border-primary {{ in_array($hariIni, $sesi->hari_berlaku ?? []) ? 'bg-primary-subtle' : '' }}">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            @if(in_array($hariIni, $sesi->hari_berlaku ?? []))
                                                <i class="fas fa-check-circle text-success me-1"></i>
                                            @endif
                                            {{ $sesi->nama_sesi }}
                                        </h6>
                                        <div class="card-text">
                                            <div class="mb-2">
                                                <i class="fas fa-clock me-1"></i>
                                                <strong>Jam Masuk:</strong> {{ $sesi->jam_masuk }}
                                            </div>
                                            <div class="mb-2">
                                                <i class="fas fa-clock me-1"></i>
                                                <strong>Jam Keluar:</strong> {{ $sesi->jam_keluar }}
                                            </div>
                                            <div class="mb-2">
                                                <i class="fas fa-hourglass-half me-1"></i>
                                                <strong>Batas Telat:</strong> {{ $sesi->batas_telat }}
                                            </div>
                                            <div class="mb-2">
                                                <i class="fas fa-calendar-week me-1"></i>
                                                <strong>Hari:</strong> 
                                                @if($sesi->hari_berlaku)
                                                    {{ implode(', ', array_map('ucfirst', $sesi->hari_berlaku)) }}
                                                @else
                                                    Semua hari
                                                @endif
                                            </div>
                                        </div>
                                        @if(in_array($hariIni, $sesi->hari_berlaku ?? []))
                                            <div class="badge bg-success">
                                                <i class="fas fa-star me-1"></i>
                                                Berlaku Hari Ini
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Sesi Siang -->
                <div class="row">
                    <div class="col-12">
                        <h5 class="text-warning mb-3">
                            <i class="fas fa-cloud-sun me-2"></i>
                            Sesi Siang
                        </h5>
                        <div class="row">
                            @foreach($jadwalSiang as $sesi)
                            <div class="col-md-4 mb-3">
                                <div class="card border-warning {{ in_array($hariIni, $sesi->hari_berlaku ?? []) ? 'bg-warning-subtle' : '' }}">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            @if(in_array($hariIni, $sesi->hari_berlaku ?? []))
                                                <i class="fas fa-check-circle text-success me-1"></i>
                                            @endif
                                            {{ $sesi->nama_sesi }}
                                        </h6>
                                        <div class="card-text">
                                            <div class="mb-2">
                                                <i class="fas fa-clock me-1"></i>
                                                <strong>Jam Masuk:</strong> {{ $sesi->jam_masuk }}
                                            </div>
                                            <div class="mb-2">
                                                <i class="fas fa-clock me-1"></i>
                                                <strong>Jam Keluar:</strong> {{ $sesi->jam_keluar }}
                                            </div>
                                            <div class="mb-2">
                                                <i class="fas fa-hourglass-half me-1"></i>
                                                <strong>Batas Telat:</strong> {{ $sesi->batas_telat }}
                                            </div>
                                            <div class="mb-2">
                                                <i class="fas fa-calendar-week me-1"></i>
                                                <strong>Hari:</strong> 
                                                @if($sesi->hari_berlaku)
                                                    {{ implode(', ', array_map('ucfirst', $sesi->hari_berlaku)) }}
                                                @else
                                                    Semua hari
                                                @endif
                                            </div>
                                        </div>
                                        @if(in_array($hariIni, $sesi->hari_berlaku ?? []))
                                            <div class="badge bg-success">
                                                <i class="fas fa-star me-1"></i>
                                                Berlaku Hari Ini
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Quick Action -->
                <div class="text-center mt-4">
                    <a href="{{ route('absensi.index') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-qrcode me-2"></i>
                        Mulai Absensi
                    </a>
                    <div class="btn-group ms-2" role="group">
                        <button type="button" class="btn btn-success btn-lg dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-download me-2"></i>
                            Download QR Siswa
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('qr.index') }}">
                                <i class="fas fa-eye me-2"></i>Lihat Semua QR
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('qr.download.all.pdf') }}">
                                <i class="fas fa-file-pdf me-2 text-danger"></i>Download PDF
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('qr.download.all') }}">
                                <i class="fas fa-file-zipper me-2 text-warning"></i>Download ZIP/HTML
                            </a></li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.bg-primary-subtle {
    background-color: rgba(13, 110, 253, 0.1) !important;
    border-color: rgba(13, 110, 253, 0.3) !important;
}

.bg-warning-subtle {
    background-color: rgba(255, 193, 7, 0.1) !important;
    border-color: rgba(255, 193, 7, 0.3) !important;
}

.card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.badge {
    font-size: 0.75em;
}
</style>
@endpush
