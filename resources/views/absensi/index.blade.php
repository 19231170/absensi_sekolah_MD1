@extends('layouts.app')

@section('title', 'Scan QR Code - Absensi')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-custom">
            <div class="card-header bg-primary text-white text-center">
                <h4 class="mb-0">
                    <i class="fas fa-qrcode me-2"></i>
                    Scan QR Code untuk Absensi
                </h4>
                <small class="mt-1 d-block opacity-75">
                    <i class="fas fa-calendar me-1"></i>
                    Hari {{ ucfirst($hariIni ?? 'ini') }} - {{ Carbon\Carbon::now('Asia/Jakarta')->format('d/m/Y') }}
                </small>
            </div>
            <div class="card-body p-4">
                <!-- Form Pilihan -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="jam_sekolah_id" class="form-label">
                            Pilih Sesi Sekolah untuk Hari {{ ucfirst($hariIni ?? 'Ini') }}:
                        </label>
                        <select class="form-select" id="jam_sekolah_id" required>
                            <option value="">-- Pilih Sesi --</option>
                            @foreach($jamSekolah as $jam)
                                <option value="{{ $jam->id }}">
                                    {{ $jam->nama_sesi }}
                                </option>
                            @endforeach
                        </select>
                        @if($jamSekolah->isEmpty())
                            <small class="text-muted mt-1">
                                <i class="fas fa-info-circle me-1"></i>
                                Tidak ada sesi untuk hari {{ $hariIni ?? 'ini' }}.
                            </small>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label for="absen_type" class="form-label">Jenis Absensi:</label>
                        <select class="form-select" id="absen_type" required>
                            <option value="">-- Pilih Jenis --</option>
                            <option value="masuk">Absen Masuk</option>
                            <option value="keluar">Absen Keluar</option>
                        </select>
                    </div>
                </div>

                <!-- Scanner Container -->
                <div class="qr-scanner-container">
                    <div class="text-center mb-3">
                        <button type="button" class="btn btn-success btn-custom" id="startScan">
                            <i class="fas fa-camera me-2"></i>
                            Mulai Scan QR Code
                        </button>
                        <button type="button" class="btn btn-danger btn-custom d-none" id="stopScan">
                            <i class="fas fa-stop me-2"></i>
                            Stop Scan
                        </button>
                    </div>

                    <!-- Video untuk kamera -->
                    <div id="scanner-container" class="d-none">
                        <div class="scanner-wrapper position-relative">
                            <video id="qr-video" class="w-100 rounded" style="max-height: 400px; object-fit: cover;"></video>
                            <!-- QR Scanner Overlay -->
                            <div class="qr-scanner-overlay">
                                <div class="qr-scanner-box">
                                    <div class="qr-corner qr-corner-top-left"></div>
                                    <div class="qr-corner qr-corner-top-right"></div>
                                    <div class="qr-corner qr-corner-bottom-left"></div>
                                    <div class="qr-corner qr-corner-bottom-right"></div>
                                    <div class="qr-scanner-line"></div>
                                </div>
                                <div class="qr-scanner-text">
                                    <p class="text-white text-center mb-1">
                                        <span id="scanner-status">Arahkan QR Code ke dalam kotak</span>
                                    </p>
                                    <small class="text-white-50 d-block text-center">
                                        <span id="scanner-tips">Pastikan QR Code terlihat jelas dan tidak buram</span>
                                    </small>
                                </div>
                            </div>
                        </div>
                        <canvas id="qr-canvas" class="d-none"></canvas>
                    </div>

                    <!-- Manual Input -->
                    <div class="mt-3">
                        <div class="input-group">
                            <input type="text" class="form-control" id="manual_qr" placeholder="Atau masukkan kode QR manual">
                            <button class="btn btn-primary" type="button" id="submitManual">
                                <i class="fas fa-paper-plane me-1"></i>
                                Submit
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Alert Container -->
                <div id="alert-container" class="mt-3"></div>

                <!-- Result Container -->
                <div id="result-container" class="d-none">
                    <div class="scanner-result">
                        <h5 class="text-center mb-3">
                            <i class="fas fa-check-circle me-2"></i>
                            Data Siswa
                        </h5>
                        <div class="row">
                            <div class="col-6">
                                <strong>NIS:</strong> <span id="result-nis"></span><br>
                                <strong>Nama:</strong> <span id="result-nama"></span><br>
                                <strong>Kelas:</strong> <span id="result-kelas"></span>
                            </div>
                            <div class="col-6">
                                <strong>Jurusan:</strong> <span id="result-jurusan"></span><br>
                                <strong>Jam:</strong> <span id="result-jam"></span><br>
                                <strong>Status:</strong> <span id="result-status" class="status-badge"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistik Hari Ini -->
        <div class="card card-custom mt-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    Statistik Absensi Hari Ini
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="p-3">
                            <i class="fas fa-user-check fa-2x text-success mb-2"></i>
                            <h4 class="text-success" id="stat-hadir">0</h4>
                            <small class="text-muted">Hadir</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3">
                            <i class="fas fa-user-clock fa-2x text-warning mb-2"></i>
                            <h4 class="text-warning" id="stat-telat">0</h4>
                            <small class="text-muted">Telat</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3">
                            <i class="fas fa-user-times fa-2x text-danger mb-2"></i>
                            <h4 class="text-danger" id="stat-alpha">0</h4>
                            <small class="text-muted">Alpha</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3">
                            <i class="fas fa-users fa-2x text-primary mb-2"></i>
                            <h4 class="text-primary" id="stat-total">0</h4>
                            <small class="text-muted">Total</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* QR Scanner Styles */
.scanner-wrapper {
    border-radius: 8px;
    overflow: hidden;
    background: #000;
}

.qr-scanner-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.5);
    z-index: 10;
}

.qr-scanner-box {
    position: relative;
    width: 250px;
    height: 250px;
    margin-bottom: 20px;
}

.qr-corner {
    position: absolute;
    width: 30px;
    height: 30px;
    border: 3px solid #00ff00;
}

.qr-corner-top-left {
    top: 0;
    left: 0;
    border-right: none;
    border-bottom: none;
}

.qr-corner-top-right {
    top: 0;
    right: 0;
    border-left: none;
    border-bottom: none;
}

.qr-corner-bottom-left {
    bottom: 0;
    left: 0;
    border-right: none;
    border-top: none;
}

.qr-corner-bottom-right {
    bottom: 0;
    right: 0;
    border-left: none;
    border-top: none;
}

.qr-scanner-line {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg, transparent, #00ff00, transparent);
    animation: scanLine 2s linear infinite;
}

@keyframes scanLine {
    0% {
        top: 0;
        opacity: 1;
    }
    50% {
        opacity: 1;
    }
    100% {
        top: 248px;
        opacity: 0;
    }
}

.qr-scanner-text {
    text-align: center;
}

.qr-scanner-text p {
    font-size: 16px;
    font-weight: 500;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.8);
}

.qr-scanner-text small {
    font-size: 12px;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.8);
}

/* Pulsing animation for corners */
.qr-corner {
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0% {
        border-color: #00ff00;
        box-shadow: 0 0 5px #00ff00;
    }
    50% {
        border-color: #00ff88;
        box-shadow: 0 0 15px #00ff00;
    }
    100% {
        border-color: #00ff00;
        box-shadow: 0 0 5px #00ff00;
    }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .qr-scanner-box {
        width: 200px;
        height: 200px;
    }
    
    .qr-scanner-line {
        height: 2px;
    }
    
    @keyframes scanLine {
        0% {
            top: 0;
            opacity: 1;
        }
        50% {
            opacity: 1;
        }
        100% {
            top: 198px;
            opacity: 0;
        }
    }
}

/* Scanner container improvements */
#scanner-container {
    max-width: 500px;
    margin: 0 auto;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    border-radius: 8px;
    overflow: hidden;
}

/* Status badges */
.status-hadir {
    background-color: #28a745;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
}

.status-telat {
    background-color: #ffc107;
    color: #212529;
    padding: 4px 8px;
    border-radius: 4px;
}

.status-alpha {
    background-color: #dc3545;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
}

/* Result container styling */
.scanner-result {
    background: #ffffff;
    border: 2px solid #28a745;
    border-radius: 12px;
    padding: 25px;
    margin-top: 15px;
    box-shadow: 0 4px 20px rgba(40, 167, 69, 0.15);
}

.scanner-result h5 {
    color: #28a745;
    font-weight: 600;
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 10px;
    margin-bottom: 20px;
}

.scanner-result .row {
    margin: 0;
}

.scanner-result .col-6 {
    padding: 10px 15px;
}

.scanner-result strong {
    color: #495057;
    font-weight: 600;
    display: inline-block;
    min-width: 80px;
}

.scanner-result span:not(.status-badge) {
    color: #212529;
    font-weight: 500;
}

.scanner-result br {
    margin-bottom: 8px;
}

/* Data field styling */
.scanner-result .col-6 > div {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 10px;
    border-left: 4px solid #28a745;
}

.scanner-result .col-6:first-child > div {
    border-left-color: #007bff;
}

.scanner-result .col-6:last-child > div {
    border-left-color: #6f42c1;
}

/* Button improvements */
.btn-custom {
    border-radius: 25px;
    padding: 10px 20px;
    font-weight: 600;
}

/* Alert improvements */
.alert-custom {
    border-radius: 8px;
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script>
$(document).ready(function() {
    let scanner = null;
    let scanning = false;
    let scanStats = {
        frameCount: 0,
        lastDetectionTime: 0,
        noDetectionCount: 0
    };
    
    // Setup CSRF token for AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Load statistik saat halaman dimuat
    loadStatistik();

    // Start QR Scanner
    $('#startScan').click(function() {
        if (!validateForm()) return;
        startQRScanner();
    });

    // Stop QR Scanner
    $('#stopScan').click(function() {
        stopQRScanner();
    });

    // Manual QR Submit
    $('#submitManual').click(function() {
        if (!validateForm()) return;
        
        const qrCode = $('#manual_qr').val().trim();
        if (!qrCode) {
            showAlert('danger', 'Masukkan kode QR terlebih dahulu!');
            return;
        }
        
        processQRCode(qrCode);
    });

    // Enter key pada manual input
    $('#manual_qr').keypress(function(e) {
        if (e.which === 13) {
            $('#submitManual').click();
        }
    });

    function validateForm() {
        const jamSekolahId = $('#jam_sekolah_id').val();
        const absenType = $('#absen_type').val();
        
        if (!jamSekolahId) {
            showAlert('warning', 'Pilih sesi sekolah terlebih dahulu!');
            return false;
        }
        
        if (!absenType) {
            showAlert('warning', 'Pilih jenis absensi terlebih dahulu!');
            return false;
        }
        
        return true;
    }

    function startQRScanner() {
        const video = document.getElementById('qr-video');
        const canvas = document.getElementById('qr-canvas');
        const context = canvas.getContext('2d');
        
        // Request camera dengan preferensi back camera
        const constraints = {
            video: {
                facingMode: { ideal: "environment" },
                width: { ideal: 1280 },
                height: { ideal: 720 }
            }
        };
        
        navigator.mediaDevices.getUserMedia(constraints)
        .then(function(stream) {
            video.srcObject = stream;
            video.play();
            scanning = true;
            
            // Reset scan statistics
            scanStats = {
                frameCount: 0,
                lastDetectionTime: 0,
                noDetectionCount: 0
            };
            
            $('#startScan').addClass('d-none');
            $('#stopScan').removeClass('d-none');
            $('#scanner-container').removeClass('d-none');
            
            // Wait for video to be ready
            video.addEventListener('loadedmetadata', function() {
                scanQRCode(video, canvas, context);
            });
            
            showAlert('info', 'Kamera aktif! Arahkan QR Code ke dalam kotak hijau.', true);
        })
        .catch(function(err) {
            console.error("Error accessing camera: ", err);
            let errorMsg = 'Tidak dapat mengakses kamera. ';
            
            if (err.name === 'NotAllowedError') {
                errorMsg += 'Silakan izinkan akses kamera di browser.';
            } else if (err.name === 'NotFoundError') {
                errorMsg += 'Kamera tidak ditemukan.';
            } else if (err.name === 'NotReadableError') {
                errorMsg += 'Kamera sedang digunakan aplikasi lain.';
            } else {
                errorMsg += 'Pastikan kamera tersedia dan izin diberikan.';
            }
            
            showAlert('danger', errorMsg);
        });
    }

    function stopQRScanner() {
        const video = document.getElementById('qr-video');
        
        if (video.srcObject) {
            video.srcObject.getTracks().forEach(track => track.stop());
        }
        
        scanning = false;
        $('#startScan').removeClass('d-none');
        $('#stopScan').addClass('d-none');
        $('#scanner-container').addClass('d-none');
    }

    function scanQRCode(video, canvas, context) {
        if (!scanning) return;
        
        scanStats.frameCount++;
        
        if (video.readyState === video.HAVE_ENOUGH_DATA) {
            canvas.height = video.videoHeight;
            canvas.width = video.videoWidth;
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            
            const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
            
            // Try multiple detection methods for better accuracy
            const code = jsQR(imageData.data, imageData.width, imageData.height, {
                inversionAttempts: "dontInvert",
            });
            
            // Update scanner status
            if (code) {
                $('#scanner-status').text('QR Code terdeteksi! Memproses...');
                $('#scanner-tips').text('Tetap tahan posisi QR Code');
                
                // Play success sound
                playSuccessSound();
                
                // Visual feedback
                flashScreen();
                
                // Process the QR code
                processQRCode(code.data);
                stopQRScanner();
                return;
            } else {
                scanStats.noDetectionCount++;
                
                // Provide helpful tips based on scanning duration
                if (scanStats.frameCount % 60 === 0) { // Every 2 seconds (30fps)
                    if (scanStats.noDetectionCount > 180) { // After 6 seconds
                        $('#scanner-status').text('QR Code tidak terdeteksi');
                        $('#scanner-tips').text('Coba pindahkan posisi atau periksa pencahayaan');
                    } else if (scanStats.noDetectionCount > 90) { // After 3 seconds
                        $('#scanner-status').text('Mencari QR Code...');
                        $('#scanner-tips').text('Pastikan QR Code berada di dalam kotak hijau');
                    }
                }
            }
        }
        
        requestAnimationFrame(() => scanQRCode(video, canvas, context));
    }
    
    function playSuccessSound() {
        // Create audio context for beep sound
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            oscillator.frequency.value = 800;
            oscillator.type = 'sine';
            
            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
            
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.5);
        } catch (e) {
            console.log('Audio context not supported');
        }
    }
    
    function flashScreen() {
        // Add white flash effect
        const flashDiv = $('<div>').css({
            position: 'fixed',
            top: 0,
            left: 0,
            width: '100%',
            height: '100%',
            backgroundColor: 'white',
            opacity: 0.8,
            zIndex: 9999,
            pointerEvents: 'none'
        });
        
        $('body').append(flashDiv);
        
        flashDiv.animate({ opacity: 0 }, 300, function() {
            flashDiv.remove();
        });
    }

    function processQRCode(qrCode) {
        const jamSekolahId = $('#jam_sekolah_id').val();
        const type = $('#absen_type').val();
        
        // Show processing state
        showAlert('info', '<i class="fas fa-spinner fa-spin me-2"></i>Memproses absensi...', false);
        
        $.ajax({
            url: '{{ route("absensi.scan") }}',
            method: 'POST',
            data: {
                qr_code: qrCode,
                jam_sekolah_id: jamSekolahId,
                type: type
            },
            timeout: 15000, // 15 second timeout
            success: function(response) {
                if (response.success) {
                    showAlert('success', `<i class="fas fa-check-circle me-2"></i>${response.message}`, true);
                    showResult(response.data);
                    loadStatistik();
                    $('#manual_qr').val('');
                    
                    // Auto-scroll to result
                    $('html, body').animate({
                        scrollTop: $("#result-container").offset().top - 100
                    }, 500);
                } else {
                    showAlert('danger', `<i class="fas fa-exclamation-triangle me-2"></i>${response.message}`, false);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText,
                    error: error
                });
                
                let errorMessage = 'Terjadi kesalahan saat memproses absensi.';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (status === 'timeout') {
                    errorMessage = 'Request timeout. Periksa koneksi internet dan coba lagi.';
                } else if (status === 'parsererror') {
                    errorMessage = 'Error parsing response. Silakan refresh halaman dan coba lagi.';
                } else if (xhr.status === 0) {
                    errorMessage = 'Tidak ada koneksi. Periksa koneksi internet Anda.';
                } else if (xhr.status >= 500) {
                    errorMessage = 'Server error. Silakan coba lagi dalam beberapa saat.';
                } else if (xhr.status === 404) {
                    errorMessage = 'Endpoint tidak ditemukan. Hubungi administrator.';
                }
                
                showAlert('danger', `<i class="fas fa-exclamation-triangle me-2"></i>${errorMessage}`, false);
            }
        });
    }

    function showResult(data) {
        $('#result-nis').text(data.nis);
        $('#result-nama').text(data.nama);
        $('#result-kelas').text(data.kelas);
        $('#result-jurusan').text(data.jurusan);
        $('#result-jam').text(data.jam_masuk || data.jam_keluar);
        
        const statusBadge = $('#result-status');
        statusBadge.text(data.status)
                  .removeClass('status-hadir status-telat status-alpha')
                  .addClass('status-' + data.status);
        
        $('#result-container').removeClass('d-none');
        
        // Auto hide result after 10 seconds
        setTimeout(() => {
            $('#result-container').addClass('d-none');
        }, 10000);
    }

    function showAlert(type, message, autoHide = true) {
        const alertHtml = `
            <div class="alert alert-${type} alert-custom alert-dismissible fade show" role="alert">
                <i class="fas fa-${getAlertIcon(type)} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        $('#alert-container').html(alertHtml);
        
        if (autoHide && type !== 'danger') {
            setTimeout(() => {
                $('.alert').alert('close');
            }, 5000);
        }
    }

    function getAlertIcon(type) {
        switch(type) {
            case 'success': return 'check-circle';
            case 'danger': return 'exclamation-triangle';
            case 'warning': return 'exclamation-circle';
            case 'info': return 'info-circle';
            default: return 'info-circle';
        }
    }

    function loadStatistik() {
        // Implementasi AJAX untuk load statistik hari ini
        // Untuk sekarang, gunakan data dummy
        $('#stat-hadir').text('25');
        $('#stat-telat').text('5');
        $('#stat-alpha').text('2');
        $('#stat-total').text('32');
    }
});
</script>
@endpush
