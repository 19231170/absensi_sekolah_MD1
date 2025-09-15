@extends('layouts.app')

@section('title', 'Download Multiple QR - Individual Files')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Download QR Codes - Individual Files</h1>
                    <p class="text-muted">
                        Kelas: {{ $kelas->tingkat }} {{ $kelas->nama_kelas }} 
                        @if($kelas->jurusan)
                            - {{ $kelas->jurusan->nama_jurusan }}
                        @endif
                    </p>
                </div>
                <div>
                    <a href="{{ route('kelas.download-qr-js', $kelas->id) }}" class="btn btn-info me-2">
                        <i class="fas fa-file-archive"></i> Download as ZIP
                    </a>
                    <a href="{{ route('kelas.show', $kelas->id) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Download Controls -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-download"></i> Download Controls
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <button id="downloadAllBtn" class="btn btn-primary btn-lg w-100 mb-2" onclick="downloadAllFiles()">
                                <i class="fas fa-download"></i> Download Semua ({{ count($siswaWithQr) }} files)
                            </button>
                            <button id="downloadSelectedBtn" class="btn btn-outline-primary w-100" onclick="downloadSelected()" disabled>
                                <i class="fas fa-check-square"></i> Download Yang Dipilih (<span id="selectedCount">0</span>)
                            </button>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Status Download:</h6>
                                    <div id="downloadStatus">
                                        <span class="text-muted">Belum dimulai</span>
                                    </div>
                                    <div class="progress mt-2" style="display: none;" id="progressContainer">
                                        <div id="progressBar" class="progress-bar" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Students List with Individual Download -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Daftar Siswa dengan QR Code</h5>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                        <label class="form-check-label" for="selectAll">
                            Pilih Semua
                        </label>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($siswaWithQr as $index => $siswa)
                        <div class="col-lg-6 mb-3">
                            <div class="card border">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="form-check me-3">
                                            <input class="form-check-input qr-checkbox" type="checkbox" 
                                                   data-nis="{{ $siswa->nis }}" 
                                                   data-nama="{{ $siswa->nama }}"
                                                   onchange="updateSelectedCount()">
                                        </div>
                                        <div class="me-3">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px;">
                                                <i class="fas fa-qrcode"></i>
                                            </div>
                                        </div>
                                        <div class="flex-fill">
                                            <h6 class="mb-1">{{ $siswa->nama }}</h6>
                                            <p class="text-muted mb-1">NIS: {{ $siswa->nis }}</p>
                                            @if($siswa->kelas)
                                            <small class="text-muted">
                                                {{ $siswa->kelas->tingkat }} {{ $siswa->kelas->nama_kelas }}
                                            </small>
                                            @endif
                                        </div>
                                        <div>
                                            <a href="{{ route('kelas.siswa.qr', [$kelas->id, $siswa->nis]) }}" 
                                               class="btn btn-sm btn-outline-primary"
                                               title="Download QR {{ $siswa->nama }}">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        </div>
                                    </div>
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

<script>
let isDownloading = false;

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.qr-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateSelectedCount();
}

function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('.qr-checkbox:checked');
    const count = checkboxes.length;
    const totalCheckboxes = document.querySelectorAll('.qr-checkbox');
    
    document.getElementById('selectedCount').textContent = count;
    document.getElementById('downloadSelectedBtn').disabled = count === 0;
    
    // Update select all checkbox
    const selectAll = document.getElementById('selectAll');
    if (count === 0) {
        selectAll.indeterminate = false;
        selectAll.checked = false;
    } else if (count === totalCheckboxes.length) {
        selectAll.indeterminate = false;
        selectAll.checked = true;
    } else {
        selectAll.indeterminate = true;
    }
}

async function downloadAllFiles() {
    if (isDownloading) return;
    
    const checkboxes = document.querySelectorAll('.qr-checkbox');
    const files = Array.from(checkboxes).map(cb => ({
        nis: cb.dataset.nis,
        nama: cb.dataset.nama
    }));
    
    await downloadFiles(files);
}

async function downloadSelected() {
    if (isDownloading) return;
    
    const selectedCheckboxes = document.querySelectorAll('.qr-checkbox:checked');
    if (selectedCheckboxes.length === 0) {
        alert('Pilih minimal satu siswa untuk didownload.');
        return;
    }
    
    const files = Array.from(selectedCheckboxes).map(cb => ({
        nis: cb.dataset.nis,
        nama: cb.dataset.nama
    }));
    
    await downloadFiles(files);
}

async function downloadFiles(files) {
    if (isDownloading) return;
    
    isDownloading = true;
    const downloadAllBtn = document.getElementById('downloadAllBtn');
    const downloadSelectedBtn = document.getElementById('downloadSelectedBtn');
    const originalAllText = downloadAllBtn.innerHTML;
    const originalSelectedText = downloadSelectedBtn.innerHTML;
    
    try {
        // Disable buttons
        downloadAllBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Downloading...';
        downloadSelectedBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Downloading...';
        downloadAllBtn.disabled = true;
        downloadSelectedBtn.disabled = true;
        
        // Show progress
        showProgress(0, files.length);
        
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            
            updateStatus(`Downloading QR ${file.nama} (${i + 1}/${files.length})`);
            updateProgress(i, files.length);
            
            try {
                // Create download link and trigger
                const downloadUrl = `{{ route('kelas.siswa.qr', [$kelas->id, '__NIS__']) }}`.replace('__NIS__', file.nis);
                
                const link = document.createElement('a');
                link.href = downloadUrl;
                link.download = `QR_${file.nis}_${file.nama}.png`;
                link.style.display = 'none';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                // Delay between downloads to prevent browser blocking
                if (i < files.length - 1) {
                    await new Promise(resolve => setTimeout(resolve, 800));
                }
                
            } catch (error) {
                console.error(`Error downloading QR for ${file.nama}:`, error);
            }
        }
        
        updateProgress(files.length, files.length);
        updateStatus(`Selesai! ${files.length} QR codes berhasil didownload.`);
        
        // Show success message
        showSuccessAlert(`${files.length} QR codes berhasil didownload!`);
        
    } catch (error) {
        console.error('Download error:', error);
        updateStatus('Download gagal: ' + error.message);
        showErrorAlert('Download gagal: ' + error.message);
    } finally {
        // Re-enable buttons
        setTimeout(() => {
            downloadAllBtn.innerHTML = originalAllText;
            downloadSelectedBtn.innerHTML = originalSelectedText;
            downloadAllBtn.disabled = false;
            downloadSelectedBtn.disabled = false;
            isDownloading = false;
            hideProgress();
        }, 2000);
    }
}

function showProgress(current, total) {
    const container = document.getElementById('progressContainer');
    container.style.display = 'block';
    updateProgress(current, total);
}

function updateProgress(current, total) {
    const progressBar = document.getElementById('progressBar');
    const percent = total > 0 ? Math.round((current / total) * 100) : 0;
    progressBar.style.width = percent + '%';
    progressBar.textContent = percent + '%';
}

function hideProgress() {
    const container = document.getElementById('progressContainer');
    container.style.display = 'none';
}

function updateStatus(message) {
    document.getElementById('downloadStatus').innerHTML = message;
}

function showSuccessAlert(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible fade show mt-3';
    alertDiv.innerHTML = `
        <i class="fas fa-check-circle"></i> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const cardBody = document.querySelector('.card-body');
    cardBody.insertBefore(alertDiv, cardBody.firstChild);
    
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

function showErrorAlert(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-danger alert-dismissible fade show mt-3';
    alertDiv.innerHTML = `
        <i class="fas fa-exclamation-circle"></i> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const cardBody = document.querySelector('.card-body');
    cardBody.insertBefore(alertDiv, cardBody.firstChild);
    
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 8000);
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateSelectedCount();
});
</script>
@endsection
