@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Detail Kelas: {{ $kelas->tingkat }} {{ $kelas->nama_kelas }}</h5>
                    <div>
                        <a href="{{ route('kelas.edit', ['kelas' => $kelas->id]) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('kelas.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 30%">Kelas</th>
                                    <td>{{ $kelas->tingkat }} {{ $kelas->nama_kelas }}</td>
                                </tr>
                                <tr>
                                    <th>Jurusan</th>
                                    <td>
                                        @if($kelas->jurusan)
                                            <span class="badge bg-primary">{{ $kelas->jurusan->kode_jurusan }}</span>
                                            {{ $kelas->jurusan->nama_jurusan }}
                                        @else
                                            <span class="text-muted">Tidak ada jurusan</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Kapasitas</th>
                                    <td>{{ $kelas->kapasitas }} siswa</td>
                                </tr>
                                <tr>
                                    <th>Jumlah Siswa</th>
                                    <td>{{ $kelas->siswa->count() }} siswa</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        @if($kelas->is_active)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-danger">Non-aktif</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Keterangan</th>
                                    <td>{{ $kelas->keterangan ?? 'Tidak ada keterangan' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Daftar Siswa di Kelas Ini -->
                    <h5 class="mt-4 mb-3">Daftar Siswa</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="siswa-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>NIS</th>
                                    <th>Nama</th>
                                    <th>Jenis Kelamin</th>
                                    <th>QR Code</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($kelas->siswa as $index => $siswa)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $siswa->nis }}</td>
                                    <td>{{ $siswa->nama }}</td>
                                    <td>{{ $siswa->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                                    <td>
                                        @if($siswa->qr_code)
                                            <button class="btn btn-sm btn-outline-primary show-qr" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#qrModal" 
                                                    data-qr="{{ $siswa->qr_code }}" 
                                                    data-name="{{ $siswa->nama }}">
                                                <i class="fas fa-qrcode"></i> Lihat QR
                                            </button>
                                        @else
                                            <span class="badge bg-danger">Tidak ada QR</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($siswa->status_aktif)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-danger">Non-aktif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('siswa.show', $siswa->nis) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">Tidak ada siswa di kelas ini</td>
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

<!-- Modal QR Code -->
<div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="qrModalLabel">QR Code</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div id="qr-container"></div>
                <p id="qr-name" class="mt-2"></p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrious@4.0.2/dist/qrious.min.js"></script>
<script>
    $(document).ready(function() {
        $('#siswa-table').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
            }
        });
        
        // QR Code modal
        $('.show-qr').click(function() {
            const qrData = $(this).data('qr');
            const name = $(this).data('name');
            
            $('#qr-name').text(name);
            $('#qr-container').empty();
            
            const canvas = document.createElement('canvas');
            $('#qr-container').append(canvas);
            
            new QRious({
                element: canvas,
                value: qrData,
                size: 200
            });
        });
    });
</script>
@endpush
