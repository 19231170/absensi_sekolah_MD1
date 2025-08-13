@extends('layouts.app')
@section('title','Edit Guru')
@section('content')
<h4 class="mb-3"><i class="fas fa-edit me-2"></i>Edit Guru</h4>
<form method="post" action="{{ route('guru.update',$guru) }}" class="card p-3">
@csrf @method('put')
<div class="row g-3">
  <div class="col-md-6">
    <label class="form-label">Nama</label>
    <input type="text" name="name" class="form-control" required value="{{ old('name',$guru->name) }}">
  </div>
  <div class="col-md-6">
    <label class="form-label">Email</label>
    <input type="email" name="email" class="form-control" required value="{{ old('email',$guru->email) }}">
  </div>
  <div class="col-md-4">
    <label class="form-label">NIP</label>
    <input type="text" name="nip" class="form-control" value="{{ old('nip',$guru->nip) }}">
  </div>
  <div class="col-md-4">
    <label class="form-label">Mata Pelajaran</label>
    <input type="text" name="mata_pelajaran" class="form-control" value="{{ old('mata_pelajaran',$guru->mata_pelajaran) }}">
  </div>
  <div class="col-md-2">
    <label class="form-label">PIN (4 digit)</label>
    <input type="text" name="pin" class="form-control" maxlength="4" value="{{ old('pin',$guru->pin) }}">
  </div>
  <div class="col-md-2 d-flex align-items-end">
    <div class="form-check">
      <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active',$guru->is_active) ? 'checked' : '' }}>
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
