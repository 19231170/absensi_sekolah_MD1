@extends('layouts.app')

@section('title', 'Tambah Jadwal Persesi')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-custom">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="fas fa-plus me-2"></i>
                    Tambah Jadwal Persesi
                </h4>
                <a href="{{ route('jadwal-kelas.index') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-1"></i>
                    Kembali
                </a>
            </div>app')

@section('title', 'Tambah Jadwal Kelas')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-custom">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="fas fa-plus-circle me-2"></i>
                    Tambah Jadwal Kelas
                </h4>
                <a href="{{ route('jadwal-kelas.index') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-1"></i>
                    Kembali
                </a>
            </div>
            <div class="card-body">
                @if($errors->any())
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Terdapat kesalahan:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form action="{{ route('jadwal-kelas.store') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="kelas_id" class="form-label">Kelas <span class="text-danger">*</span></label>
                            <select class="form-select @error('kelas_id') is-invalid @enderror" 
                                    id="kelas_id" name="kelas_id" required>
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($kelas as $k)
                                    <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
                                        {{ $k->tingkat }} {{ $k->nama_kelas }} - {{ $k->jurusan->nama_jurusan }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kelas_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="hari" class="form-label">Hari <span class="text-danger">*</span></label>
                            <select class="form-select @error('hari') is-invalid @enderror" 
                                    id="hari" name="hari" required>
                                <option value="">-- Pilih Hari --</option>
                                @foreach($hariOptions as $value => $label)
                                    <option value="{{ $value }}" {{ old('hari') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('hari')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="jam_masuk" class="form-label">Jam Masuk <span class="text-danger">*</span></label>
                            <input type="time" 
                                   class="form-control @error('jam_masuk') is-invalid @enderror" 
                                   id="jam_masuk" name="jam_masuk" 
                                   value="{{ old('jam_masuk') }}" required>
                            @error('jam_masuk')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="jam_keluar" class="form-label">Jam Keluar <span class="text-danger">*</span></label>
                            <input type="time" 
                                   class="form-control @error('jam_keluar') is-invalid @enderror" 
                                   id="jam_keluar" name="jam_keluar" 
                                   value="{{ old('jam_keluar') }}" required>
                            @error('jam_keluar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="batas_telat" class="form-label">Batas Telat</label>
                            <input type="time" 
                                   class="form-control @error('batas_telat') is-invalid @enderror" 
                                   id="batas_telat" name="batas_telat" 
                                   value="{{ old('batas_telat') }}">
                            <small class="form-text text-muted">Kosongkan jika tidak ada batas telat</small>
                            @error('batas_telat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="mata_pelajaran" class="form-label">Mata Pelajaran</label>
                            <input type="text" 
                                   class="form-control @error('mata_pelajaran') is-invalid @enderror" 
                                   id="mata_pelajaran" name="mata_pelajaran" 
                                   value="{{ old('mata_pelajaran') }}"
                                   placeholder="Contoh: Pemrograman Web">
                            @error('mata_pelajaran')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="guru_pengampu" class="form-label">Guru Pengampu</label>
                            <input type="text" 
                                   class="form-control @error('guru_pengampu') is-invalid @enderror" 
                                   id="guru_pengampu" name="guru_pengampu" 
                                   value="{{ old('guru_pengampu') }}"
                                   placeholder="Contoh: Pak Budi">
                            @error('guru_pengampu')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <textarea class="form-control @error('keterangan') is-invalid @enderror" 
                                  id="keterangan" name="keterangan" rows="3"
                                  placeholder="Contoh: Lab Komputer 1, Ruang 201, dll.">{{ old('keterangan') }}</textarea>
                        @error('keterangan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('jadwal-kelas.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>
                            Batal
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-1"></i>
                            Simpan Jadwal Persesi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto set batas telat 15 menit setelah jam masuk
    const jamMasuk = document.getElementById('jam_masuk');
    const batasTelat = document.getElementById('batas_telat');
    
    jamMasuk.addEventListener('change', function() {
        if (this.value && !batasTelat.value) {
            const [hours, minutes] = this.value.split(':');
            const time = new Date();
            time.setHours(parseInt(hours), parseInt(minutes) + 15, 0, 0);
            
            const newHours = time.getHours().toString().padStart(2, '0');
            const newMinutes = time.getMinutes().toString().padStart(2, '0');
            
            batasTelat.value = `${newHours}:${newMinutes}`;
        }
    });
});
</script>
@endpush
@endsection
