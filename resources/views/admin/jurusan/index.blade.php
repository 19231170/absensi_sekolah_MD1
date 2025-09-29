@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Daftar Jurusan</h5>
                    <a href="{{ route('jurusan.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Jurusan
                    </a>
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

                    <!-- Form Pencarian dan Filter -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <form method="GET" action="{{ route('jurusan.index') }}" class="row g-3">
                                <div class="col-md-4">
                                    <input type="text" 
                                           class="form-control" 
                                           name="search" 
                                           placeholder="Cari nama atau kode jurusan..." 
                                           value="{{ request('search') }}">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Cari
                                    </button>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('jurusan.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-refresh"></i> Reset
                                    </a>
                                </div>
                                <div class="col-md-4 text-end">
                                    <small class="text-muted">
                                        Menampilkan {{ $jurusan->firstItem() ?? 0 }} - {{ $jurusan->lastItem() ?? 0 }} 
                                        dari {{ $jurusan->total() }} data
                                    </small>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kode</th>
                                    <th>Nama Jurusan</th>
                                    <th>Jumlah Kelas</th>
                                    <th>Jumlah Siswa</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($jurusan as $index => $item)
                                <tr>
                                    <td>{{ ($jurusan->currentPage() - 1) * $jurusan->perPage() + $loop->iteration }}</td>
                                    <td><span class="badge bg-primary">{{ $item->kode_jurusan }}</span></td>
                                    <td>{{ $item->nama_jurusan }}</td>
                                    <td>{{ $item->kelas_count }}</td>
                                    <td>{{ $item->siswa_count }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('jurusan.show', ['jurusan' => $item->id]) }}" class="btn btn-info">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
                                            <a href="{{ route('jurusan.edit', ['jurusan' => $item->id]) }}" class="btn btn-warning">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            @if($item->kelas_count == 0)
                                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $item->id }}">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                            @endif
                                        </div>
                                        
                                        <!-- Modal Hapus -->
                                        <div class="modal fade" id="deleteModal{{ $item->id }}" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        Apakah Anda yakin ingin menghapus jurusan <strong>{{ $item->nama_jurusan }}</strong>?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <form action="{{ route('jurusan.destroy', ['jurusan' => $item->id]) }}" method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">
                                        @if(request('search'))
                                            Tidak ada data jurusan yang cocok dengan pencarian "{{ request('search') }}"
                                        @else
                                            Tidak ada data jurusan
                                        @endif
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination Links -->
                    @if ($jurusan->hasPages())
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Menampilkan {{ $jurusan->firstItem() }} - {{ $jurusan->lastItem() }} 
                                dari {{ $jurusan->total() }} hasil
                            </div>
                            {{ $jurusan->links('pagination::bootstrap-4') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
