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
                                    <td>{{ $s->kelas->tingkat }} {{ $s->kelas->nama_kelas }}</td>
                                    <td>{{ $s->kelas->jurusan->nama_jurusan }}</td>
                                    <td>{{ $s->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $s->status_aktif ? 'success' : 'danger' }}">
                                            {{ $s->status_aktif ? 'Aktif' : 'Tidak Aktif' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('siswa.show', $s->nis) }}" class="btn btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('siswa.edit', $s->nis) }}" class="btn btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-danger" 
                                                    onclick="confirmDelete('{{ $s->nis }}', '{{ $s->nama }}')">
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
                Apakah Anda yakin ingin menghapus siswa <span id="siswa-name" class="fw-bold"></span>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirm-delete">Hapus</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function confirmDelete(nis, nama) {
        document.getElementById('siswa-name').textContent = nama;
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
        
        document.getElementById('confirm-delete').onclick = function() {
            document.getElementById('delete-form-' + nis).submit();
        }
    }
</script>
@endsection
