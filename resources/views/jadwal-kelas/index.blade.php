@extends('layouts.app')

@section('title', 'Jadwal Persesi')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card card-custom">
            <div class="card-header bg-info text-white text-center">
                <h4 class="mb-0">
                    <i class="fas fa-calendar-alt me-2"></i>
                    Jadwal Persesi
                </h4>
                <small class="mt-1 d-block opacity-75">
                    <i class="fas fa-clock me-1"></i>
                    Hari {{ ucfirst($hariHariIni) }} - {{ Carbon\Carbon::now('Asia/Jakarta')->format('d/m/Y') }}
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

                <!-- Filter Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card bg-light">
                            <div class="card-body">
                                <form method="GET" class="row g-3">
                                    <div class="col-md-4">
                                        <label for="hari" class="form-label">
                                            <i class="fas fa-calendar me-1"></i>Hari:
                                        </label>
                                        <select class="form-select" id="hari" name="hari">
                                            <option value="">-- Semua Hari --</option>
                                            @foreach($hariOptions as $value => $label)
                                                <option value="{{ $value }}" {{ $hari == $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="kelas_id" class="form-label">
                                            <i class="fas fa-users me-1"></i>Kelas:
                                        </label>
                                        <select class="form-select" id="kelas_id" name="kelas_id">
                                            <option value="">-- Semua Kelas --</option>
                                            @foreach($kelas as $k)
                                                <option value="{{ $k->id }}" {{ $kelasId == $k->id ? 'selected' : '' }}>
                                                    {{ $k->tingkat }} {{ $k->nama_kelas }} - {{ $k->jurusan->nama_jurusan }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">&nbsp;</label>
                                        <div class="d-grid gap-2 d-md-flex">
                                            <button type="submit" class="btn btn-primary flex-fill">
                                                <i class="fas fa-search me-1"></i> Filter
                                            </button>
                                            <a href="{{ route('jadwal-kelas.create') }}" class="btn btn-success flex-fill">
                                                <i class="fas fa-plus me-1"></i> Tambah
                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                @if($jadwal->count() > 0)
                    <!-- Sesi Pagi -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-sun me-2"></i>
                                Sesi Pagi (07:00 - 12:00)
                            </h5>
                            <div class="row">
                                @foreach($jadwalPagi as $sesi)
                                <div class="col-md-4 mb-3">
                                    <div class="card border-primary {{ $sesi->is_active ? 'bg-primary-subtle' : 'bg-light' }}">
                                        <div class="card-body">
                                            <h6 class="card-title d-flex justify-content-between align-items-center">
                                                <span>
                                                    @if($sesi->is_active)
                                                        <i class="fas fa-check-circle text-success me-1"></i>
                                                    @else
                                                        <i class="fas fa-pause-circle text-secondary me-1"></i>
                                                    @endif
                                                    {{ $sesi->kelas->tingkat }} {{ $sesi->kelas->nama_kelas }}
                                                </span>
                                                <span class="badge bg-info">{{ $sesi->nama_hari }}</span>
                                            </h6>
                                            <div class="card-text">
                                                <div class="mb-2">
                                                    <i class="fas fa-clock me-1"></i>
                                                    <strong>Jam:</strong> {{ $sesi->jam_masuk_format }} - {{ $sesi->jam_keluar_format }}
                                                </div>
                                                @if($sesi->batas_telat)
                                                <div class="mb-2">
                                                    <i class="fas fa-hourglass-half me-1 text-warning"></i>
                                                    <strong>Batas Telat:</strong> {{ Carbon\Carbon::parse($sesi->batas_telat)->format('H:i') }}
                                                </div>
                                                @endif
                                                @if($sesi->mata_pelajaran)
                                                <div class="mb-2">
                                                    <i class="fas fa-book me-1"></i>
                                                    <strong>Mapel:</strong> {{ $sesi->mata_pelajaran }}
                                                </div>
                                                @endif
                                                @if($sesi->guru_pengampu)
                                                <div class="mb-2">
                                                    <i class="fas fa-chalkboard-teacher me-1"></i>
                                                    <strong>Guru:</strong> {{ $sesi->guru_pengampu }}
                                                </div>
                                                @endif
                                                <div class="mb-2">
                                                    <i class="fas fa-graduation-cap me-1"></i>
                                                    <strong>Jurusan:</strong> {{ $sesi->kelas->jurusan->nama_jurusan }}
                                                </div>
                                                <div class="mb-2">
                                                    <i class="fas fa-clock me-1"></i>
                                                    <strong>Durasi:</strong> {{ $sesi->durasi }}
                                                </div>
                                            </div>
                                            
                                            <!-- Action Buttons -->
                                            <div class="mt-3">
                                                <div class="btn-group w-100" role="group">
                                                    <a href="{{ route('jadwal-kelas.show', $sesi->id) }}" class="btn btn-sm btn-outline-info" title="Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('jadwal-kelas.edit', $sesi->id) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('jadwal-kelas.toggle-active', $sesi->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-sm {{ $sesi->is_active ? 'btn-outline-secondary' : 'btn-outline-success' }}" title="{{ $sesi->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                            <i class="fas {{ $sesi->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>

                                            @if($sesi->is_active)
                                                <div class="badge bg-success mt-2">
                                                    <i class="fas fa-star me-1"></i>
                                                    Jadwal Aktif
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
                                Sesi Siang (12:00 - 17:00)
                            </h5>
                            <div class="row">
                                @foreach($jadwalSiang as $sesi)
                                <div class="col-md-4 mb-3">
                                    <div class="card border-warning {{ $sesi->is_active ? 'bg-warning-subtle' : 'bg-light' }}">
                                        <div class="card-body">
                                            <h6 class="card-title d-flex justify-content-between align-items-center">
                                                <span>
                                                    @if($sesi->is_active)
                                                        <i class="fas fa-check-circle text-success me-1"></i>
                                                    @else
                                                        <i class="fas fa-pause-circle text-secondary me-1"></i>
                                                    @endif
                                                    {{ $sesi->kelas->tingkat }} {{ $sesi->kelas->nama_kelas }}
                                                </span>
                                                <span class="badge bg-info">{{ $sesi->nama_hari }}</span>
                                            </h6>
                                            <div class="card-text">
                                                <div class="mb-2">
                                                    <i class="fas fa-clock me-1"></i>
                                                    <strong>Jam:</strong> {{ $sesi->jam_masuk_format }} - {{ $sesi->jam_keluar_format }}
                                                </div>
                                                @if($sesi->batas_telat)
                                                <div class="mb-2">
                                                    <i class="fas fa-hourglass-half me-1 text-warning"></i>
                                                    <strong>Batas Telat:</strong> {{ Carbon\Carbon::parse($sesi->batas_telat)->format('H:i') }}
                                                </div>
                                                @endif
                                                @if($sesi->mata_pelajaran)
                                                <div class="mb-2">
                                                    <i class="fas fa-book me-1"></i>
                                                    <strong>Mapel:</strong> {{ $sesi->mata_pelajaran }}
                                                </div>
                                                @endif
                                                @if($sesi->guru_pengampu)
                                                <div class="mb-2">
                                                    <i class="fas fa-chalkboard-teacher me-1"></i>
                                                    <strong>Guru:</strong> {{ $sesi->guru_pengampu }}
                                                </div>
                                                @endif
                                                <div class="mb-2">
                                                    <i class="fas fa-graduation-cap me-1"></i>
                                                    <strong>Jurusan:</strong> {{ $sesi->kelas->jurusan->nama_jurusan }}
                                                </div>
                                                <div class="mb-2">
                                                    <i class="fas fa-clock me-1"></i>
                                                    <strong>Durasi:</strong> {{ $sesi->durasi }}
                                                </div>
                                            </div>
                                            
                                            <!-- Action Buttons -->
                                            <div class="mt-3">
                                                <div class="btn-group w-100" role="group">
                                                    <a href="{{ route('jadwal-kelas.show', $sesi->id) }}" class="btn btn-sm btn-outline-info" title="Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('jadwal-kelas.edit', $sesi->id) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('jadwal-kelas.toggle-active', $sesi->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-sm {{ $sesi->is_active ? 'btn-outline-secondary' : 'btn-outline-success' }}" title="{{ $sesi->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                            <i class="fas {{ $sesi->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>

                                            @if($sesi->is_active)
                                                <div class="badge bg-success mt-2">
                                                    <i class="fas fa-star me-1"></i>
                                                    Jadwal Aktif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Empty State -->
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">Belum Ada Jadwal Persesi</h5>
                        <p class="text-muted">Mulai dengan menambahkan jadwal persesi pertama untuk kelas lab.</p>
                        <a href="{{ route('jadwal-kelas.create') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-plus me-2"></i>
                            Tambah Jadwal Persesi Pertama
                        </a>
                    </div>
                @endif

                <!-- Quick Action -->
                @if($jadwal->count() > 0)
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
                @endif

                <!-- Statistik -->
                @if($jadwal->count() > 0)
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h4>{{ $jadwal->count() }}</h4>
                                <small>Total Jadwal</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h4>{{ $jadwal->where('is_active', true)->count() }}</h4>
                                <small>Jadwal Aktif</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h4>{{ $jadwal->groupBy('kelas_id')->count() }}</h4>
                                <small>Kelas Terjadwal</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-dark">
                            <div class="card-body text-center">
                                <h4>{{ $jadwal->groupBy('hari')->count() }}</h4>
                                <small>Hari Aktif</small>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
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

.btn-group .btn {
    border-radius: 0;
}

.btn-group .btn:first-child {
    border-top-left-radius: 0.375rem;
    border-bottom-left-radius: 0.375rem;
}

.btn-group .btn:last-child {
    border-top-right-radius: 0.375rem;
    border-bottom-right-radius: 0.375rem;
}
</style>
@endpush
