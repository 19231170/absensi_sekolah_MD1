@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Data Siswa</h5>
                    <div>
                        <a href="{{ route('siswa.create') }}" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Tambah Siswa</a>
                        <a href="{{ route('siswa.import') }}" class="btn btn-sm btn-success"><i class="fas fa-file-excel"></i> Import Excel</a>
                        <a href="{{ route('siswa.template.download') }}" class="btn btn-sm btn-info"><i class="fas fa-download"></i> Download Template</a>
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>NIS</th>
                                    <th>Nama</th>
                                    <th>Kelas</th>
                                    <th>Jurusan</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Status</th>
                                    <th width="150">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($siswa as $s)
                                <tr>
                                    <td>{{ $s->nis }}</td>
                                    <td>{{ $s->nama }}</td>
                                    <td>
                                        @if($s->kelas)
                                            {{ $s->kelas->tingkat }} {{ $s->kelas->nama_kelas }}
                                        @else
                                            <span class="text-muted">Belum ada kelas</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($s->kelas && $s->kelas->jurusan)
                                            {{ $s->kelas->jurusan->nama_jurusan }}
                                        @else
                                            <span class="text-muted">Belum ada jurusan</span>
                                        @endif
                                    </td>
                                    <td>{{ $s->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $s->status_aktif ? 'success' : 'danger' }}">
                                            {{ $s->status_aktif ? 'Aktif' : 'Tidak Aktif' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('siswa.show', $s->nis) }}" class="btn btn-info" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('siswa.edit', $s->nis) }}" class="btn btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-danger" title="Hapus"
                                                    onclick="confirmDelete('{{ $s->nis }}', '{{ addslashes($s->nama) }}')"
                                                    data-nis="{{ $s->nis }}" 
                                                    data-nama="{{ addslashes($s->nama) }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                        <form id="delete-form-{{ $s->nis }}" 
                                              action="{{ route('siswa.destroy', $s->nis) }}" 
                                              method="POST" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">Tidak ada data siswa</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus siswa <span id="siswa-name" class="fw-bold text-danger"></span>?</p>
                <p class="text-muted small">Data siswa yang dihapus tidak dapat dikembalikan.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirm-delete">
                    <i class="fas fa-trash"></i> Hapus
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentDeleteNis = null;
    
    function confirmDelete(nis, nama) {
        console.log('confirmDelete called with NIS:', nis, 'Nama:', nama);
        
        // Try to show Bootstrap modal first
        try {
            // Store the NIS for later use
            currentDeleteNis = nis;
            
            // Set the name in the modal
            const siswaNameElement = document.getElementById('siswa-name');
            if (siswaNameElement) {
                siswaNameElement.textContent = nama;
            }
            
            // Show the modal
            const deleteModal = document.getElementById('deleteModal');
            if (deleteModal && typeof bootstrap !== 'undefined') {
                const modal = new bootstrap.Modal(deleteModal);
                modal.show();
                console.log('Bootstrap modal shown successfully');
                return; // Exit function if modal works
            } else {
                throw new Error('Bootstrap modal not available');
            }
        } catch (error) {
            console.warn('Bootstrap modal failed:', error);
            // Fallback to simple confirm dialog
            directDelete(nis, nama);
        }
    }
    
    // Handle confirm delete button click
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM Content Loaded - Setting up delete handlers');
        
        const confirmDeleteBtn = document.getElementById('confirm-delete');
        if (confirmDeleteBtn) {
            confirmDeleteBtn.addEventListener('click', function() {
                console.log('Confirm delete button clicked, NIS:', currentDeleteNis);
                if (currentDeleteNis) {
                    const form = document.getElementById('delete-form-' + currentDeleteNis);
                    if (form) {
                        console.log('Submitting delete form for NIS:', currentDeleteNis);
                        
                        // Hide modal first
                        const deleteModal = document.getElementById('deleteModal');
                        if (deleteModal && typeof bootstrap !== 'undefined') {
                            const modal = bootstrap.Modal.getInstance(deleteModal);
                            if (modal) {
                                modal.hide();
                            }
                        }
                        
                        // Submit form
                        form.submit();
                    } else {
                        console.error('Delete form not found for NIS:', currentDeleteNis);
                        alert('Form tidak ditemukan. Silakan refresh halaman dan coba lagi.');
                    }
                } else {
                    console.error('No NIS selected for deletion');
                    alert('Tidak ada siswa yang dipilih untuk dihapus.');
                }
            });
        } else {
            console.warn('Confirm delete button not found');
        }
        
        // Close modal event listener
        const deleteModal = document.getElementById('deleteModal');
        if (deleteModal) {
            deleteModal.addEventListener('hidden.bs.modal', function() {
                console.log('Modal hidden, clearing currentDeleteNis');
                currentDeleteNis = null;
            });
        }
        
        // Check if all required elements exist
        console.log('Elements check:');
        console.log('- deleteModal:', !!document.getElementById('deleteModal'));
        console.log('- confirmDeleteBtn:', !!document.getElementById('confirm-delete'));
        console.log('- siswa-name:', !!document.getElementById('siswa-name'));
        console.log('- bootstrap available:', typeof bootstrap !== 'undefined');
    });
    
    // Alternative fallback using direct confirmation
    function directDelete(nis, nama) {
        console.log('Using direct delete for NIS:', nis, 'Nama:', nama);
        
        if (confirm('Apakah Anda yakin ingin menghapus siswa "' + nama + '"?\n\nData siswa yang dihapus tidak dapat dikembalikan.')) {
            const form = document.getElementById('delete-form-' + nis);
            if (form) {
                console.log('Submitting delete form directly for NIS:', nis);
                form.submit();
            } else {
                console.error('Delete form not found for NIS:', nis);
                alert('Form tidak ditemukan. Silakan refresh halaman dan coba lagi.');
            }
        } else {
            console.log('Delete cancelled by user');
        }
    }
    
    // Add error handling for any unhandled errors
    window.addEventListener('error', function(e) {
        console.error('JavaScript error:', e.error);
    });
</script>
@endpush
