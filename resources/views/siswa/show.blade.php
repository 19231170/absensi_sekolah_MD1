@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Detail Siswa</h5>
                    <div>
                        <a href="{{ route('siswa.edit', $siswa->nis) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('siswa.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center mb-4">
                            <div class="p-3 border rounded mb-3">
                                <!-- QR Code Image - Replace with actual library if available -->
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&bgcolor=FFFFFF&color=000000&data={{ $siswa->qr_code }}" 
                                     alt="QR Code {{ $siswa->qr_code }}" class="img-fluid">
                            </div>
                            <h5 class="mb-0">{{ $siswa->qr_code }}</h5>
                            <p class="text-muted small">QR Code Absensi</p>
                        </div>
                        <div class="col-md-8">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="30%">NIS</th>
                                    <td>{{ $siswa->nis }}</td>
                                </tr>
                                <tr>
                                    <th>Nama</th>
                                    <td>{{ $siswa->nama }}</td>
                                </tr>
                                <tr>
                                    <th>Kelas</th>
                                    <td>{{ $siswa->kelas->tingkat }} {{ $siswa->kelas->nama_kelas }}</td>
                                </tr>
                                <tr>
                                    <th>Jurusan</th>
                                    <td>{{ $siswa->kelas->jurusan->nama_jurusan }}</td>
                                </tr>
                                <tr>
                                    <th>Jenis Kelamin</th>
                                    <td>{{ $siswa->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                                </tr>
                                <tr>
                                    <th>Tanggal Lahir</th>
                                    <td>{{ $siswa->tanggal_lahir ? $siswa->tanggal_lahir->format('d F Y') : '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Alamat</th>
                                    <td>{{ $siswa->alamat ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Nomor HP</th>
                                    <td>{{ $siswa->nomor_hp ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        <span class="badge bg-{{ $siswa->status_aktif ? 'success' : 'danger' }}">
                                            {{ $siswa->status_aktif ? 'Aktif' : 'Tidak Aktif' }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h6 class="border-bottom pb-2 mb-3">Riwayat Absensi</h6>
                        @if($siswa->absensi->isEmpty())
                            <p class="text-muted">Belum ada data absensi</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Jam Masuk</th>
                                            <th>Jam Keluar</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($siswa->absensi->sortByDesc('tanggal') as $absensi)
                                        <tr>
                                            <td>{{ $absensi->tanggal->format('d M Y') }}</td>
                                            <td>{{ $absensi->jam_masuk ? $absensi->jam_masuk->format('H:i') : '-' }}</td>
                                            <td>{{ $absensi->jam_keluar ? $absensi->jam_keluar->format('H:i') : '-' }}</td>
                                            <td>
                                                @if($absensi->status == 'hadir')
                                                    <span class="badge bg-success">Hadir</span>
                                                @elseif($absensi->status == 'izin')
                                                    <span class="badge bg-warning">Izin</span>
                                                @elseif($absensi->status == 'sakit')
                                                    <span class="badge bg-info">Sakit</span>
                                                @elseif($absensi->status == 'alpa')
                                                    <span class="badge bg-danger">Alpa</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
