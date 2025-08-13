@extends('layouts.app')
@section('title','Tambah Guru')
@section('content')
<h4 class="mb-3"><i class="fas fa-user-plus me-2"></i>Tambah Guru</h4>
<form method="post" action="{{ route('guru.store') }}" class="card p-3">
@csrf
<div class="row g-3">
  <div class="col-md-6">
    <label class="form-label">Nama</label>
    <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
  </div>
  <div class="col-md-6">
    <label class="form-label">Email</label>
    <input type="email" name="email" class="form-control" required value="{{ old('email') }}">
  </div>
  <div class="col-md-4">
    <label class="form-label">NIP</label>
    <input type="text" name="nip" class="form-control" value="{{ old('nip') }}">
  </div>
  <div class="col-md-4">
    <label class="form-label">Mata Pelajaran</label>
    <input type="text" name="mata_pelajaran" class="form-control" value="{{ old('mata_pelajaran') }}">
  </div>
  <div class="col-md-2">
    <label class="form-label">PIN (4 digit)</label>
    <input type="text" name="pin" class="form-control" maxlength="4" value="{{ old('pin') }}">
  </div>
  <div class="col-md-2 d-flex align-items-end">
    <div class="form-check">
      <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active') ? 'checked' : '' }}>
      <label class="form-check-label" for="is_active">Aktif</label>
    </div>
  </div>
  <div class="col-12">
    <button class="btn btn-primary"><i class="fas fa-save me-1"></i>Simpan</button>
    <a href="{{ route('guru.index') }}" class="btn btn-secondary">Batal</a>
  </div>
</div>
</form>
@endsection
