@extends('layouts.app')

@section('title', 'Debug Testing')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-bug me-2"></i>
                        Debug Testing Panel
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Halaman ini untuk debugging error pada sistem absensi.
                    </div>

                    <!-- Test Data Display -->
                    <div class="mb-4">
                        <h6>Data Jam Sekolah:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama Sesi</th>
                                        <th>Jam Masuk</th>
                                        <th>Batas Telat</th>
                                        <th>Jam Keluar</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $jamSekolahList = \App\Models\JamSekolah::all();
                                    @endphp
                                    @foreach($jamSekolahList as $jam)
                                    <tr>
                                        <td>{{ $jam->id }}</td>
                                        <td>{{ $jam->nama_sesi }}</td>
                                        <td>{{ $jam->jam_masuk }}</td>
                                        <td>{{ $jam->batas_telat }}</td>
                                        <td>{{ $jam->jam_keluar }}</td>
                                        <td>
                                            @if($jam->status_aktif)
                                                <span class="badge bg-success">Aktif</span>
                                            @else
                                                <span class="badge bg-danger">Tidak Aktif</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Sample Siswa -->
                    <div class="mb-4">
                        <h6>Sample Data Siswa:</h6>
                        @php
                            $sampleSiswa = \App\Models\Siswa::with('kelas.jurusan')->first();
                        @endphp
                        @if($sampleSiswa)
                        <div class="card">
                            <div class="card-body">
                                <p><strong>NIS:</strong> {{ $sampleSiswa->nis }}</p>
                                <p><strong>Nama:</strong> {{ $sampleSiswa->nama }}</p>
                                <p><strong>QR Code:</strong> {{ $sampleSiswa->qr_code }}</p>
                                <p><strong>Kelas:</strong> {{ $sampleSiswa->kelas->nama_kelas ?? 'N/A' }}</p>
                                <p><strong>Jurusan:</strong> {{ $sampleSiswa->kelas->jurusan->nama_jurusan ?? 'N/A' }}</p>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Simple Test Form -->
                    <div class="mb-4">
                        <h6>Test Absensi Manual:</h6>
                        <form id="debugForm">
                            @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <select class="form-select" id="debug_jam_sekolah_id">
                                        <option value="">-- Pilih Sesi --</option>
                                        @foreach($jamSekolahList as $jam)
                                        <option value="{{ $jam->id }}">{{ $jam->nama_sesi }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select class="form-select" id="debug_type">
                                        <option value="">-- Pilih Jenis --</option>
                                        <option value="masuk">Masuk</option>
                                        <option value="keluar">Keluar</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button type="button" class="btn btn-primary w-100" onclick="debugTest()">
                                        Test
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Result Display -->
                    <div id="debug-result"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function debugTest() {
    const jamSekolahId = $('#debug_jam_sekolah_id').val();
    const type = $('#debug_type').val();
    
    if (!jamSekolahId || !type) {
        alert('Pilih sesi dan jenis absensi!');
        return;
    }
    
    const qrCode = '{{ $sampleSiswa->qr_code ?? "QR20251001" }}';
    
    $('#debug-result').html('<div class="alert alert-info">Sedang memproses...</div>');
    
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    const requestData = {
        qr_code: qrCode,
        jam_sekolah_id: jamSekolahId,
        type: type
    };
    
    console.log('Sending data:', requestData);
    
    $.ajax({
        url: '{{ route("absensi.scan") }}',
        method: 'POST',
        data: requestData,
        success: function(response) {
            console.log('Success response:', response);
            $('#debug-result').html(`
                <div class="alert alert-success">
                    <h6>✅ Berhasil!</h6>
                    <pre>${JSON.stringify(response, null, 2)}</pre>
                </div>
            `);
        },
        error: function(xhr, status, error) {
            console.error('Error:', xhr.responseText);
            console.error('Status:', status);
            console.error('Error:', error);
            
            $('#debug-result').html(`
                <div class="alert alert-danger">
                    <h6>❌ Error!</h6>
                    <p><strong>Status:</strong> ${status}</p>
                    <p><strong>Error:</strong> ${error}</p>
                    <p><strong>Response:</strong></p>
                    <pre>${xhr.responseText}</pre>
                </div>
            `);
        }
    });
}
</script>
@endpush
