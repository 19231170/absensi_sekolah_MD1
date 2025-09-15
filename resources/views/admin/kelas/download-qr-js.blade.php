@extends('layouts.app')

@section('title', 'Download QR Codes - JavaScript ZIP')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Download QR Codes (JavaScript ZIP)</h1>
                    <p class="text-muted">
                        Kelas: {{ $kelas->tingkat }} {{ $kelas->nama_kelas }} 
                        @if($kelas->jurusan)
                            - {{ $kelas->jurusan->nama_jurusan }}
                        @endif
                    </p>
                </div>
                <a href="{{ route('kelas.show', $kelas->id) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Progress and Download Section -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-download"></i> Download QR Codes ZIP
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Info Alert -->
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Metode Alternatif:</strong> 
                        Download ini menggunakan JavaScript untuk membuat ZIP file di browser Anda. 
                        Tidak memerlukan PHP ZIP extension.
                    </div>

                    <!-- Download Controls -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="d-grid gap-2">
                                <button id="downloadZipBtn" class="btn btn-primary btn-lg" onclick="downloadAllAsZip()">
                                    <i class="fas fa-file-archive"></i> Download Semua sebagai ZIP
                                </button>
                                <button id="downloadIndividualBtn" class="btn btn-outline-primary" onclick="downloadAllIndividual()">
                                    <i class="fas fa-download"></i> Download Satu per Satu
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Total QR Codes:</h6>
                                    <p class="card-text h4 text-primary">{{ count($siswaWithQr) }} siswa</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div id="progressContainer" style="display: none;">
                        <div class="mb-2">
                            <span id="progressText">Memproses...</span>
                        </div>
                        <div class="progress mb-3">
                            <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" 
                                 style="width: 0%"></div>
                        </div>
                    </div>

                    <!-- Students List Preview -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Daftar Siswa dengan QR Code</h6>
                        </div>
                        <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                            <div class="row">
                                @foreach($siswaWithQr as $siswa)
                                <div class="col-md-6 col-lg-4 mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="me-2">
                                            <i class="fas fa-qrcode text-primary"></i>
                                        </div>
                                        <div class="flex-fill">
                                            <small class="text-muted">{{ $siswa->nis }}</small><br>
                                            <span class="fw-bold">{{ $siswa->nama }}</span>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include JSZip and FileSaver libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>

<script>
let qrData = [];
let isProcessing = false;

// Load QR data when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadQrData();
});

async function loadQrData() {
    try {
        showProgress('Memuat data QR codes...', 0);
        
        const url = `{{ route('kelas.qr-codes-data', $kelas->id) }}`;
        console.log('Loading QR data from:', url);
        
        const response = await fetch(url);
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            console.error('Response is not JSON:', contentType);
            const text = await response.text();
            console.error('Response text:', text.substring(0, 500));
            throw new Error('Server mengembalikan response bukan JSON. Mungkin ada error authentication atau routing.');
        }
        
        const result = await response.json();
        console.log('Response result:', result);
        
        if (result.success) {
            qrData = result.data;
            hideProgress();
            console.log('QR Data loaded successfully:', qrData.length, 'items');
            
            // Update UI with stats
            if (result.stats) {
                const statsDiv = document.createElement('div');
                statsDiv.className = 'alert alert-info mt-2';
                statsDiv.innerHTML = `
                    <i class="fas fa-info-circle"></i> 
                    Data dimuat: ${result.stats.qr_generated} QR codes dari ${result.stats.total_siswa} siswa
                    ${result.stats.errors > 0 ? `<br><small class="text-warning">Warning: ${result.stats.errors} QR gagal diproses</small>` : ''}
                `;
                const cardBody = document.querySelector('.card-body');
                cardBody.insertBefore(statsDiv, cardBody.firstChild);
            }
        } else {
            throw new Error(result.message || 'Unknown error');
        }
    } catch (error) {
        console.error('Error loading QR data:', error);
        hideProgress();
        
        // Show detailed error
        const errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-danger mt-2';
        errorDiv.innerHTML = `
            <i class="fas fa-exclamation-circle"></i> 
            <strong>Gagal memuat data QR:</strong> ${error.message}
            <br><small>Silakan refresh halaman atau coba route debug: 
            <a href="/debug/kelas/{{ $kelas->id }}/qr-data" target="_blank">Test Debug</a></small>
        `;
        const cardBody = document.querySelector('.card-body');
        cardBody.insertBefore(errorDiv, cardBody.firstChild);
    }
}

async function downloadAllAsZip() {
    if (isProcessing) return;
    if (qrData.length === 0) {
        alert('Data QR belum dimuat. Silakan coba lagi.');
        return;
    }
    
    isProcessing = true;
    const btn = document.getElementById('downloadZipBtn');
    const originalText = btn.innerHTML;
    
    try {
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
        btn.disabled = true;
        
        showProgress('Membuat ZIP file...', 0);
        
        const zip = new JSZip();
        const total = qrData.length;
        
        // Add each QR code to ZIP
        for (let i = 0; i < qrData.length; i++) {
            const item = qrData[i];
            const progress = Math.round((i / total) * 100);
            
            showProgress(`Menambahkan ${item.filename}...`, progress);
            
            // Convert base64 to binary
            const binaryData = atob(item.data);
            const bytes = new Uint8Array(binaryData.length);
            for (let j = 0; j < binaryData.length; j++) {
                bytes[j] = binaryData.charCodeAt(j);
            }
            
            zip.file(item.filename, bytes);
            
            // Small delay to prevent browser freeze
            if (i % 5 === 0) {
                await new Promise(resolve => setTimeout(resolve, 10));
            }
        }
        
        showProgress('Membuat file ZIP...', 95);
        
        // Generate ZIP file
        const content = await zip.generateAsync({
            type: "blob",
            compression: "DEFLATE",
            compressionOptions: { level: 6 }
        });
        
        showProgress('Download dimulai...', 100);
        
        // Download ZIP file
        const kelasInfo = `{{ $kelas->tingkat }}_{{ $kelas->nama_kelas }}`;
        const timestamp = new Date().toISOString().slice(0, 19).replace(/[:-]/g, '');
        const filename = `QR_Codes_Kelas_${kelasInfo}_${timestamp}.zip`;
        
        saveAs(content, filename);
        
        setTimeout(() => {
            hideProgress();
            showSuccess('ZIP file berhasil didownload!');
        }, 1000);
        
    } catch (error) {
        console.error('Error creating ZIP:', error);
        hideProgress();
        alert('Gagal membuat ZIP file: ' + error.message);
    } finally {
        isProcessing = false;
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}

async function downloadAllIndividual() {
    if (isProcessing) return;
    if (qrData.length === 0) {
        alert('Data QR belum dimuat. Silakan coba lagi.');
        return;
    }
    
    isProcessing = true;
    const btn = document.getElementById('downloadIndividualBtn');
    const originalText = btn.innerHTML;
    
    try {
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Downloading...';
        btn.disabled = true;
        
        const total = qrData.length;
        
        for (let i = 0; i < qrData.length; i++) {
            const item = qrData[i];
            const progress = Math.round((i / total) * 100);
            
            showProgress(`Downloading ${item.filename}...`, progress);
            
            // Convert base64 to blob
            const binaryData = atob(item.data);
            const bytes = new Uint8Array(binaryData.length);
            for (let j = 0; j < binaryData.length; j++) {
                bytes[j] = binaryData.charCodeAt(j);
            }
            
            const blob = new Blob([bytes], { type: 'image/png' });
            saveAs(blob, item.filename);
            
            // Delay between downloads
            await new Promise(resolve => setTimeout(resolve, 500));
        }
        
        hideProgress();
        showSuccess('Semua QR codes berhasil didownload!');
        
    } catch (error) {
        console.error('Error downloading individual files:', error);
        hideProgress();
        alert('Gagal download file: ' + error.message);
    } finally {
        isProcessing = false;
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}

function showProgress(text, percent) {
    const container = document.getElementById('progressContainer');
    const progressText = document.getElementById('progressText');
    const progressBar = document.getElementById('progressBar');
    
    container.style.display = 'block';
    progressText.textContent = text;
    progressBar.style.width = percent + '%';
    progressBar.textContent = percent + '%';
}

function hideProgress() {
    const container = document.getElementById('progressContainer');
    container.style.display = 'none';
}

function showSuccess(message) {
    // Create success alert
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible fade show';
    alertDiv.innerHTML = `
        <i class="fas fa-check-circle"></i> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insert at top of card body
    const cardBody = document.querySelector('.card-body');
    cardBody.insertBefore(alertDiv, cardBody.firstChild);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}
</script>
@endsection
