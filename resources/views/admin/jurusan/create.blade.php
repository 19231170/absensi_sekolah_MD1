@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Tambah Jurusan Baru</h5>
                    <a href="{{ route('jurusan.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('jurusan.storeForm') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="kode_jurusan" class="form-label">Kode Jurusan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('kode_jurusan') is-invalid @enderror" id="kode_jurusan" 
                                   name="kode_jurusan" value="{{ old('kode_jurusan') }}" maxlength="10" required>
                            <div class="form-text">Contoh: TKJ, RPL, MM (Maksimal 10 karakter)</div>
                            @error('kode_jurusan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="nama_jurusan" class="form-label">Nama Jurusan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama_jurusan') is-invalid @enderror" id="nama_jurusan" 
                                   name="nama_jurusan" value="{{ old('nama_jurusan') }}" required>
                            <div class="form-text">Contoh: Teknik Komputer dan Jaringan</div>
                            @error('nama_jurusan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" 
                                      name="deskripsi" rows="3">{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
