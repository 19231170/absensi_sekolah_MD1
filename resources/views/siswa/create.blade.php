@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Tambah Siswa Baru</h5>
                    <a href="{{ route('siswa.index') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('siswa.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="nis" class="form-label">NIS <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nis') is-invalid @enderror" 
                                   id="nis" name="nis" value="{{ old('nis') }}" required>
                            @error('nis')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama') is-invalid @enderror" 
                                   id="nama" name="nama" value="{{ old('nama') }}" required>
                            @error('nama')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="kelas_id" class="form-label">Kelas <span class="text-danger">*</span></label>
                            <select class="form-select @error('kelas_id') is-invalid @enderror" 
                                    id="kelas_id" name="kelas_id" required>
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($kelas->sortBy('jurusan.nama_jurusan')->sortBy('tingkat')->sortBy('nama_kelas') as $k)
                                    <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
                                        {{ $k->tingkat }} {{ $k->nama_kelas }} - {{ $k->jurusan->nama_jurusan }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kelas_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                            <div class="d-flex">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="jenis_kelamin" 
                                           id="jenis_kelamin_l" value="L" {{ old('jenis_kelamin', 'L') == 'L' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="jenis_kelamin_l">
                                        Laki-laki
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="jenis_kelamin" 
                                           id="jenis_kelamin_p" value="P" {{ old('jenis_kelamin') == 'P' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="jenis_kelamin_p">
                                        Perempuan
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                            <input type="date" class="form-control @error('tanggal_lahir') is-invalid @enderror" 
                                   id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}">
                            @error('tanggal_lahir')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat</label>
                            <textarea class="form-control @error('alamat') is-invalid @enderror" 
                                      id="alamat" name="alamat" rows="3">{{ old('alamat') }}</textarea>
                            @error('alamat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="nomor_hp" class="form-label">Nomor HP</label>
                            <input type="text" class="form-control @error('nomor_hp') is-invalid @enderror" 
                                   id="nomor_hp" name="nomor_hp" value="{{ old('nomor_hp') }}">
                            @error('nomor_hp')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="status_aktif" class="form-label">Status Siswa <span class="text-danger">*</span></label>
                            <select class="form-select @error('status_aktif') is-invalid @enderror" 
                                    id="status_aktif" name="status_aktif" required>
                                <option value="">-- Pilih Status --</option>
                                <option value="1" {{ old('status_aktif', '1') == '1' ? 'selected' : '' }}>
                                    <i class="fas fa-check-circle"></i> Aktif
                                </option>
                                <option value="0" {{ old('status_aktif') == '0' ? 'selected' : '' }}>
                                    <i class="fas fa-times-circle"></i> Tidak Aktif
                                </option>
                            </select>
                            @error('status_aktif')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
