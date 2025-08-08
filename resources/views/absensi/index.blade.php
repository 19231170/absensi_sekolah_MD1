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
                                    <!-- QR Code guide lines -->
                                    <div class="qr-guide-lines">
                                        <div class="guide-line guide-line-h"></div>
                                        <div class="guide-line guide-line-v"></div>
                                    </div>
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
    border-radius: 12px;
    overflow: hidden;
    background: #000;
    position: relative;
    box-shadow: 0 8px 25px rgba(0,0,0,0.3);
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
    background: rgba(0, 0, 0, 0.4);
    z-index: 10;
}

.qr-scanner-box {
    position: relative;
    width: 260px;
    height: 260px;
    margin-bottom: 30px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(5px);
}

.qr-corner {
    position: absolute;
    width: 35px;
    height: 35px;
    border: 4px solid #00ff88;
    animation: pulse 1.5s ease-in-out infinite;
    border-radius: 4px;
}

.qr-corner-top-left {
    top: -2px;
    left: -2px;
    border-right: none;
    border-bottom: none;
    border-top-left-radius: 8px;
}

.qr-corner-top-right {
    top: -2px;
    right: -2px;
    border-left: none;
    border-bottom: none;
    border-top-right-radius: 8px;
}

.qr-corner-bottom-left {
    bottom: -2px;
    left: -2px;
    border-right: none;
    border-top: none;
    border-bottom-left-radius: 8px;
}

.qr-corner-bottom-right {
    bottom: -2px;
    right: -2px;
    border-left: none;
    border-top: none;
    border-bottom-right-radius: 8px;
}

.qr-scanner-line {
    position: absolute;
    top: 0;
    left: 10px;
    right: 10px;
    height: 3px;
    background: linear-gradient(90deg, transparent, #00ff88, #00ff88, transparent);
    border-radius: 2px;
    animation: scanLine 2s linear infinite;
    box-shadow: 0 0 10px #00ff88;
}

.qr-scanner-text {
    text-align: center;
    color: white;
    text-shadow: 0 2px 4px rgba(0,0,0,0.5);
}

.qr-scanner-text p {
    margin: 0 0 5px 0;
    font-size: 16px;
    font-weight: 500;
}

.qr-scanner-text small {
    font-size: 13px;
    opacity: 0.8;
    display: block;
}

@keyframes scanLine {
    0% { 
        top: 10px; 
        opacity: 0; 
        transform: scaleX(0.5);
    }
    25% { 
        opacity: 1; 
        transform: scaleX(1);
    }
    75% { 
        opacity: 1; 
        transform: scaleX(1);
    }
    100% { 
        top: 240px; 
        opacity: 0; 
        transform: scaleX(0.5);
    }
}

@keyframes pulse {
    0% { 
        border-color: #00ff88; 
        box-shadow: 0 0 5px #00ff88, inset 0 0 5px rgba(0,255,136,0.2); 
        transform: scale(1);
    }
    50% { 
        border-color: #00ffaa; 
        box-shadow: 0 0 20px #00ff88, inset 0 0 10px rgba(0,255,136,0.3); 
        transform: scale(1.05);
    }
    100% { 
        border-color: #00ff88; 
        box-shadow: 0 0 5px #00ff88, inset 0 0 5px rgba(0,255,136,0.2); 
        transform: scale(1);
    }
}

/* Enhanced video display */
#qr-video {
    width: 100%;
    height: auto;
    max-height: 400px;
    object-fit: cover;
    border-radius: 12px;
}

/* QR Code guide lines for better positioning */
.qr-guide-lines {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 80%;
    height: 80%;
    pointer-events: none;
}

.guide-line {
    position: absolute;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 1px;
}

.guide-line-h {
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    transform: translateY(-50%);
}

.guide-line-v {
    left: 50%;
    top: 0;
    bottom: 0;
    width: 1px;
    transform: translateX(-50%);
}

/* Torch button styles */
#toggleTorch {
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255,255,255,0.3);
}

#toggleTorch:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(255,255,255,0.2);
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
        width: 220px;
        height: 220px;
    }
    
    .qr-corner {
        width: 28px;
        height: 28px;
        border-width: 3px;
    }
    
    .qr-scanner-line {
        height: 2px;
    }
    
    @keyframes scanLine {
        0% { 
            top: 8px; 
            opacity: 0; 
            transform: scaleX(0.5);
        }
        25% { 
            opacity: 1; 
            transform: scaleX(1);
        }
        75% { 
            opacity: 1; 
            transform: scaleX(1);
        }
        100% { 
            top: 205px; 
            opacity: 0; 
            transform: scaleX(0.5);
        }
    }
    
    #qr-video {
        max-height: 320px;
    }
    
    .qr-scanner-text p {
        font-size: 14px;
    }
    
    .qr-scanner-text small {
        font-size: 12px;
    }
}

@media (max-width: 480px) {
    .qr-scanner-box {
        width: 180px;
        height: 180px;
    }
    
    .qr-corner {
        width: 25px;
        height: 25px;
        border-width: 2px;
    }
    
    @keyframes scanLine {
        0% { 
            top: 8px; 
            opacity: 0; 
            transform: scaleX(0.5);
        }
        25% { 
            opacity: 1; 
            transform: scaleX(1);
        }
        75% { 
            opacity: 1; 
            transform: scaleX(1);
        }
        100% { 
            top: 165px; 
            opacity: 0; 
            transform: scaleX(0.5);
        }
    }
    
    #qr-video {
        max-height: 280px;
    }
    
    .qr-scanner-text p {
        font-size: 13px;
    }
    
    .qr-scanner-text small {
        font-size: 11px;
    }
    
    .btn-custom {
        padding: 8px 16px;
        font-size: 14px;
    }
}

/* High DPI displays */
@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
    .qr-scanner-line {
        height: 4px;
    }
    
    .qr-corner {
        border-width: 5px;
    }
}

/* Landscape orientation optimizations */
@media (orientation: landscape) and (max-height: 500px) {
    .qr-scanner-box {
        width: 160px;
        height: 160px;
    }
    
    #qr-video {
        max-height: 220px;
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
        
        // Enhanced camera constraints for optimal QR detection
        const constraints = {
            video: {
                facingMode: { ideal: "environment" },
                width: { ideal: 1920, min: 640 },
                height: { ideal: 1080, min: 480 },
                frameRate: { ideal: 30, min: 15 },
                focusMode: "continuous",
                exposureMode: "continuous",
                whiteBalanceMode: "continuous"
            }
        };
        
        navigator.mediaDevices.getUserMedia(constraints)
        .then(function(stream) {
            video.srcObject = stream;
            video.play();
            scanning = true;
            
            // Apply advanced camera settings for better QR detection
            const track = stream.getVideoTracks()[0];
            if (track.getCapabilities) {
                const capabilities = track.getCapabilities();
                const settings = {};
                
                // Optimize camera settings for QR code scanning
                if (capabilities.focusMode && capabilities.focusMode.includes('continuous')) {
                    settings.focusMode = 'continuous';
                }
                if (capabilities.exposureMode && capabilities.exposureMode.includes('continuous')) {
                    settings.exposureMode = 'continuous';
                }
                if (capabilities.whiteBalanceMode && capabilities.whiteBalanceMode.includes('continuous')) {
                    settings.whiteBalanceMode = 'continuous';
                }
                
                if (Object.keys(settings).length > 0) {
                    track.applyConstraints({ advanced: [settings] }).catch(console.warn);
                }
            }
            
            // Reset scan statistics
            scanStats = {
                frameCount: 0,
                lastDetectionTime: 0,
                noDetectionCount: 0,
                enhancedAttempts: 0
            };
            
            $('#startScan').addClass('d-none');
            $('#stopScan').removeClass('d-none');
            $('#scanner-container').removeClass('d-none');
            
            // Add torch/flash button if supported
            addTorchButton(stream);
            
            // Wait for video to be ready and start scanning
            video.addEventListener('loadedmetadata', function() {
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
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
            // Ensure canvas matches video dimensions
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            
            // Draw current video frame
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            
            // Get image data for QR detection
            const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
            
            // Enhanced QR detection with multiple strategies
            let code = null;
            
            // Strategy 1: Standard detection with both inversions
            code = jsQR(imageData.data, imageData.width, imageData.height, {
                inversionAttempts: "attemptBoth"
            });
            
            // Strategy 2: Focus on center region if no detection
            if (!code && canvas.width > 400 && canvas.height > 400) {
                const centerW = canvas.width * 0.6;
                const centerH = canvas.height * 0.6;
                const offsetX = (canvas.width - centerW) / 2;
                const offsetY = (canvas.height - centerH) / 2;
                
                const centerData = context.getImageData(offsetX, offsetY, centerW, centerH);
                code = jsQR(centerData.data, centerData.width, centerData.height, {
                    inversionAttempts: "attemptBoth"
                });
            }
            
            // Strategy 3: Image enhancement after several failed attempts
            if (!code && scanStats.frameCount % 15 === 0) { // Try every 0.5 seconds
                scanStats.enhancedAttempts++;
                code = enhanceImageAndScan(context, canvas, imageData);
            }
            
            // Update scanner status and provide real-time feedback
            if (code) {
                $('#scanner-status').text('QR Code terdeteksi! Memproses...');
                $('#scanner-tips').text('Berhasil! Sedang memproses data...');
                
                // Success feedback
                playSuccessSound();
                flashScreen();
                
                // Process the detected QR code
                processQRCode(code.data);
                stopQRScanner();
                return;
            } else {
                scanStats.noDetectionCount++;
                
                // Provide dynamic guidance based on scan duration
                if (scanStats.frameCount % 30 === 0) { // Every second at 30fps
                    updateScannerGuidance();
                }
            }
        }
        
        // Continue scanning with optimized frame rate
        requestAnimationFrame(() => scanQRCode(video, canvas, context));
    }
    
    function enhanceImageAndScan(context, canvas, originalData) {
        // Create enhanced image data for better QR detection
        const enhancedData = context.createImageData(canvas.width, canvas.height);
        const data = enhancedData.data;
        const original = originalData.data;
        
        // Apply multiple enhancement techniques
        for (let i = 0; i < original.length; i += 4) {
            // Convert to grayscale with weighted average
            const gray = 0.299 * original[i] + 0.587 * original[i + 1] + 0.114 * original[i + 2];
            
            // Apply adaptive threshold for better contrast
            const threshold = 128;
            const enhanced = gray > threshold ? 255 : 0;
            
            data[i] = enhanced;     // R
            data[i + 1] = enhanced; // G  
            data[i + 2] = enhanced; // B
            data[i + 3] = 255;      // A
        }
        
        // Try scanning the enhanced image
        return jsQR(data, canvas.width, canvas.height, {
            inversionAttempts: "attemptBoth"
        });
    }
    
    function updateScannerGuidance() {
        const duration = scanStats.frameCount / 30; // Approximate seconds
        
        if (duration > 10) {
            $('#scanner-status').text('QR Code tidak terdeteksi');
            $('#scanner-tips').text('Coba gunakan flash atau periksa kualitas QR Code');
        } else if (duration > 6) {
            $('#scanner-status').text('Masih mencari...');
            $('#scanner-tips').text('Coba pindahkan posisi atau periksa pencahayaan');
        } else if (duration > 3) {
            $('#scanner-status').text('Mencari QR Code...');
            $('#scanner-tips').text('Pastikan QR Code berada di dalam kotak hijau');
        } else {
            $('#scanner-status').text('Siap memindai');
            $('#scanner-tips').text('Arahkan QR Code ke tengah kotak hijau');
        }
    }
    
    function addTorchButton(stream) {
        // Check if torch/flash is supported
        const track = stream.getVideoTracks()[0];
        if (track.getCapabilities && track.getCapabilities().torch) {
            const torchButton = `
                <div class="text-center mt-2">
                    <button type="button" class="btn btn-outline-light btn-sm" id="toggleTorch">
                        <i class="fas fa-lightbulb me-1"></i> Flash
                    </button>
                </div>
            `;
            $('.qr-scanner-text').append(torchButton);
            
            let torchOn = false;
            $('#toggleTorch').click(function() {
                torchOn = !torchOn;
                track.applyConstraints({
                    advanced: [{ torch: torchOn }]
                }).then(() => {
                    const icon = $(this).find('i');
                    if (torchOn) {
                        icon.removeClass('fa-lightbulb').addClass('fa-lightbulb-on text-warning');
                        $(this).addClass('btn-warning').removeClass('btn-outline-light');
                    } else {
                        icon.removeClass('fa-lightbulb-on text-warning').addClass('fa-lightbulb');
                        $(this).removeClass('btn-warning').addClass('btn-outline-light');
                    }
                }).catch((err) => {
                    console.warn('Torch not supported:', err);
                    showAlert('warning', 'Flash tidak didukung pada perangkat ini', true);
                });
            });
        }
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
