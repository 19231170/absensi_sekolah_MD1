@extends('layouts.app')

@section('title', 'Tambah Jadwal Persesi')

@section('styles')
<style>
    .form-control:disabled {
        background-color: #f8f9fa;
    }
</style>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-12">
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
                        <div class="col-lg-6 mb-3">
                            <label for="kelas_id" class="form-label">Kelas <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <select class="form-select @error('kelas_id') is-invalid @enderror" 
                                        id="kelas_id" name="kelas_id" required>
                                    <option value="">-- Pilih Kelas --</option>
                                    @foreach($kelas as $k)
                                        <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
                                            {{ $k->tingkat }} {{ $k->nama_kelas }} - {{ $k->jurusan->nama_jurusan }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalTambahKelas">
                                    <i class="fas fa-plus"></i> Tambah Kelas
                                </button>
                            </div>
                            @error('kelas_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-6 mb-3">
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
                        <div class="col-lg-4 mb-3">
                            <label for="jam_masuk" class="form-label">Jam Masuk <span class="text-danger">*</span></label>
                            <input type="time" 
                                   class="form-control @error('jam_masuk') is-invalid @enderror" 
                                   id="jam_masuk" name="jam_masuk" 
                                   value="{{ old('jam_masuk') }}" required>
                            @error('jam_masuk')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-4 mb-3">
                            <label for="jam_keluar" class="form-label">Jam Keluar <span class="text-danger">*</span></label>
                            <input type="time" 
                                   class="form-control @error('jam_keluar') is-invalid @enderror" 
                                   id="jam_keluar" name="jam_keluar" 
                                   value="{{ old('jam_keluar') }}" required>
                            @error('jam_keluar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-4 mb-3">
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
                        <div class="col-lg-6 mb-3">
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

                        <div class="col-lg-6 mb-3">
                            <label for="guru_id" class="form-label">Guru Pengampu</label>
                            <div class="input-group">
                                <select class="form-select" id="guru_id">
                                    <option value="">-- Pilih Guru --</option>
                                    @foreach($guru as $g)
                                        <option value="{{ $g->id }}" data-mapel="{{ $g->mata_pelajaran }}">{{ $g->name }} - {{ $g->nip ?? 'No NIP' }}</option>
                                    @endforeach
                                </select>
                                <a href="{{ route('guru.index') }}" class="btn btn-outline-primary" title="Kelola Guru" target="_blank">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            </div>
                            <small class="form-text text-muted">Pilih guru untuk mengisi data otomatis</small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-lg-12 mb-3">
                            <label for="guru_pengampu" class="form-label">Nama Guru Pengampu</label>
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

                    <div class="mb-4">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <textarea class="form-control @error('keterangan') is-invalid @enderror" 
                                  id="keterangan" name="keterangan" rows="4"
                                  placeholder="Contoh: Lab Komputer 1, Ruang 201, dll.">{{ old('keterangan') }}</textarea>
                        @error('keterangan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('jadwal-kelas.index') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times me-1"></i>
                            Batal
                        </a>
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-save me-1"></i>
                            Simpan Jadwal Persesi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Kelas -->
<div class="modal fade" id="modalTambahKelas" tabindex="-1" aria-labelledby="modalTambahKelasLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTambahKelasLabel">
                    <i class="fas fa-plus-circle me-2"></i>
                    Tambah Kelas Baru
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formTambahKelas">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="modal_jurusan_id" class="form-label">Jurusan <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <select class="form-select" id="modal_jurusan_id" name="jurusan_id" required>
                                    <option value="">-- Pilih Jurusan --</option>
                                    @if(isset($jurusan))
                                        @foreach($jurusan as $j)
                                            <option value="{{ $j->id }}">{{ $j->nama_jurusan }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#modalTambahJurusan" title="Tambah Jurusan Baru">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="modal_tingkat" class="form-label">Tingkat <span class="text-danger">*</span></label>
                            <select class="form-select" id="modal_tingkat" name="tingkat" required>
                                <option value="">-- Pilih Tingkat --</option>
                                <option value="X">X (10)</option>
                                <option value="XI">XI (11)</option>
                                <option value="XII">XII (12)</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="modal_nama_kelas" class="form-label">Nama Kelas <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="modal_nama_kelas" name="nama_kelas" 
                                   placeholder="Contoh: TKJ 1, RPL A, MM 2" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="modal_kapasitas" class="form-label">Kapasitas Siswa</label>
                            <input type="number" class="form-control" id="modal_kapasitas" name="kapasitas" 
                                   placeholder="Contoh: 30" min="1" max="50">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="modal_keterangan_kelas" class="form-label">Keterangan</label>
                        <textarea class="form-control" id="modal_keterangan_kelas" name="keterangan" rows="3"
                                  placeholder="Keterangan tambahan tentang kelas..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    Batal
                </button>
                <button type="button" class="btn btn-primary" id="btnSimpanKelas">
                    <i class="fas fa-save me-1"></i>
                    Simpan Kelas
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Jurusan -->
<div class="modal fade" id="modalTambahJurusan" tabindex="-1" aria-labelledby="modalTambahJurusanLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalTambahJurusanLabel">
                    <i class="fas fa-graduation-cap me-2"></i>
                    Tambah Jurusan Baru
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formTambahJurusan">
                    @csrf
                    <div class="mb-3">
                        <label for="modal_nama_jurusan" class="form-label">Nama Jurusan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="modal_nama_jurusan" name="nama_jurusan" 
                               placeholder="Contoh: Teknik Komputer dan Jaringan" required>
                        <small class="form-text text-muted">Masukkan nama jurusan lengkap</small>
                    </div>

                    <div class="mb-3">
                        <label for="modal_kode_jurusan" class="form-label">Kode Jurusan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="modal_kode_jurusan" name="kode_jurusan" 
                               placeholder="Contoh: TKJ" maxlength="10" required>
                        <small class="form-text text-muted">Kode singkat untuk identifikasi jurusan</small>
                    </div>

                    <div class="mb-3">
                        <label for="modal_deskripsi_jurusan" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="modal_deskripsi_jurusan" name="deskripsi" rows="3"
                                  placeholder="Deskripsi atau informasi tambahan tentang jurusan..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    Batal
                </button>
                <button type="button" class="btn btn-success" id="btnSimpanJurusan">
                    <i class="fas fa-save me-1"></i>
                    Simpan Jurusan
                </button>
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

    // Handle form tambah kelas
    const btnSimpanKelas = document.getElementById('btnSimpanKelas');
    const formTambahKelas = document.getElementById('formTambahKelas');
    const modalTambahKelas = new bootstrap.Modal(document.getElementById('modalTambahKelas'));

    // Handle form tambah jurusan
    const btnSimpanJurusan = document.getElementById('btnSimpanJurusan');
    const formTambahJurusan = document.getElementById('formTambahJurusan');
    const modalTambahJurusan = new bootstrap.Modal(document.getElementById('modalTambahJurusan'));

    btnSimpanJurusan.addEventListener('click', function() {
        const formData = new FormData(formTambahJurusan);
        
        // Disable button dan show loading
        btnSimpanJurusan.disabled = true;
        btnSimpanJurusan.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Menyimpan...';

        // AJAX request to save jurusan
        fetch("{{ route('jurusan.store') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Add new option to both jurusan selects (main form and modal kelas)
                const jurusanSelects = document.querySelectorAll('#modal_jurusan_id');
                jurusanSelects.forEach(select => {
                    const newOption = new Option(data.jurusan.nama_jurusan, data.jurusan.id, true, true);
                    select.add(newOption);
                });
                
                // Close modal and reset form
                modalTambahJurusan.hide();
                formTambahJurusan.reset();
                
                // Show success message
                showAlert('success', 'Jurusan berhasil ditambahkan!');
            } else {
                showAlert('danger', data.message || 'Gagal menambahkan jurusan!');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'Terjadi kesalahan saat menambahkan jurusan!');
        })
        .finally(() => {
            // Reset button
            btnSimpanJurusan.disabled = false;
            btnSimpanJurusan.innerHTML = '<i class="fas fa-save me-1"></i>Simpan Jurusan';
        });
    });

    btnSimpanKelas.addEventListener('click', function() {
        const formData = new FormData(formTambahKelas);
        
        // Disable button dan show loading
        btnSimpanKelas.disabled = true;
        btnSimpanKelas.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Menyimpan...';

        // AJAX request to save kelas
        fetch("{{ route('kelas.store') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Add new option to select
                const kelasSelect = document.getElementById('kelas_id');
                const newOption = new Option(data.kelas.display_name, data.kelas.id, true, true);
                kelasSelect.add(newOption);
                
                // Close modal and reset form
                modalTambahKelas.hide();
                formTambahKelas.reset();
                
                // Show success message
                showAlert('success', 'Kelas berhasil ditambahkan!');
            } else {
                showAlert('danger', data.message || 'Gagal menambahkan kelas!');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'Terjadi kesalahan saat menambahkan kelas!');
        })
        .finally(() => {
            // Reset button
            btnSimpanKelas.disabled = false;
            btnSimpanKelas.innerHTML = '<i class="fas fa-save me-1"></i>Simpan Kelas';
        });
    });

    // Function to show alert
    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const cardBody = document.querySelector('.card-body');
        cardBody.insertBefore(alertDiv, cardBody.firstChild);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
});
</script>
@endpush

@push('styles')
<style>
/* Custom styles for wider layout */
.card-custom {
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    border-radius: 12px;
}

.form-control, .form-select {
    border-radius: 8px;
    border: 1px solid #ddd;
    padding: 10px 15px;
}

.form-control:focus, .form-select:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

.btn-lg {
    padding: 12px 30px;
    font-size: 1.1em;
    border-radius: 8px;
}

.input-group .btn {
    border-radius: 0 8px 8px 0;
}

.modal-content {
    border-radius: 12px;
    border: none;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.modal-header {
    border-radius: 12px 12px 0 0;
    border-bottom: 1px solid #e9ecef;
}

.alert {
    border-radius: 8px;
    border: none;
    padding: 15px 20px;
}

/* Responsive improvements */
@media (max-width: 768px) {
    .col-12 {
        padding: 0 10px;
    }
    
    .card-custom {
        margin: 10px 0;
    }
    
    .btn-lg {
        padding: 10px 20px;
        font-size: 1em;
    }
}
</style>
@endpush

@push('scripts')
<script>
    // Guru selection for auto-fill
    document.getElementById('guru_id').addEventListener('change', function() {
        const guruId = this.value;
        const guruPengampuField = document.getElementById('guru_pengampu');
        const mataPelajaranField = document.getElementById('mata_pelajaran');
        
        if (!guruId) {
            guruPengampuField.value = '';
            return;
        }
        
        // Get selected option
        const selectedOption = this.options[this.selectedIndex];
        const selectedGuruName = selectedOption.textContent.split(' - ')[0];
        
        // Set guru_pengampu field
        guruPengampuField.value = selectedGuruName;
        
        // Get mata_pelajaran from data attribute
        const mapel = selectedOption.getAttribute('data-mapel');
        if (mapel) {
            mataPelajaranField.value = mapel;
        }
        
        // Optional: Get more detailed data from server
        fetch('{{ url("jadwal-kelas/guru") }}/' + guruId)
            .then(response => response.json())
            .then(data => {
                if (data.mata_pelajaran) {
                    mataPelajaranField.value = data.mata_pelajaran;
                }
            })
            .catch(error => console.error('Error fetching guru data:', error));
    });
</script>
@endpush
@endsection
