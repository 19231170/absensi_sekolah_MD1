@extends('layouts.app')
@section('title','Kelola Guru')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4><i class="fas fa-chalkboard-teacher me-2"></i>Kelola Guru</h4>
  <div>
    <a href="{{ route('guru.download.all.zip') }}" class="btn btn-sm btn-outline-secondary me-2"><i class="fas fa-file-archive me-1"></i> ZIP</a>
    <a href="{{ route('guru.download.all.pdf') }}" class="btn btn-sm btn-outline-secondary me-2"><i class="fas fa-file-pdf me-1"></i> PDF</a>
    <a href="{{ route('guru.create') }}" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i> Tambah Guru</a>
  </div>
</div>
<form method="get" class="mb-3">
  <div class="input-group">
    <input type="text" name="search" class="form-control" placeholder="Cari nama / email / NIP / mapel" value="{{ $search }}">
    <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i></button>
  </div>
</form>
@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif
<table class="table table-bordered table-sm align-middle">
  <thead class="table-light">
    <tr>
      <th>#</th>
      <th>Nama</th>
      <th>Email</th>
      <th>NIP</th>
      <th>Mapel</th>
      <th>Aktif</th>
      <th>QR</th>
      <th>Aksi</th>
    </tr>
  </thead>
  <tbody>
    @forelse($guru as $g)
    <tr>
      <td>{{ $loop->iteration + ($guru->currentPage()-1)*$guru->perPage() }}</td>
      <td>{{ $g->name }}</td>
      <td>{{ $g->email }}</td>
      <td>{{ $g->nip ?? '-' }}</td>
      <td>{{ $g->mata_pelajaran ?? '-' }}</td>
      <td>{!! $g->is_active ? '<span class="badge bg-success">Ya</span>' : '<span class="badge bg-secondary">Tidak</span>' !!}</td>
      <td>
        @if($g->qr_code)
          <small class="text-muted d-block">{{ $g->qr_code }}</small>
          <a href="{{ route('guru.qr',$g) }}" class="btn btn-sm btn-outline-dark mt-1"><i class="fas fa-download"></i></a>
        @else
          <span class="text-muted">Belum</span>
        @endif
      </td>
      <td>
        <a href="{{ route('guru.edit',$g) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
        <form action="{{ route('guru.destroy',$g) }}" method="post" style="display:inline-block" onsubmit="return confirm('Hapus guru ini?')">
          @csrf @method('delete')
          <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
        </form>
      </td>
    </tr>
    @empty
    <tr><td colspan="8" class="text-center text-muted">Tidak ada data</td></tr>
    @endforelse
  </tbody>
</table>
{{ $guru->links() }}
@endsection
