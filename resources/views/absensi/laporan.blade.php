@extends('layouts.app')

@section('title', 'Laporan Absensi')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-custom">
            <div class="card-header bg-info text-white">
                <h4 class="mb-0">
                    <i class="fas fa-chart-bar me-2"></i>
                    Laporan Absensi
                </h4>
            </div>
            <div class="card-body">
                <!-- Filter -->
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label for="tanggal" class="form-label">Tanggal:</label>
                        <input type="date" class="form-control" id="tanggal" name="tanggal" value="{{ $tanggal }}">
                    </div>
                    <div class="col-md-4">
                        <label for="jam_sekolah_id" class="form-label">Sesi Sekolah:</label>
                        <select class="form-select" id="jam_sekolah_id" name="jam_sekolah_id">
                            <option value="">-- Semua Sesi --</option>
                            @foreach($jamSekolah as $jam)
                                <option value="{{ $jam->id }}" {{ $jamSekolahId == $jam->id ? 'selected' : '' }}>
                                    {{ $jam->nama_sesi }} ({{ $jam->jam_masuk }} - {{ $jam->jam_keluar }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Statistik -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-user-check fa-2x mb-2"></i>
                                <h4>{{ $absensi->filter(function($item) { return (is_array($item) ? $item['status'] : $item->status_masuk) == 'hadir'; })->count() }}</h4>
                                <small>Hadir</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-dark">
                            <div class="card-body text-center">
                                <i class="fas fa-user-clock fa-2x mb-2"></i>
                                <h4>{{ $absensi->filter(function($item) { return (is_array($item) ? $item['status'] : $item->status_masuk) == 'telat'; })->count() }}</h4>
                                <small>Telat</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-sign-out-alt fa-2x mb-2"></i>
                                <h4>{{ $absensi->filter(function($item) { return (is_array($item) ? $item['jam_keluar'] : $item->jam_keluar) != null; })->count() }}</h4>
                                <small>Sudah Keluar</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-users fa-2x mb-2"></i>
                                <h4>{{ $absensi->count() }}</h4>
                                <small>Total Absensi</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabel Data -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>NIS</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Jurusan</th>
                                <th>Sesi/Pelajaran</th>
                                <th>Mata Pelajaran</th>
                                <th>Guru Pengampu</th>
                                <th>Jam Masuk</th>
                                <th>Jam Keluar</th>
                                <th>Status</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($absensi as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ is_array($item) ? $item['nis'] : $item->nis }}</td>
                                <td>{{ is_array($item) ? $item['nama'] : $item->siswa->nama }}</td>
                                <td>{{ is_array($item) ? $item['kelas'] : $item->siswa->kelas->nama_lengkap }}</td>
                                <td>{{ is_array($item) ? $item['jurusan'] : $item->siswa->kelas->jurusan->nama_jurusan }}</td>
                                <td>
                                    {{ is_array($item) ? $item['sesi'] : $item->jamSekolah->nama_sesi }}
                                    @if(is_array($item) && $item['type'] == 'pelajaran')
                                        <span class="badge bg-info ms-1">Pelajaran</span>
                                    @elseif(is_array($item) && $item['type'] == 'sesi')
                                        <span class="badge bg-secondary ms-1">Sesi</span>
                                    @endif
                                </td>
                                <td>{{ is_array($item) ? $item['mata_pelajaran'] : '-' }}</td>
                                <td>{{ is_array($item) ? $item['guru_pengampu'] : '-' }}</td>
                                <td>
                                    @if(is_array($item) ? $item['jam_masuk'] : $item->jam_masuk)
                                        <span class="badge bg-success">{{ is_array($item) ? $item['jam_masuk'] : $item->jam_masuk }}</span>
                                    @else
                                        <span class="badge bg-secondary">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if(is_array($item) ? $item['jam_keluar'] : $item->jam_keluar)
                                        <span class="badge bg-primary">{{ is_array($item) ? $item['jam_keluar'] : $item->jam_keluar }}</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Belum Keluar</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $status = is_array($item) ? $item['status'] : $item->status_masuk;
                                    @endphp
                                    @if($status == 'hadir')
                                        <span class="badge bg-success">Hadir</span>
                                    @elseif($status == 'telat')
                                        <span class="badge bg-warning text-dark">Telat</span>
                                    @else
                                        <span class="badge bg-danger">Alpha</span>
                                    @endif
                                </td>
                                <td>{{ is_array($item) ? $item['keterangan'] : ($item->keterangan ?? '-') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="12" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Tidak ada data absensi untuk tanggal yang dipilih.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Export Buttons -->
                @if($absensi->count() > 0)
                <div class="text-center mt-4">
                    <button class="btn btn-success me-2" onclick="exportToExcel()">
                        <i class="fas fa-file-excel me-1"></i> Export Excel
                    </button>
                    <button class="btn btn-danger" onclick="exportToPDF()">
                        <i class="fas fa-file-pdf me-1"></i> Export PDF
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function exportToExcel() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'excel');
    window.open(window.location.pathname + '?' + params.toString());
}

function exportToPDF() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'pdf');
    window.open(window.location.pathname + '?' + params.toString());
}
</script>
@endpush
