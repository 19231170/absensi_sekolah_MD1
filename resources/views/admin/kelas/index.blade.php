@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Daftar Kelas</h5>
                    <a href="{{ route('kelas.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Kelas
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

                    <!-- Search and Filter Form -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <form method="GET" class="row g-3">
                                <div class="col-md-4">
                                    <input type="text" class="form-control" name="search" 
                                           placeholder="Cari kelas atau tingkat..." 
                                           value="{{ request('search') }}">
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" name="jurusan_id">
                                        <option value="">-- Semua Jurusan --</option>
                                        @foreach($jurusanList as $jurusan)
                                            <option value="{{ $jurusan->id }}" {{ request('jurusan_id') == $jurusan->id ? 'selected' : '' }}>
                                                {{ $jurusan->nama_jurusan }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Cari
                                    </button>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('kelas.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Reset
                                    </a>
                                </div>
                                <div class="col-md-1">
                                    <span class="badge bg-info">{{ $kelas->total() }} kelas</span>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kelas</th>
                                    <th>Jurusan</th>
                                    <th>Kapasitas</th>
                                    <th>Jumlah Siswa</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($kelas as $index => $item)
                                <tr>
                                    <td>{{ $kelas->firstItem() + $index }}</td>
                                    <td>{{ $item->tingkat }} {{ $item->nama_kelas }}</td>
                                    <td>
                                        @if($item->jurusan)
                                        <span class="badge bg-primary">{{ $item->jurusan->kode_jurusan }}</span>
                                        {{ $item->jurusan->nama_jurusan }}
                                        @else
                                        <span class="text-muted">Tidak ada jurusan</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->kapasitas }}</td>
                                    <td>{{ $item->siswa->count() }} siswa</td>
                                    <td>
                                        @if($item->is_active)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-danger">Non-aktif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('kelas.show', ['kelas' => $item->id]) }}" class="btn btn-info">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
                                            @if($item->siswa->where('status_aktif', true)->where('qr_code', '!=', null)->count() > 0)
                                            <a href="{{ route('kelas.download.qr', $item->id) }}" class="btn btn-success" title="Download ZIP QR Codes">
                                                <i class="fas fa-download"></i> ZIP QR
                                            </a>
                                            @endif
                                            <a href="{{ route('kelas.edit', ['kelas' => $item->id]) }}" class="btn btn-warning">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            @if($item->siswa->count() == 0)
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
                                                        Apakah Anda yakin ingin menghapus kelas <strong>{{ $item->tingkat }} {{ $item->nama_kelas }}</strong>?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <form action="{{ route('kelas.destroy', ['kelas' => $item->id]) }}" method="POST">
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
                                    <td colspan="7" class="text-center">Tidak ada data kelas</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    @if($kelas->hasPages())
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <p class="text-muted mb-0">
                                    Menampilkan {{ $kelas->firstItem() }} hingga {{ $kelas->lastItem() }} 
                                    dari {{ $kelas->total() }} kelas
                                </p>
                            </div>
                            <div>
                                {{ $kelas->appends(request()->query())->links('pagination::bootstrap-4') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
