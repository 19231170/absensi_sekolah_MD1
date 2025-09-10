@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Tambah Kelas Baru</h5>
                    <a href="{{ route('kelas.index') }}" class="btn btn-secondary btn-sm">
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

                    <form action="{{ route('kelas.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="jurusan_id" class="form-label">Jurusan <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <select class="form-select @error('jurusan_id') is-invalid @enderror" id="jurusan_id" name="jurusan_id" required>
                                    <option value="">-- Pilih Jurusan --</option>
                                    @foreach($jurusans as $jurusan)
                                        <option value="{{ $jurusan->id }}" {{ old('jurusan_id') == $jurusan->id ? 'selected' : '' }}>
                                            {{ $jurusan->kode_jurusan }} - {{ $jurusan->nama_jurusan }}
                                        </option>
                                    @endforeach
                                </select>
                                <a href="#" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#tambahJurusanModal">
                                    <i class="fas fa-plus"></i>
                                </a>
                            </div>
                            @error('jurusan_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="tingkat" class="form-label">Tingkat <span class="text-danger">*</span></label>
                            <select class="form-select @error('tingkat') is-invalid @enderror" id="tingkat" name="tingkat" required>
                                <option value="">-- Pilih Tingkat --</option>
                                @foreach($tingkatOptions as $tingkat)
                                    <option value="{{ $tingkat }}" {{ old('tingkat') == $tingkat ? 'selected' : '' }}>
                                        {{ $tingkat }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tingkat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="nama_kelas" class="form-label">Nama Kelas <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama_kelas') is-invalid @enderror" id="nama_kelas" 
                                   name="nama_kelas" value="{{ old('nama_kelas') }}" required>
                            <div class="form-text">Contoh: A, B, RPL 1, IPA 2, dll</div>
                            @error('nama_kelas')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="kapasitas" class="form-label">Kapasitas</label>
                            <input type="number" class="form-control @error('kapasitas') is-invalid @enderror" id="kapasitas" 
                                   name="kapasitas" value="{{ old('kapasitas', 40) }}" min="1" max="50">
                            <div class="form-text">Kapasitas maksimal siswa dalam kelas (default: 40)</div>
                            @error('kapasitas')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="keterangan" class="form-label">Keterangan</label>
                            <textarea class="form-control @error('keterangan') is-invalid @enderror" id="keterangan" 
                                      name="keterangan" rows="3">{{ old('keterangan') }}</textarea>
                            @error('keterangan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" 
                                   {{ old('is_active') ? 'checked' : '' }} checked>
                            <label class="form-check-label" for="is_active">Kelas Aktif</label>
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

<!-- Modal Tambah Jurusan -->
<div class="modal fade" id="tambahJurusanModal" tabindex="-1" aria-labelledby="tambahJurusanModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tambahJurusanModalLabel">Tambah Jurusan Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formJurusanBaru">
                    <div class="mb-3">
                        <label for="kode_jurusan_modal" class="form-label">Kode Jurusan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="kode_jurusan_modal" name="kode_jurusan_modal" maxlength="10" required>
                        <div class="form-text">Contoh: TKJ, RPL, MM (Maksimal 10 karakter)</div>
                    </div>
                    <div class="mb-3">
                        <label for="nama_jurusan_modal" class="form-label">Nama Jurusan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_jurusan_modal" name="nama_jurusan_modal" required>
                        <div class="form-text">Contoh: Teknik Komputer dan Jaringan</div>
                    </div>
                    <div class="mb-3">
                        <label for="deskripsi_modal" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi_modal" name="deskripsi_modal" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnSimpanJurusan">Simpan</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Submit Jurusan baru via AJAX
        $('#btnSimpanJurusan').click(function() {
            var kodeJurusan = $('#kode_jurusan_modal').val();
            var namaJurusan = $('#nama_jurusan_modal').val();
            var deskripsi = $('#deskripsi_modal').val();
            
            if (!kodeJurusan || !namaJurusan) {
                alert('Kode dan Nama Jurusan harus diisi!');
                return;
            }
            
            $.ajax({
                url: '{{ route("jurusan.store") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    kode_jurusan: kodeJurusan,
                    nama_jurusan: namaJurusan,
                    deskripsi: deskripsi
                },
                success: function(response) {
                    if (response.success) {
                        // Add new option to select
                        $('#jurusan_id').append(new Option(
                            response.jurusan.kode_jurusan + ' - ' + response.jurusan.nama_jurusan, 
                            response.jurusan.id, 
                            true, 
                            true
                        ));
                        
                        // Close modal and reset form
                        $('#tambahJurusanModal').modal('hide');
                        $('#formJurusanBaru')[0].reset();
                        
                        // Show success alert
                        alert('Jurusan berhasil ditambahkan!');
                    } else {
                        alert('Gagal menambahkan jurusan: ' + response.message);
                    }
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors;
                    var errorMsg = '';
                    
                    for (var key in errors) {
                        errorMsg += errors[key][0] + '\n';
                    }
                    
                    alert('Gagal menambahkan jurusan:\n' + errorMsg);
                }
            });
        });
    });
</script>
@endpush
