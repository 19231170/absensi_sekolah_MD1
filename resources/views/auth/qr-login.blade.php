@extends('layouts.app')

@section('title', 'Login QR Code - Staff')

@section('content')
<div class="row justify-content-center" id="login-wrapper">
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
                    <div id="scanner-container" class="d-none mb-4">
                        <div class="scanner-wrapper mb-3">
                            <video id="qr-video" playsinline></video>
                            <canvas id="qr-canvas" class="d-none"></canvas>
                            
                            <div class="qr-scanner-overlay">
                                <div class="qr-scanner-box">
                                    <div class="qr-corner qr-corner-top-left"></div>
                                    <div class="qr-corner qr-corner-top-right"></div>
                                    <div class="qr-corner qr-corner-bottom-left"></div>
                                    <div class="qr-corner qr-corner-bottom-right"></div>
                                    <div class="qr-scanner-line"></div>
                                </div>
                                <div class="qr-scanner-text">
                                    <p id="scanner-status">Arahkan QR Code ke dalam kotak</p>
                                </div>
                            </div>
                        </div>

                        <div class="text-center">
                            <button type="button" class="btn btn-danger" id="stopScan">
                                <i class="fas fa-stop-circle me-2"></i> Berhenti Scan
                            </button>
                        </div>
                    </div>

                    <div class="qr-scanner-container">
                        <div class="text-center mb-3">
                            <button type="button" class="btn btn-primary btn-custom" id="startScan">
                                <i class="fas fa-camera me-2"></i>
                                Mulai Scan QR Code
                            </button>
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

/* Torch button styles */
#toggleTorch {
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255,255,255,0.3);
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    padding: 8px 15px;
    border-radius: 30px;
    font-size: 14px;
    cursor: pointer;
    z-index: 15;
    display: flex;
    align-items: center;
    justify-content: center;
}

#toggleTorch:hover {
    transform: translateX(-50%) translateY(-2px);
    box-shadow: 0 4px 15px rgba(255,255,255,0.2);
}

#toggleTorch.active {
    background: rgba(255, 255, 136, 0.3);
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
        0% { top: 10px; opacity: 0; }
        100% { top: 180px; opacity: 0; }
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
        0% { top: 8px; opacity: 0; }
        100% { top: 165px; opacity: 0; }
    }
    
    #qr-video {
        max-height: 250px;
    }
    
    .pin-input {
        font-size: 18px;
        height: 45px;
        letter-spacing: 4px;
    }
    
    .btn-custom {
        padding: 10px 20px;
        font-size: 14px;
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
    // Variables
    let videoStream = null;
    let scanning = false;
    let animationId = null;
    
    // Setup CSRF token for AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Start QR Scanner
    $('#startScan').click(function() {
        initQRScanner();
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
        if (this.value.length > 4) {
            this.value = this.value.substring(0, 4);
        }
    });

    // Check for camera permissions on page load
    checkCameraPermissions();

    // Initialize QR Scanner with enhanced camera access
    function initQRScanner() {
        const video = document.getElementById('qr-video');
        const canvas = document.getElementById('qr-canvas');
        
        // Show scanner container
        $('#scanner-container').removeClass('d-none');
        $('#startScan').addClass('d-none');
        
        // Enhanced camera constraints with fallbacks
        const constraints = {
            video: { 
                facingMode: { ideal: "environment" },  // Prefer back camera
                width: { ideal: 1280, max: 1920 },     // Good resolution
                height: { ideal: 720, max: 1080 },
                frameRate: { ideal: 60, max: 60 }      // Smooth scanning
            }
        };
        
        showAlert('info', '<i class="fas fa-spinner fa-spin me-2"></i>Mengakses kamera...', false);
        
        // Request camera access with proper error handling
        navigator.mediaDevices.getUserMedia(constraints)
            .then(function(stream) {
                console.log('Camera stream obtained successfully');
                
                videoStream = stream;
                video.srcObject = stream;
                video.setAttribute("playsinline", true); // required for iOS
                
                // Wait for video to be ready
                video.onloadedmetadata = function() {
                    console.log('Video metadata loaded');
                    video.play()
                        .then(() => {
                            console.log('Video playing successfully');
                            scanning = true;
                            
                            // Check for torch support
                            checkTorchSupport(stream);
                            
                            // Start scanning loop
                            scanQRCode();
                            
                            showAlert('success', 'Kamera aktif! Arahkan QR Code ke dalam kotak hijau.', true);
                        })
                        .catch(err => {
                            console.error('Error playing video:', err);
                            showAlert('danger', 'Tidak dapat memutar video kamera.', false);
                            stopQRScanner();
                        });
                };
                
                // Handle video errors
                video.onerror = function(err) {
                    console.error('Video error:', err);
                    showAlert('danger', 'Terjadi error pada video kamera.', false);
                    stopQRScanner();
                };
                
            })
            .catch(function(err) {
                console.error("Error accessing camera: ", err);
                let errorMsg = 'Tidak dapat mengakses kamera. ';
                
                if (err.name === 'NotAllowedError') {
                    errorMsg += 'Silakan izinkan akses kamera di browser dan refresh halaman.';
                } else if (err.name === 'NotFoundError') {
                    errorMsg += 'Kamera tidak ditemukan pada perangkat ini.';
                } else if (err.name === 'NotReadableError') {
                    errorMsg += 'Kamera sedang digunakan oleh aplikasi lain.';
                } else if (err.name === 'OverconstrainedError') {
                    errorMsg += 'Konfigurasi kamera tidak didukung. Mencoba dengan pengaturan yang lebih sederhana...';
                    
                    // Try with simpler constraints
                    trySimpleCamera();
                    return;
                } else {
                    errorMsg += 'Error: ' + err.message;
                }
                
                $('#scanner-container').addClass('d-none');
                $('#startScan').removeClass('d-none');
                
                showAlert('danger', errorMsg, false);
            });
    }

    // Try with simpler camera constraints as fallback
    function trySimpleCamera() {
        const video = document.getElementById('qr-video');
        
        const simpleConstraints = {
            video: true  // Basic video access
        };
        
        navigator.mediaDevices.getUserMedia(simpleConstraints)
            .then(function(stream) {
                console.log('Simple camera access successful');
                
                videoStream = stream;
                video.srcObject = stream;
                video.setAttribute("playsinline", true);
                
                video.onloadedmetadata = function() {
                    video.play()
                        .then(() => {
                            scanning = true;
                            scanQRCode();
                            showAlert('success', 'Kamera aktif (mode sederhana)! Arahkan QR Code ke kamera.', true);
                        })
                        .catch(err => {
                            console.error('Error playing simple video:', err);
                            showAlert('danger', 'Tidak dapat memutar video kamera.', false);
                            stopQRScanner();
                        });
                };
            })
            .catch(function(err) {
                console.error("Simple camera access failed: ", err);
                $('#scanner-container').addClass('d-none');
                $('#startScan').removeClass('d-none');
                showAlert('danger', 'Tidak dapat mengakses kamera dengan pengaturan apapun.', false);
            });
    }

    // Enhanced QR Code scanning with better error handling
    function scanQRCode() {
        if (!scanning) return;
        
        const video = document.getElementById('qr-video');
        const canvas = document.getElementById('qr-canvas');
        const context = canvas.getContext('2d');
        
        // Make sure video is ready
        if (video.readyState !== video.HAVE_ENOUGH_DATA) {
            animationId = requestAnimationFrame(scanQRCode);
            return;
        }
        
        try {
            // Set canvas dimensions to match video
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            
            // Draw current frame to canvas
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            
            // Get image data
            const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
            
            // Try to find QR code with enhanced settings
            const code = jsQR(imageData.data, imageData.width, imageData.height, {
                inversionAttempts: "attemptBoth",  // Try both normal and inverted
                locateAttempts: 10,               // More location attempts
                minSize: 50                       // Minimum QR code size
            });
            
            // If QR code found
            if (code && code.data && code.data.trim()) {
                console.log('QR Code detected:', code.data);
                
                // Visual and audio feedback
                playSuccessSound();
                flashSuccess();
                
                // Process the code
                processQRCode(code.data.trim());
                
                // Stop scanning
                stopQRScanner();
                return;
            }
            
        } catch (error) {
            console.error('Error during QR scanning:', error);
        }
        
        // Continue scanning
        animationId = requestAnimationFrame(scanQRCode);
    }

    // Stop QR Scanner with proper cleanup
    function stopQRScanner() {
        scanning = false;
        
        // Cancel animation frame
        if (animationId) {
            cancelAnimationFrame(animationId);
            animationId = null;
        }
        
        // Stop all video tracks
        if (videoStream) {
            videoStream.getTracks().forEach(track => {
                track.stop();
                console.log('Video track stopped:', track.label);
            });
            videoStream = null;
        }
        
        // Clear video element
        const video = document.getElementById('qr-video');
        if (video) {
            video.srcObject = null;
        }
        
        // Remove torch button if it exists
        $('#toggleTorch').remove();
        
        // Reset UI
        $('#scanner-container').addClass('d-none');
        $('#startScan').removeClass('d-none');
        
        console.log('QR Scanner stopped and cleaned up');
    }
    
    // Check for camera permissions
    function checkCameraPermissions() {
        if (navigator.permissions && navigator.permissions.query) {
            navigator.permissions.query({ name: 'camera' })
                .then(function(permissionStatus) {
                    console.log('Camera permission status:', permissionStatus.state);
                    
                    if (permissionStatus.state === 'denied') {
                        showAlert('warning', 'Akses kamera ditolak. Silakan izinkan akses kamera di pengaturan browser.', false);
                    }
                    
                    // Listen for permission changes
                    permissionStatus.addEventListener('change', function() {
                        console.log('Camera permission changed to:', this.state);
                    });
                })
                .catch(function(error) {
                    console.log('Could not check camera permissions:', error);
                });
        }
    }
    
    // Check for torch support
    function checkTorchSupport(stream) {
        const track = stream.getVideoTracks()[0];
        
        if (track && track.getCapabilities && track.getCapabilities().torch) {
            const torchButton = `
                <button id="toggleTorch" type="button">
                    <i class="fas fa-lightbulb me-1"></i> Flash
                </button>
            `;
            
            $('.scanner-wrapper').append(torchButton);
            
            $('#toggleTorch').click(function() {
                const torchOn = $(this).hasClass('active');
                
                track.applyConstraints({
                    advanced: [{ torch: !torchOn }]
                })
                .then(() => {
                    $(this).toggleClass('active');
                    console.log('Torch toggled:', !torchOn);
                })
                .catch(err => {
                    console.error('Torch error:', err);
                    showAlert('warning', 'Flash tidak dapat digunakan pada perangkat ini.', true);
                });
            });
        }
    }
    
    // Play success sound
    function playSuccessSound() {
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            oscillator.type = "sine";
            oscillator.frequency.value = 1200;
            gainNode.gain.value = 0.2;
            
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.2);
        } catch (e) {
            console.log('Audio not supported');
        }
    }
    
    // Flash success animation
    function flashSuccess() {
        const overlay = $('.qr-scanner-overlay');
        overlay.css('background', 'rgba(0, 255, 0, 0.3)');
        
        setTimeout(() => {
            overlay.css('background', 'rgba(0, 0, 0, 0.4)');
        }, 500);
    }
    
    // Process QR Code
    function processQRCode(qrCode) {
        showAlert('info', '<i class="fas fa-spinner fa-spin me-2"></i>Memproses QR Code...', false);
        
        $.ajax({
            url: '{{ route("qr.login.scan") }}',
            method: 'POST',
            data: { qr_code: qrCode },
            success: function(response) {
                if (response.success) {
                    showUserInfo(response.data || response.user);
                    showStep('pin');
                    showAlert('success', response.message || 'QR Code valid! Silakan masukkan PIN Anda.', true);
                } else {
                    showStep('qr');
                    showAlert('danger', response.message || 'QR Code tidak valid', false);
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Terjadi kesalahan saat memproses QR Code.';
                showStep('qr');
                showAlert('danger', message, false);
            }
        });
    }

    // Show user info
    function showUserInfo(userData) {
        // Calculate initials from name
        const nameParts = userData.name ? userData.name.split(' ') : ['?'];
        let initials = nameParts[0][0];
        if (nameParts.length > 1) {
            initials += nameParts[nameParts.length - 1][0];
        }
        initials = initials.toUpperCase();
        
        // Update UI
        $('#user-initials').text(initials);
        $('#welcome-message').text('Selamat datang, ' + (userData.name || ''));
        $('#user-role').text(userData.role || 'Staff');
        
        // Set user details
        let details = '';
        if (userData.nip) details += 'NIP: ' + userData.nip;
        if (userData.mata_pelajaran) {
            if (details) details += ' • ';
            details += userData.mata_pelajaran;
        }
        $('#user-details').text(details);
    }

    // Verify PIN
    function verifyPin(pin) {
        showAlert('info', '<i class="fas fa-spinner fa-spin me-2"></i>Memverifikasi PIN...', false);
        
        $.ajax({
            url: '{{ route("qr.login.pin") }}',
            method: 'POST',
            data: { pin: pin },
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message || 'Login berhasil!', false);
                    
                    // Redirect to dashboard
                    setTimeout(function() {
                        if (response.redirect) { 
                            window.location.href = response.redirect; 
                        } else if (response.data && response.data.redirect_url) { 
                            window.location.href = response.data.redirect_url; 
                        } else { 
                            window.location.href = '/'; 
                        }
                    }, 1500);
                } else {
                    showAlert('danger', response.message || 'PIN tidak valid.', false);
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

    // Clear session
    function clearSession() {
        $.ajax({
            url: '{{ route("qr.login.clear") }}',
            method: 'POST'
        });
    }

    // Show Step (qr or pin)
    function showStep(step) {
        if (step === 'qr') {
            $('#qr-step').removeClass('d-none');
            $('#pin-step').addClass('d-none');
            $('#manual_qr').val('');
        } else if (step === 'pin') {
            $('#qr-step').addClass('d-none');
            $('#pin-step').removeClass('d-none');
            $('#pin-input').val('').focus();
        }
    }

    // Show Alert
    function showAlert(type, message, autoHide = true) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        $('#alert-container').html(alertHtml);
        
        if (autoHide && type !== 'danger') {
            setTimeout(function() {
                $('.alert').fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        }
    }

    // Cleanup when page is unloaded
    $(window).on('beforeunload', function() {
        stopQRScanner();
    });
});
</script>
@endpush
