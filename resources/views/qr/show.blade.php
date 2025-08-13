@extends('layouts.app')

@section('title', 'QR Code - ' . $siswa->nama)

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-custom">
            <div class="card-header bg-success text-white text-center">
                <h4 class="mb-0">
                    <i class="fas fa-qrcode me-2"></i>
                    QR Code Siswa
                </h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <!-- Data Siswa -->
                        <h5 class="text-primary mb-3">Data Siswa</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td width="30%"><strong>NIS</strong></td>
                                <td>: {{ $siswa->nis }}</td>
                            </tr>
                            <tr>
                                <td><strong>Nama</strong></td>
                                <td>: {{ $siswa->nama }}</td>
                            </tr>
                            <tr>
                                <td><strong>Kelas</strong></td>
                                <td>: {{ $siswa->kelas->nama_lengkap }}</td>
                            </tr>
                            <tr>
                                <td><strong>Jurusan</strong></td>
                                <td>: {{ $siswa->kelas->jurusan->nama_jurusan }}</td>
                            </tr>
                            <tr>
                                <td><strong>QR Code</strong></td>
                                <td>: {{ $siswa->qr_code }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6 text-center">
                        <!-- QR Code -->
                        <h5 class="text-primary mb-3">QR Code</h5>
                        <div class="mb-3">
                            <img src="{{ route('qr.image', $siswa->nis) }}" 
                                 alt="QR Code {{ $siswa->nama }}" 
                                 class="img-fluid rounded border p-2"
                                 style="max-width: 200px;">
                        </div>
                        <p class="text-muted small">Scan QR code ini untuk absensi</p>
                        
                        <!-- Action Buttons -->
                        <div class="mt-4">
                            <a href="{{ route('qr.download', $siswa->nis) }}" class="btn btn-success">
                                <i class="fas fa-download me-1"></i>
                                Download QR
                            </a>
                            <button type="button" class="btn btn-primary ms-2" onclick="copyQrCode()">
                                <i class="fas fa-copy me-1"></i>
                                Copy QR Code
                            </button>
                            <a href="{{ route('jadwal-kelas.index') }}" class="btn btn-outline-success ms-2">
                                <i class="fas fa-calendar-alt me-1"></i>
                                Lihat Jadwal
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer text-center">
                <a href="{{ route('qr.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyQrCode() {
    const qrCode = '{{ $siswa->qr_code }}';
    navigator.clipboard.writeText(qrCode).then(() => {
        alert('QR Code berhasil disalin: ' + qrCode);
    }).catch(err => {
        console.error('Error copying text: ', err);
        alert('Gagal menyalin QR Code');
    });
}
</script>
@endpush
