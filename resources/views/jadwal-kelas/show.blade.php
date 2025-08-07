@extends('layouts.app')

@section('title', 'Detail Jadwal Persesi')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-custom">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="fas fa-eye me-2"></i>
                    Detail Jadwal Persesi
                </h4>
                <div>
                    <a href="{{ route('jadwal-kelas.edit', $jadwalKelas->id) }}" class="btn btn-warning btn-sm me-2">
                        <i class="fas fa-edit me-1"></i>
                        Edit
                    </a>
                    <a href="{{ route('jadwal-kelas.index') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>
                        Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Status Badge -->
                <div class="mb-4">
                    @if($jadwalKelas->is_active)
                        <span class="badge bg-success fs-6">
                            <i class="fas fa-check-circle me-1"></i>
                            Status: Aktif
                        </span>
                    @else
                        <span class="badge bg-danger fs-6">
                            <i class="fas fa-times-circle me-1"></i>
                            Status: Tidak Aktif
                        </span>
                    @endif
                </div>

                <!-- Informasi Kelas -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="info-item">
                            <i class="fas fa-users text-primary me-2"></i>
                            <strong>Kelas:</strong>
                            <div class="ms-4">
                                {{ $jadwalKelas->kelas->tingkat }} {{ $jadwalKelas->kelas->nama_kelas }} - 
                                {{ $jadwalKelas->kelas->jurusan->nama_jurusan }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item">
                            <i class="fas fa-calendar-alt text-warning me-2"></i>
                            <strong>Hari:</strong>
                            <div class="ms-4">{{ $jadwalKelas->nama_hari }}</div>
                        </div>
                    </div>
                </div>

                <!-- Informasi Waktu -->
                <div class="card bg-light mb-4">
                    <div class="card-body">
                        <h6 class="card-title text-primary">
                            <i class="fas fa-clock me-2"></i>
                            Informasi Waktu
                        </h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="info-item">
                                    <strong>Jam Masuk:</strong>
                                    <div class="text-success fs-5 fw-bold">
                                        {{ Carbon\Carbon::parse($jadwalKelas->jam_masuk)->format('H:i') }} WIB
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-item">
                                    <strong>Jam Keluar:</strong>
                                    <div class="text-danger fs-5 fw-bold">
                                        {{ Carbon\Carbon::parse($jadwalKelas->jam_keluar)->format('H:i') }} WIB
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-item">
                                    <strong>Durasi:</strong>
                                    <div class="text-info fs-5 fw-bold">
                                        {{ $jadwalKelas->durasi }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        @if($jadwalKelas->batas_telat)
                        <div class="mt-3 pt-3 border-top">
                            <div class="info-item">
                                <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                <strong>Batas Telat:</strong>
                                <span class="text-warning fw-bold">
                                    {{ Carbon\Carbon::parse($jadwalKelas->batas_telat)->format('H:i') }} WIB
                                </span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Informasi Akademik -->
                @if($jadwalKelas->mata_pelajaran || $jadwalKelas->guru_pengampu)
                <div class="card bg-light mb-4">
                    <div class="card-body">
                        <h6 class="card-title text-success">
                            <i class="fas fa-book me-2"></i>
                            Informasi Akademik
                        </h6>
                        <div class="row">
                            @if($jadwalKelas->mata_pelajaran)
                            <div class="col-md-6">
                                <div class="info-item">
                                    <i class="fas fa-book-open text-primary me-2"></i>
                                    <strong>Mata Pelajaran:</strong>
                                    <div class="ms-4">{{ $jadwalKelas->mata_pelajaran }}</div>
                                </div>
                            </div>
                            @endif
                            
                            @if($jadwalKelas->guru_pengampu)
                            <div class="col-md-6">
                                <div class="info-item">
                                    <i class="fas fa-chalkboard-teacher text-success me-2"></i>
                                    <strong>Guru Pengampu:</strong>
                                    <div class="ms-4">{{ $jadwalKelas->guru_pengampu }}</div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                <!-- Keterangan -->
                @if($jadwalKelas->keterangan)
                <div class="card bg-light mb-4">
                    <div class="card-body">
                        <h6 class="card-title text-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Keterangan
                        </h6>
                        <p class="mb-0">{{ $jadwalKelas->keterangan }}</p>
                    </div>
                </div>
                @endif

                <!-- Informasi Sistem -->
                <div class="card bg-secondary bg-opacity-10 border-0">
                    <div class="card-body">
                        <h6 class="card-title text-secondary">
                            <i class="fas fa-database me-2"></i>
                            Informasi Sistem
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <small class="text-muted">
                                        <strong>Dibuat:</strong> 
                                        {{ $jadwalKelas->created_at->format('d/m/Y H:i') }} WIB
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <small class="text-muted">
                                        <strong>Terakhir Diupdate:</strong> 
                                        {{ $jadwalKelas->updated_at->format('d/m/Y H:i') }} WIB
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                    <div>
                        <form action="{{ route('jadwal-kelas.toggle-active', $jadwalKelas->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            @if($jadwalKelas->is_active)
                                <button type="submit" class="btn btn-outline-danger btn-sm" 
                                        onclick="return confirm('Yakin ingin menonaktifkan jadwal persesi ini?')">
                                    <i class="fas fa-toggle-off me-1"></i>
                                    Nonaktifkan
                                </button>
                            @else
                                <button type="submit" class="btn btn-outline-success btn-sm" 
                                        onclick="return confirm('Yakin ingin mengaktifkan jadwal persesi ini?')">
                                    <i class="fas fa-toggle-on me-1"></i>
                                    Aktifkan
                                </button>
                            @endif
                        </form>
                    </div>
                    
                    <div>
                        <a href="{{ route('jadwal-kelas.edit', $jadwalKelas->id) }}" class="btn btn-warning btn-sm me-2">
                            <i class="fas fa-edit me-1"></i>
                            Edit Jadwal Persesi
                        </a>
                        <form action="{{ route('jadwal-kelas.destroy', $jadwalKelas->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm" 
                                    onclick="return confirm('Yakin ingin menghapus jadwal persesi ini? Data tidak dapat dikembalikan!')">
                                <i class="fas fa-trash me-1"></i>
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.info-item {
    margin-bottom: 1rem;
}

.info-item:last-child {
    margin-bottom: 0;
}

.card-custom {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}
</style>
@endsection
