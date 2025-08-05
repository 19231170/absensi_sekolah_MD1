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
                            <a href="{{ route('absensi.index') }}" class="btn btn-outline-success ms-2">
                                <i class="fas fa-camera me-1"></i>
                                Scan Sekarang
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

        <!-- Testing Panel -->
        <div class="card card-custom mt-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                    <i class="fas fa-flask me-2"></i>
                    Panel Testing
                </h5>
            </div>
            <div class="card-body">
                <p class="mb-3">Gunakan panel ini untuk testing absensi tanpa scan QR:</p>
                
                <form id="testForm" class="row g-3">
                    @csrf
                    <div class="col-md-6">
                        <label class="form-label">Sesi Sekolah:</label>
                        <select class="form-select" id="test_jam_sekolah_id" required>
                            <option value="">-- Pilih Sesi --</option>
                            @php
                                $jamSekolahData = \App\Models\JamSekolah::aktif()->get();
                            @endphp
                            @foreach($jamSekolahData as $jam)
                                <option value="{{ $jam->id }}">{{ $jam->nama_sesi }} ({{ $jam->jam_masuk }} - {{ $jam->jam_keluar }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Jenis Absensi:</label>
                        <select class="form-select" id="test_type" required>
                            <option value="">-- Pilih Jenis --</option>
                            <option value="masuk">Absen Masuk</option>
                            <option value="keluar">Absen Keluar</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="button" class="btn btn-warning" onclick="testAbsensi()">
                            <i class="fas fa-play me-1"></i>
                            Test Absensi
                        </button>
                        <button type="button" class="btn btn-secondary ms-2" onclick="clearTestResult()">
                            <i class="fas fa-times me-1"></i>
                            Clear Result
                        </button>
                    </div>
                </form>
                
                <div id="test-result" class="mt-3"></div>
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

function testAbsensi() {
    const jamSekolahId = $('#test_jam_sekolah_id').val();
    const type = $('#test_type').val();
    
    if (!jamSekolahId || !type) {
        alert('Pilih sesi sekolah dan jenis absensi terlebih dahulu!');
        return;
    }
    
    // Clear previous results
    $('#test-result').html('<div class="alert alert-info"><i class="fas fa-spinner fa-spin me-2"></i>Memproses...</div>');
    
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    $.ajax({
        url: '{{ route("absensi.scan") }}',
        method: 'POST',
        data: {
            qr_code: '{{ $siswa->qr_code }}',
            jam_sekolah_id: parseInt(jamSekolahId),
            type: type
        },
        timeout: 10000, // 10 seconds timeout
        success: function(response) {
            if (response.success) {
                $('#test-result').html(`
                    <div class="alert alert-success">
                        <h6><i class="fas fa-check-circle me-2"></i>${response.message}</h6>
                        <div class="row">
                            <div class="col-6">
                                <strong>NIS:</strong> ${response.data.nis}<br>
                                <strong>Nama:</strong> ${response.data.nama}<br>
                                <strong>Kelas:</strong> ${response.data.kelas}
                            </div>
                            <div class="col-6">
                                <strong>Status:</strong> ${response.data.status}<br>
                                <strong>Jam:</strong> ${response.data.jam_masuk || response.data.jam_keluar || 'N/A'}<br>
                                <strong>Sesi:</strong> ${response.data.sesi || 'N/A'}
                            </div>
                        </div>
                    </div>
                `);
            } else {
                $('#test-result').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        ${response.message}
                    </div>
                `);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', xhr.responseText);
            let errorMessage = 'Terjadi kesalahan saat memproses absensi.';
            
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (status === 'timeout') {
                errorMessage = 'Request timeout. Silakan coba lagi.';
            } else if (status === 'parsererror') {
                errorMessage = 'Error parsing response. Silakan refresh halaman.';
            }
            
            $('#test-result').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    ${errorMessage}
                    <br><small class="text-muted">Error details: ${error}</small>
                </div>
            `);
        }
    });
}

function clearTestResult() {
    $('#test-result').html('');
    $('#test_jam_sekolah_id').val('');
    $('#test_type').val('');
}
</script>
@endpush
