@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Detail Kelas: {{ $kelas->tingkat }} {{ $kelas->nama_kelas }}</h5>
                    <div>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-download"></i> Download QR
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('kelas.download.qr', $kelas->id) }}">
                                    <i class="fas fa-file-archive"></i> Download ZIP (PHP)
                                    <small class="text-muted d-block">Memerlukan ZIP extension</small>
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><h6 class="dropdown-header">Alternatif Download:</h6></li>
                                <li><a class="dropdown-item" href="{{ route('kelas.download-qr-js', $kelas->id) }}">
                                    <i class="fas fa-code"></i> Download ZIP (JavaScript)
                                    <small class="text-muted d-block">Tanpa PHP extension</small>
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('kelas.download-multiple', $kelas->id) }}">
                                    <i class="fas fa-download"></i> Download Individual
                                    <small class="text-muted d-block">Satu per satu</small>
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('kelas.qr.show', $kelas->id) }}">
                                    <i class="fas fa-images"></i> Lihat Semua QR
                                    <small class="text-muted d-block">Print/Save Manual</small>
                                </a></li>
                            </ul>
                        </div>
                        <a href="{{ route('kelas.edit', ['kelas' => $kelas->id]) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('kelas.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if(session('info'))
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            {{ session('info') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
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
                                                    data-name="{{ $siswa->nama }}"
                                                    data-nis="{{ $siswa->nis }}"
                                                    title="QR: {{ $siswa->qr_code }}">
                                                <i class="fas fa-qrcode"></i> Lihat QR
                                            </button>
                                            <small class="text-muted d-block mt-1">{{ Str::limit($siswa->qr_code, 15) }}</small>
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
                                        @if($siswa->qr_code)
                                            <a href="{{ route('kelas.siswa.qr', [$kelas->id, $siswa->nis]) }}" class="btn btn-sm btn-success">
                                                <i class="fas fa-download"></i> QR
                                            </a>
                                        @endif
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
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="qrModalLabel">
                    <i class="fas fa-qrcode me-2"></i>QR Code Siswa
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div id="qr-container" class="mb-3">
                    <!-- QR Code will be generated here -->
                </div>
                <div class="student-info">
                    <h6 id="qr-name" class="fw-bold text-primary mb-1"></h6>
                    <p id="qr-nis" class="text-muted mb-0"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Tutup
                </button>
                <button type="button" class="btn btn-primary" onclick="downloadCurrentQR()">
                    <i class="fas fa-download me-1"></i>Download
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- DataTables CSS & JS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<!-- QRious Library -->
<script src="https://cdn.jsdelivr.net/npm/qrious@4.0.2/dist/qrious.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#siswa-table').DataTable({
            "language": {
                "search": "Cari:",
                "lengthMenu": "Tampilkan _MENU_ data per halaman",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                "infoEmpty": "Menampilkan 0 sampai 0 dari 0 data",
                "infoFiltered": "(difilter dari _MAX_ total data)",
                "paginate": {
                    "first": "Pertama",
                    "last": "Terakhir",
                    "next": "Selanjutnya",
                    "previous": "Sebelumnya"
                },
                "emptyTable": "Tidak ada data yang tersedia"
            },
            "pageLength": 10,
            "responsive": true,
            "order": [[1, 'asc']] // Sort by NIS
        });
        
        // QR Code modal event handler
        $(document).on('click', '.show-qr', function(e) {
            e.preventDefault();
            
            const qrData = $(this).data('qr');
            const name = $(this).data('name');
            const nis = $(this).data('nis');
            
            console.log('Showing QR for:', {qrData, name, nis});
            
            // Update modal content
            $('#qr-name').text(name || 'Nama tidak tersedia');
            $('#qr-nis').text('NIS: ' + (nis || 'Tidak tersedia'));
            $('#qr-container').html('<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
            
            if (qrData && qrData.trim() !== '') {
                generateQRCode(qrData);
            } else {
                $('#qr-container').html('<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> Data QR Code tidak tersedia untuk siswa ini</div>');
            }
        });
        
        // QR Code generation function
        function generateQRCode(qrData) {
            console.log('Generating QR for data:', qrData);
            
            // Try QRious library first
            if (typeof QRious !== 'undefined') {
                try {
                    // Create canvas element
                    const canvasId = 'qr-canvas-' + Date.now();
                    const canvasHtml = '<canvas id="' + canvasId + '" style="border: 2px solid #9C27B0; border-radius: 10px; padding: 10px; background: white;"></canvas>';
                    
                    $('#qr-container').html(canvasHtml);
                    
                    // Generate QR Code
                    setTimeout(function() {
                        const canvas = document.getElementById(canvasId);
                        if (canvas) {
                            const qr = new QRious({
                                element: canvas,
                                value: qrData.toString(),
                                size: 200,
                                background: 'white',
                                foreground: 'black',
                                level: 'M',
                                padding: 10
                            });
                            console.log('QR Code generated successfully with QRious');
                        }
                    }, 100);
                    
                } catch (error) {
                    console.error('QRious error:', error);
                    generateFallbackQR(qrData);
                }
            } else {
                console.log('QRious not available, using fallback');
                generateFallbackQR(qrData);
            }
        }
        
        // Fallback QR generation using API
        function generateFallbackQR(qrData) {
            console.log('Using API fallback for QR generation:', qrData);
            
            const qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&bgcolor=FFFFFF&color=000000&data=' + encodeURIComponent(qrData);
            
            const imgHtml = '<div style="border: 2px solid #9C27B0; border-radius: 10px; padding: 10px; background: white; display: inline-block;">' +
                           '<img src="' + qrUrl + '" alt="QR Code" class="img-fluid" style="max-width: 200px; height: auto;" />' +
                           '</div>';
            
            $('#qr-container').html(imgHtml);
            
            // Check if image loads successfully
            $('#qr-container img').on('load', function() {
                console.log('QR image loaded successfully');
            }).on('error', function() {
                console.error('Failed to load QR image');
                $('#qr-container').html('<div class="alert alert-danger"><i class="fas fa-times-circle"></i> Gagal memuat QR Code.<br><small>Data: ' + qrData + '</small></div>');
            });
        }
        
        // Modal events for debugging
        $('#qrModal').on('show.bs.modal', function() {
            console.log('QR Modal opening...');
        });
        
        $('#qrModal').on('shown.bs.modal', function() {
            console.log('QR Modal fully opened');
        });
        
        // Download current QR function
        window.downloadCurrentQR = function() {
            const canvas = document.querySelector('#qr-container canvas');
            const img = document.querySelector('#qr-container img');
            const name = $('#qr-name').text();
            const nis = $('#qr-nis').text().replace('NIS: ', '');
            
            if (canvas) {
                // Download from canvas
                const link = document.createElement('a');
                link.download = 'QR_' + nis + '_' + name.replace(/[^a-zA-Z0-9]/g, '_') + '.png';
                link.href = canvas.toDataURL();
                link.click();
            } else if (img) {
                // Download from image
                const link = document.createElement('a');
                link.download = 'QR_' + nis + '_' + name.replace(/[^a-zA-Z0-9]/g, '_') + '.png';
                link.href = img.src;
                link.target = '_blank';
                link.click();
            } else {
                alert('QR Code belum dimuat. Silakan tunggu sebentar dan coba lagi.');
            }
        };
    });
</script>
@endpush
