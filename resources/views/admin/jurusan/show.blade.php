@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Detail Jurusan: {{ $jurusan->nama_jurusan }}</h5>
                    <div>
                        <a href="{{ route('jurusan.edit', ['jurusan' => $jurusan->id]) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('jurusan.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 30%">Kode Jurusan</th>
                                    <td><span class="badge bg-primary">{{ $jurusan->kode_jurusan }}</span></td>
                                </tr>
                                <tr>
                                    <th>Nama Jurusan</th>
                                    <td>{{ $jurusan->nama_jurusan }}</td>
                                </tr>
                                <tr>
                                    <th>Deskripsi</th>
                                    <td>{{ $jurusan->deskripsi ?? 'Tidak ada deskripsi' }}</td>
                                </tr>
                                <tr>
                                    <th>Jumlah Kelas</th>
                                    <td>{{ $jurusan->kelas->count() }}</td>
                                </tr>
                                <tr>
                                    <th>Jumlah Siswa</th>
                                    <td>{{ $jurusan->siswa->count() }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Daftar Kelas di Jurusan Ini -->
                    <h5 class="mt-4 mb-3">Daftar Kelas</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="kelas-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kelas</th>
                                    <th>Kapasitas</th>
                                    <th>Jumlah Siswa</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($jurusan->kelas as $index => $kelas)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $kelas->tingkat }} {{ $kelas->nama_kelas }}</td>
                                    <td>{{ $kelas->kapasitas }}</td>
                                    <td>{{ $kelas->siswa->count() }}</td>
                                    <td>
                                        <a href="{{ route('kelas.show', ['kelas' => $kelas->id]) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">Tidak ada kelas untuk jurusan ini</td>
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
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#kelas-table').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
            }
        });
    });
</script>
@endpush
