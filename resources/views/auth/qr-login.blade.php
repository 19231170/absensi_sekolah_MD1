@extends('layouts.app')

@section('title', 'Login QR Code - Staff')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card card-custom">
            <div class="card-header bg-primary text-white text-center">
                <h4 class="mb-0">
                    <i class="fas fa-qrcode me-2"></i>
                    Login Staff
                </h4>
                <small class="mt-1 d-block opacity-75">
                    <i class="fas fa-shield-alt me-1"></i>
                    Khusus Guru & Administrator
                </small>
            </div>
            <div class="card-body p-4">
                <!-- Step 1: QR Scanner -->
                <div id="qr-step" class="step-container">
                    <div class="text-center mb-4">
                        <div class="login-icon mb-3">
                            <i class="fas fa-qrcode fa-4x text-primary"></i>
                        </div>
                        <h5 class="mb-2">Langkah 1: Scan QR Code Anda</h5>
                        <p class="text-muted">Tunjukkan QR Code staff Anda ke kamera</p>
                    </div>

                    <!-- Scanner Container -->
                    <div class="qr-scanner-container">
                        <div class="text-center mb-3">
                            <button type="button" class="btn btn-primary btn-custom" id="startScan">
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
                                <video id="qr-video" class="w-100 rounded" style="max-height: 300px; object-fit: cover;"></video>
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
                                        <small class="text-white-50 text-center">
                                            <span id="scanner-tips">Pastikan QR Code terlihat jelas</span>
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
                </div>

                <!-- Step 2: PIN Input -->
                <div id="pin-step" class="step-container d-none">
                    <div class="text-center mb-4">
                        <div class="user-welcome-card">
                            <div class="user-avatar">
                                <span id="user-initials">?</span>
                            </div>
                            <h5 id="welcome-message" class="mb-1">Selamat datang!</h5>
                            <div class="user-info">
                                <span class="badge bg-primary" id="user-role">Staff</span>
                                <small class="text-muted d-block" id="user-details"></small>
                            </div>
                        </div>
                    </div>

                    <div class="pin-input-section">
                        <h6 class="text-center mb-3">
                            <i class="fas fa-key me-2"></i>
                            Langkah 2: Masukkan PIN Anda
                        </h6>
                        <div class="pin-input-container">
                            <input type="password" 
                                   class="form-control pin-input" 
                                   id="pin-input" 
                                   maxlength="4" 
                                   placeholder="****"
                                   autocomplete="off">
                        </div>
                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-success btn-custom" id="submitPin">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Login
                            </button>
                            <button type="button" class="btn btn-secondary btn-custom ms-2" id="backToQr">
                                <i class="fas fa-arrow-left me-2"></i>
                                Kembali
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Alert Container -->
                <div id="alert-container" class="mt-3"></div>
            </div>
        </div>

        <!-- Help Card -->
        <div class="card card-custom mt-4">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">
                    <i class="fas fa-question-circle me-2"></i>
                    Butuh Bantuan?
                </h6>
                <p class="text-muted small mb-0">
                    Hubungi administrator jika Anda lupa PIN atau QR Code tidak berfungsi.
                </p>
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
    width: 240px;
    height: 240px;
    margin-bottom: 30px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(5px);
}

.qr-corner {
    position: absolute;
    width: 30px;
    height: 30px;
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
    margin: 0;
    font-size: 16px;
    font-weight: 500;
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
        top: 220px; 
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
    max-height: 320px;
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

/* User Welcome Card */
.user-welcome-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 25px;
    border-radius: 15px;
    margin-bottom: 20px;
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

.user-avatar {
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    font-size: 24px;
    font-weight: bold;
    backdrop-filter: blur(10px);
}

.user-info .badge {
    font-size: 0.8rem;
    padding: 5px 10px;
}

/* PIN Input */
.pin-input-container {
    max-width: 200px;
    margin: 0 auto;
}

.pin-input {
    text-align: center;
    font-size: 24px;
    font-weight: bold;
    letter-spacing: 8px;
    height: 60px;
    border: 2px solid #dee2e6;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.pin-input:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    transform: scale(1.05);
}

/* Login Icon Animation */
.login-icon {
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
    40% { transform: translateY(-10px); }
    60% { transform: translateY(-5px); }
}

/* Step Container */
.step-container {
    min-height: 400px;
    transition: all 0.3s ease;
}

/* Button Styles */
.btn-custom {
    border-radius: 25px;
    padding: 12px 25px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-custom:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

/* Responsive */
@media (max-width: 768px) {
    .qr-scanner-box {
        width: 200px;
        height: 200px;
    }
    
    .qr-corner {
        width: 25px;
        height: 25px;
        border-width: 3px;
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
            top: 180px; 
            opacity: 0; 
            transform: scaleX(0.5);
        }
    }
    
    #qr-video {
        max-height: 280px;
    }
    
    .user-welcome-card {
        padding: 20px;
        margin-bottom: 15px;
    }
    
    .pin-input {
        font-size: 20px;
        height: 50px;
        letter-spacing: 6px;
    }
    
    .qr-scanner-text p {
        font-size: 14px;
    }
}

@media (max-width: 480px) {
    .qr-scanner-box {
        width: 180px;
        height: 180px;
    }
    
    .qr-corner {
        width: 20px;
        height: 20px;
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
        max-height: 250px;
    }
    
    .pin-input {
        font-size: 18px;
        height: 45px;
        letter-spacing: 4px;
    }
    
    .qr-scanner-text p {
        font-size: 13px;
    }
    
    .btn-custom {
        padding: 10px 20px;
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
        max-height: 200px;
    }
    
    .step-container {
        min-height: 300px;
    }
}

/* Alert Custom Styles */
.alert-custom {
    border-radius: 10px;
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
    
    // Setup CSRF token for AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Start QR Scanner
    $('#startScan').click(function() {
        startQRScanner();
    });

    // Stop QR Scanner
    $('#stopScan').click(function() {
        stopQRScanner();
    });

    // Manual QR Submit
    $('#submitManual').click(function() {
        const qrCode = $('#manual_qr').val().trim();
        if (!qrCode) {
            showAlert('warning', 'Masukkan kode QR terlebih dahulu!');
            return;
        }
        processQRCode(qrCode);
    });

    // Submit PIN
    $('#submitPin').click(function() {
        const pin = $('#pin-input').val().trim();
        if (!pin || pin.length !== 4) {
            showAlert('warning', 'PIN harus 4 digit!');
            return;
        }
        verifyPin(pin);
    });

    // Back to QR step
    $('#backToQr').click(function() {
        clearSession();
        showStep('qr');
    });

    // Enter key handlers
    $('#manual_qr').keypress(function(e) {
        if (e.which === 13) {
            $('#submitManual').click();
        }
    });

    $('#pin-input').keypress(function(e) {
        if (e.which === 13) {
            $('#submitPin').click();
        }
    });

    // Only allow numbers in PIN input
    $('#pin-input').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    function startQRScanner() {
        const video = document.getElementById('qr-video');
        const canvas = document.getElementById('qr-canvas');
        const context = canvas.getContext('2d');
        
        // Enhanced camera constraints for better QR detection
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
            
            // Apply advanced camera settings if supported
            const track = stream.getVideoTracks()[0];
            if (track.getCapabilities) {
                const capabilities = track.getCapabilities();
                const settings = {};
                
                // Set optimal focus for QR scanning
                if (capabilities.focusMode && capabilities.focusMode.includes('continuous')) {
                    settings.focusMode = 'continuous';
                }
                if (capabilities.exposureMode && capabilities.exposureMode.includes('continuous')) {
                    settings.exposureMode = 'continuous';
                }
                if (capabilities.torch) {
                    // Enable torch/flashlight if available
                    settings.torch = false; // Start with torch off
                }
                
                if (Object.keys(settings).length > 0) {
                    track.applyConstraints({ advanced: [settings] }).catch(console.warn);
                }
            }
            
            $('#startScan').addClass('d-none');
            $('#stopScan').removeClass('d-none');
            $('#scanner-container').removeClass('d-none');
            
            // Add torch toggle button if supported
            addTorchButton(stream);
            
            video.addEventListener('loadedmetadata', function() {
                // Optimize video display for better scanning
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                scanQRCode(video, canvas, context);
            });
            
            showAlert('info', 'Kamera aktif! Arahkan QR Code staff ke dalam kotak hijau.', true);
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
        
        if (video.readyState === video.HAVE_ENOUGH_DATA) {
            // Set canvas dimensions to match video
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            
            // Draw current frame
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            
            // Get image data for QR detection
            const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
            
            // Enhanced QR detection with multiple attempts
            let code = null;
            
            // Try normal detection first
            code = jsQR(imageData.data, imageData.width, imageData.height, {
                inversionAttempts: "attemptBoth"
            });
            
            // If not found, try with different scan regions (center focus)
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
            
            // If still not found, try with image enhancement
            if (!code) {
                enhanceImageAndScan(context, canvas, imageData);
            }
            
            if (code) {
                $('#scanner-status').text('QR Code terdeteksi! Memproses...');
                
                // Visual and audio feedback
                playSuccessSound();
                flashSuccess();
                
                processQRCode(code.data);
                stopQRScanner();
                return;
            } else {
                // Provide scanning guidance
                $('#scanner-status').text('Mencari QR Code...');
            }
        }
        
        // Continue scanning with optimal frame rate
        requestAnimationFrame(() => scanQRCode(video, canvas, context));
    }
    
    function enhanceImageAndScan(context, canvas, originalData) {
        // Create enhanced version with better contrast
        const enhancedData = context.createImageData(canvas.width, canvas.height);
        const data = enhancedData.data;
        const original = originalData.data;
        
        for (let i = 0; i < original.length; i += 4) {
            // Convert to grayscale and enhance contrast
            const gray = 0.299 * original[i] + 0.587 * original[i + 1] + 0.114 * original[i + 2];
            const enhanced = gray > 128 ? 255 : 0; // High contrast threshold
            
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
    
    function playSuccessSound() {
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            oscillator.frequency.value = 800;
            oscillator.type = 'sine';
            
            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
            
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.3);
        } catch (e) {
            console.log('Audio not supported');
        }
    }
    
    function flashSuccess() {
        const overlay = $('.qr-scanner-overlay');
        overlay.css({
            'background': 'rgba(0, 255, 0, 0.3)',
            'transition': 'background 0.3s ease'
        });
        
        setTimeout(() => {
            overlay.css('background', 'rgba(0, 0, 0, 0.5)');
        }, 300);
    }
    
    function addTorchButton(stream) {
        const track = stream.getVideoTracks()[0];
        if (track.getCapabilities && track.getCapabilities().torch) {
            const torchButton = `
                <button type="button" class="btn btn-outline-light btn-sm mt-2" id="toggleTorch">
                    <i class="fas fa-lightbulb me-1"></i> Flash
                </button>
            `;
            $('.qr-scanner-text').append(torchButton);
            
            let torchOn = false;
            $('#toggleTorch').click(function() {
                torchOn = !torchOn;
                track.applyConstraints({
                    advanced: [{ torch: torchOn }]
                }).then(() => {
                    $(this).find('i').toggleClass('fa-lightbulb fas-regular', !torchOn)
                           .toggleClass('fa-lightbulb-on text-warning', torchOn);
                }).catch(console.warn);
            });
        }
    }

    function processQRCode(qrCode) {
        showAlert('info', '<i class="fas fa-spinner fa-spin me-2"></i>Memproses QR Code...', false);
        
        $.ajax({
            url: '{{ route("qr.login.scan") }}',
            method: 'POST',
            data: { qr_code: qrCode },
            success: function(response) {
                if (response.success) {
                    showUserInfo(response.data);
                    showStep('pin');
                    showAlert('success', response.message, true);
                } else {
                    showAlert('danger', response.message, false);
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Terjadi kesalahan saat memproses QR Code.';
                showAlert('danger', message, false);
            }
        });
    }

    function showUserInfo(userData) {
        $('#user-initials').text(userData.initials);
        $('#welcome-message').text(`Selamat datang, ${userData.name}!`);
        $('#user-role').text(userData.role);
        
        let details = '';
        if (userData.nip) details += `NIP: ${userData.nip}`;
        if (userData.mata_pelajaran) details += ` • ${userData.mata_pelajaran}`;
        $('#user-details').text(details);
    }

    function verifyPin(pin) {
        showAlert('info', '<i class="fas fa-spinner fa-spin me-2"></i>Memverifikasi PIN...', false);
        
        $.ajax({
            url: '{{ route("qr.login.pin") }}',
            method: 'POST',
            data: { pin: pin },
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message, false);
                    setTimeout(() => {
                        window.location.href = response.data.redirect_url;
                    }, 1500);
                } else {
                    showAlert('danger', response.message, false);
                    $('#pin-input').val('').focus();
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Terjadi kesalahan saat verifikasi PIN.';
                showAlert('danger', message, false);
                $('#pin-input').val('').focus();
            }
        });
    }

    function showStep(step) {
        $('.step-container').addClass('d-none');
        
        if (step === 'qr') {
            $('#qr-step').removeClass('d-none');
            $('#manual_qr').val('');
        } else if (step === 'pin') {
            $('#pin-step').removeClass('d-none');
            $('#pin-input').val('').focus();
        }
    }

    function clearSession() {
        $.ajax({
            url: '{{ route("qr.login.clear") }}',
            method: 'POST'
        });
    }

    function showAlert(type, message, autoHide = true) {
        const alertHtml = `
            <div class="alert alert-${type} alert-custom alert-dismissible fade show" role="alert">
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
});
</script>
@endpush
