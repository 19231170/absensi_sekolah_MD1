@extends('layouts.app')

@push('styles')
<style>
/* QR Scanner Styles - Sama seperti login page */
.scanner-wrapper {
    position: relative;
    background: #1a1a1a;
    border-radius: 15px;
    overflow: hidden;
    margin: 20px auto;
    max-width: 400px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

#qr-video {
    width: 100%;
    height: 300px;
    object-fit: cover;
    border-radius: 15px;
    background: #000;
}

#qr-canvas {
    display: none;
}

.qr-scanner-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
    z-index: 10;
}

.qr-scanner-frame {
    width: 200px;
    height: 200px;
    border: 3px solid #00ff00;
    border-radius: 15px;
    position: relative;
    background: transparent;
    animation: qr-scan-animation 2s ease-in-out infinite;
}

.qr-scanner-frame::before,
.qr-scanner-frame::after {
    content: '';
    position: absolute;
    width: 20px;
    height: 20px;
    border: 3px solid #00ff00;
}

.qr-scanner-frame::before {
    top: -3px;
    left: -3px;
    border-right: none;
    border-bottom: none;
}

.qr-scanner-frame::after {
    bottom: -3px;
    right: -3px;
    border-left: none;
    border-top: none;
}

@keyframes qr-scan-animation {
    0%, 100% {
        border-color: #00ff00;
        box-shadow: 0 0 20px rgba(0, 255, 0, 0.5);
    }
    50% {
        border-color: #00aa00;
        box-shadow: 0 0 30px rgba(0, 255, 0, 0.8);
    }
}

.qr-scanner-line {
    position: absolute;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg, transparent, #00ff00, transparent);
    animation: qr-scan-line 2s linear infinite;
}

@keyframes qr-scan-line {
    0% { top: 0; opacity: 1; }
    50% { opacity: 1; }
    100% { top: 100%; opacity: 0; }
}

.scanner-status {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    color: white;
    background: rgba(0, 0, 0, 0.7);
    padding: 8px 15px;
    border-radius: 20px;
    font-size: 12px;
    z-index: 15;
}

/* Control buttons styling */
.scanner-controls {
    text-align: center;
    margin-top: 20px;
}

.scanner-controls .btn {
    margin: 5px;
    border-radius: 25px;
    padding: 10px 20px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
}

.btn-success {
    background: linear-gradient(45deg, #28a745, #20c997);
    border: none;
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
}

.btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
}

.btn-danger {
    background: linear-gradient(45deg, #dc3545, #fd7e14);
    border: none;
    box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
}

.btn-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
}

.btn-warning {
    background: linear-gradient(45deg, #ffc107, #fd7e14);
    border: none;
    color: #212529;
    box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);
}

.btn-warning:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(255, 193, 7, 0.4);
}

/* Manual input styling */
.manual-input-section {
    background: #f8f9fa;
    border-radius: 15px;
    padding: 20px;
    margin-top: 20px;
    border: 1px solid #e9ecef;
}

.manual-input-section .form-control {
    border-radius: 10px;
    border: 2px solid #e9ecef;
    padding: 12px 15px;
    font-size: 16px;
    transition: all 0.3s ease;
}

.manual-input-section .form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 10px rgba(0, 123, 255, 0.2);
}

/* Result display styling */
.result-display {
    background: linear-gradient(135deg, #d4edda, #c3e6cb);
    border: 1px solid #c3e6cb;
    border-radius: 15px;
    padding: 25px;
    margin-top: 20px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.result-display h4 {
    color: #155724;
    margin-bottom: 20px;
    font-weight: 600;
}

.result-display p {
    margin-bottom: 10px;
    font-size: 14px;
}

.result-display .badge {
    font-size: 12px;
    padding: 5px 10px;
    border-radius: 10px;
}

/* Responsive design */
@media (max-width: 768px) {
    .scanner-wrapper {
        margin: 10px;
        max-width: 100%;
    }
    
    #qr-video {
        height: 250px;
    }
    
    .qr-scanner-frame {
        width: 150px;
        height: 150px;
    }
    
    .scanner-controls .btn {
        display: block;
        width: 100%;
        margin: 5px 0;
    }
}

/* Loading animations */
.loading-spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    border-top-color: #fff;
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Alert customizations */
.alert {
    border-radius: 10px;
    border: none;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    font-weight: 500;
}

.alert-success {
    background: linear-gradient(135deg, #d4edda, #c3e6cb);
    color: #155724;
}

.alert-danger {
    background: linear-gradient(135deg, #f8d7da, #f5c6cb);
    color: #721c24;
}

.alert-warning {
    background: linear-gradient(135deg, #fff3cd, #ffeaa7);
    color: #856404;
}

.alert-info {
    background: linear-gradient(135deg, #d1ecf1, #bee5eb);
    color: #0c5460;
}
</style>
@endpush

@section('title', 'Absensi Pelajaran - ' . $jadwalKelas->mata_pelajaran)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-custom">
            <div class="card-header bg-primary text-white text-center">
                <h4 class="mb-0">
                    <i class="fas fa-qrcode me-2"></i>
                    Absensi Pelajaran
                </h4>
                <small class="mt-1 d-block opacity-75">
                    <i class="fas fa-clock me-1"></i>
                    {{ $hariDisplay }}, {{ $waktuDisplay }} WIB
                </small>
            </div>
            <div class="card-body">
                <!-- Informasi Pelajaran -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h5 class="card-title text-primary mb-3">
                                    <i class="fas fa-book me-2"></i>
                                    Informasi Pelajaran
                                </h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-2">
                                            <strong><i class="fas fa-chalkboard me-1"></i> Mata Pelajaran:</strong><br>
                                            <span class="text-primary fs-5">{{ $jadwalKelas->mata_pelajaran ?: 'Tidak disebutkan' }}</span>
                                        </div>
                                        <div class="mb-2">
                                            <strong><i class="fas fa-chalkboard-teacher me-1"></i> Guru Pengampu:</strong><br>
                                            <span class="text-success">{{ $jadwalKelas->guru_pengampu ?: 'Tidak disebutkan' }}</span>
                                        </div>
                                        <div class="mb-2">
                                            <strong><i class="fas fa-users me-1"></i> Kelas:</strong><br>
                                            <span class="text-info">{{ $jadwalKelas->kelas->tingkat }} {{ $jadwalKelas->kelas->nama_kelas }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-2">
                                            <strong><i class="fas fa-clock me-1"></i> Waktu Pelajaran:</strong><br>
                                            <span class="text-warning">{{ $jadwalKelas->jam_masuk_format }} - {{ $jadwalKelas->jam_keluar_format }} WIB</span>
                                        </div>
                                        <div class="mb-2">
                                            <strong><i class="fas fa-calendar me-1"></i> Hari:</strong><br>
                                            <span class="text-secondary">{{ $jadwalKelas->nama_hari }}</span>
                                        </div>
                                        <div class="mb-2">
                                            <strong><i class="fas fa-graduation-cap me-1"></i> Jurusan:</strong><br>
                                            <span class="text-dark">{{ $jadwalKelas->kelas->jurusan->nama_jurusan }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alert Container -->
                <div id="alert-container"></div>

                <!-- Form Absensi -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-camera me-2"></i>
                                    Scan QR Code Siswa
                                </h5>
                            </div>
                            <div class="card-body">
                                <!-- Type and Session Info -->
                                <div class="mb-3">
                                    <label for="absen_type" class="form-label">Jenis Absensi</label>
                                    <input type="text" id="absen_type" name="absen_type" class="form-control" 
                                           value="{{ ucfirst($absenType) }}" readonly>
                                    <input type="hidden" id="absen_type_value" value="{{ $absenType }}">
                                    <input type="hidden" id="jadwal_kelas_id" value="{{ $jadwalKelas->id }}">
                                </div>

                                <!-- Scanner Container -->
                                <div id="scanner-container" class="d-none mb-4">
                                    <div class="scanner-wrapper mb-3">
                                        <video id="qr-video" autoplay muted playsinline style="width: 100%; max-width: 500px; height: auto; border-radius: 12px;"></video>
                                        <canvas id="qr-canvas" style="display: none;"></canvas>
                                        
                                        <!-- Scanner Overlay -->
                                        <div class="qr-scanner-overlay">
                                            <div class="qr-scanner-box">
                                                <div class="qr-corner qr-corner-top-left"></div>
                                                <div class="qr-corner qr-corner-top-right"></div>
                                                <div class="qr-corner qr-corner-bottom-left"></div>
                                                <div class="qr-corner qr-corner-bottom-right"></div>
                                                <div class="qr-scanner-line"></div>
                                            </div>
                                            <div class="qr-scanner-text">
                                                <p>Arahkan QR Code ke dalam kotak</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Scanner Status -->
                                    <div class="text-center">
                                        <div id="scanner-status" class="text-muted">Memuat scanner...</div>
                                        <div id="scanner-tips" class="text-info small mt-1">Pastikan QR Code berada di dalam area hijau</div>
                                    </div>
                                </div>

                                <!-- Control Buttons -->
                                <div class="text-center mb-3">
                                    <button type="button" id="startScan" class="btn btn-success btn-lg me-2">
                                        <i class="fas fa-camera me-2"></i>Mulai Scan QR Code
                                    </button>
                                    <button type="button" id="stopScan" class="btn btn-danger btn-lg d-none me-2">
                                        <i class="fas fa-stop me-2"></i>Stop Scan
                                    </button>
                                    <a href="{{ route('jadwal-kelas.index') }}" class="btn btn-secondary btn-lg">
                                        <i class="fas fa-arrow-left me-2"></i>Kembali ke Jadwal
                                    </a>
                                </div>

                                <!-- Manual QR Input -->
                                <div class="card mt-3">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="fas fa-keyboard me-2"></i>
                                            Input Manual QR Code
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="manual_qr" 
                                                   placeholder="Masukkan kode QR siswa secara manual">
                                            <button class="btn btn-primary" type="button" id="submitManual">
                                                <i class="fas fa-check me-1"></i>Submit
                                            </button>
                                        </div>
                                        <small class="text-muted">Gunakan jika scanner tidak dapat membaca QR Code</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Result Container -->
                <div id="result-container" class="d-none mt-4">
                    <div class="card border-success">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-check-circle me-2"></i>
                                Absensi Berhasil
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="result-content"></div>
                            <div class="text-center mt-3">
                                <button type="button" id="backToScan" class="btn btn-primary">
                                    <i class="fas fa-qrcode me-2"></i>Scan Lagi
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- QR Scanner Libraries -->
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script>
$(document).ready(function() {
    // QR Scanner Variables (sama seperti login)
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

    // Handle Back to Scan button
    $('#backToScan').click(function() {
        $('#result-container').addClass('d-none');
        $('#startScan').click();
    });

    // Check for camera permissions on page load
    checkCameraPermissions();

    // Initialize QR Scanner dengan implementasi seperti login
    function initQRScanner() {
        const video = document.getElementById('qr-video');
        const canvas = document.getElementById('qr-canvas');
        
        // Show scanner container
        $('#scanner-container').removeClass('d-none');
        $('#startScan').addClass('d-none');
        $('#stopScan').removeClass('d-none');
        
        // Enhanced camera constraints
        const constraints = {
            video: { 
                facingMode: { ideal: "environment" },  // Prefer back camera
                width: { ideal: 1280, max: 1920 },     // Good resolution
                height: { ideal: 720, max: 1080 },
                frameRate: { ideal: 60, max: 60 }      // Smooth scanning
            }
        };
        
        showAlert('info', '<i class="fas fa-spinner fa-spin me-2"></i>Mengakses kamera...', false);
        
        // Request camera access
        navigator.mediaDevices.getUserMedia(constraints)
            .then(function(stream) {
                console.log('Camera stream obtained successfully');
                
                videoStream = stream;
                video.srcObject = stream;
                video.setAttribute("playsinline", true);
                
                // Wait for video to be ready
                video.onloadedmetadata = function() {
                    console.log('Video metadata loaded');
                    video.play()
                        .then(() => {
                            scanning = true;
                            scanQRCode(); // Start scanning loop
                            showAlert('success', 'Kamera aktif! Arahkan QR Code siswa ke dalam kotak hijau.', true);
                            
                            // Check for torch support
                            checkTorchSupport(stream);
                        })
                        .catch(err => {
                            console.error('Video play error:', err);
                            showAlert('danger', 'Gagal memulai video kamera.', false);
                            stopQRScanner();
                        });
                };
                
                video.onerror = function(err) {
                    console.error('Video error:', err);
                    showAlert('danger', 'Terjadi error pada video kamera.', false);
                    stopQRScanner();
                };
                
            })
            .catch(function(err) {
                console.error("Error accessing camera: ", err);
                handleCameraError(err);
            });
    }

    // Handle camera errors dengan fallback
    function handleCameraError(err) {
        let errorMsg = 'Tidak dapat mengakses kamera. ';
        
        if (err.name === 'NotAllowedError') {
            errorMsg += 'Silakan izinkan akses kamera di browser dan refresh halaman.';
        } else if (err.name === 'NotFoundError') {
            errorMsg += 'Kamera tidak ditemukan pada perangkat ini.';
        } else if (err.name === 'NotReadableError') {
            errorMsg += 'Kamera sedang digunakan oleh aplikasi lain.';
        } else if (err.name === 'OverconstrainedError') {
            errorMsg += 'Konfigurasi kamera tidak didukung. Mencoba dengan pengaturan sederhana...';
            
            // Try with simpler constraints
            trySimpleCamera();
            return;
        } else {
            errorMsg += 'Error: ' + err.message;
        }
        
        $('#scanner-container').addClass('d-none');
        $('#startScan').removeClass('d-none');
        $('#stopScan').addClass('d-none');
        
        showAlert('danger', errorMsg, false);
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
                            showAlert('success', 'Kamera aktif (mode sederhana)! Arahkan QR Code ke area scan.', true);
                        });
                };
            })
            .catch(function(err) {
                console.error("Simple camera access failed: ", err);
                $('#scanner-container').addClass('d-none');
                $('#startScan').removeClass('d-none');
                $('#stopScan').addClass('d-none');
                showAlert('danger', 'Tidak dapat mengakses kamera dengan pengaturan apapun.', false);
            });
    }

    // Enhanced QR Code scanning dengan jsQR
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
            
            // Try to find QR code
            const code = jsQR(imageData.data, imageData.width, imageData.height, {
                inversionAttempts: "attemptBoth",
                locateAttempts: 10,
                minSize: 50
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

    // Stop QR Scanner dengan cleanup yang proper
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
        $('#stopScan').addClass('d-none');
        
        console.log('QR Scanner stopped and cleaned up');
    }
    
    // Check for camera permissions
    function checkCameraPermissions() {
        if (navigator.permissions && navigator.permissions.query) {
            navigator.permissions.query({ name: 'camera' })
                .then(function(permissionStatus) {
                    console.log('Camera permission status:', permissionStatus.state);
                    
                    if (permissionStatus.state === 'denied') {
                        showAlert('warning', 'Akses kamera ditolak. Silakan berikan izin kamera di pengaturan browser.', false);
                    }
                    
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
                <button id="toggleTorch" type="button" style="position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); background: rgba(255, 255, 255, 0.2); border: none; color: white; padding: 8px 15px; border-radius: 30px; font-size: 14px; cursor: pointer; z-index: 15;">
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

    function processQRCode(qrCode) {
        const jadwalKelasId = $('#jadwal_kelas_id').val();
        const absenType = $('#absen_type_value').val();
        
        console.log('📤 Processing QR Code:', {
            qr_code: qrCode,
            jadwal_kelas_id: jadwalKelasId,
            type: absenType
        });
        
        // Show loading
        showAlert('info', '<i class="fas fa-spinner fa-spin me-2"></i>Memproses absensi...', false);
        
        $.ajax({
            url: '{{ route("absensi.pelajaran.scan") }}',
            method: 'POST',
            data: {
                qr_code: qrCode,
                jadwal_kelas_id: jadwalKelasId,
                type: absenType
            },
            success: function(response) {
                console.log('✅ Absensi success:', response);
                
                if (response.success) {
                    showSuccessResult(response.data, response.message);
                } else {
                    showAlert('danger', response.message || 'Terjadi kesalahan saat memproses absensi.', false);
                }
            },
            error: function(xhr) {
                console.error('❌ Absensi error:', xhr);
                
                let errorMessage = 'Terjadi kesalahan saat memproses absensi.';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                showAlert('danger', errorMessage, false);
            }
        });
        
        // Clear manual input
        $('#manual_qr').val('');
    }

    function showSuccessResult(data, message) {
        $('#alert-container').empty();
        
        const resultHtml = `
            <div class="row">
                <div class="col-md-6">
                    <p><strong><i class="fas fa-id-card me-1"></i> NIS:</strong> ${data.nis}</p>
                    <p><strong><i class="fas fa-user me-1"></i> Nama:</strong> ${data.nama}</p>
                    <p><strong><i class="fas fa-users me-1"></i> Kelas:</strong> ${data.kelas}</p>
                    <p><strong><i class="fas fa-graduation-cap me-1"></i> Jurusan:</strong> ${data.jurusan}</p>
                </div>
                <div class="col-md-6">
                    <p><strong><i class="fas fa-book me-1"></i> Mata Pelajaran:</strong> ${data.mata_pelajaran || 'Tidak disebutkan'}</p>
                    <p><strong><i class="fas fa-chalkboard-teacher me-1"></i> Guru:</strong> ${data.guru_pengampu || 'Tidak disebutkan'}</p>
                    <p><strong><i class="fas fa-clock me-1"></i> Jam Masuk:</strong> ${data.jam_masuk}</p>
                    ${data.jam_keluar ? `<p><strong><i class="fas fa-sign-out-alt me-1"></i> Jam Keluar:</strong> ${data.jam_keluar}</p>` : ''}
                    <p><strong><i class="fas fa-info-circle me-1"></i> Status:</strong> <span class="badge bg-${data.status === 'telat' ? 'warning' : 'success'}">${data.status === 'telat' ? 'Terlambat' : 'Hadir'}</span></p>
                </div>
            </div>
        `;
        
        $('#result-content').html(resultHtml);
        $('#result-container').removeClass('d-none');
        
        // Success sound and animation
        playSuccessSound();
        flashScreen();
    }

    function showAlert(type, message, autoHide = true) {
        const alertClass = type === 'success' ? 'alert-success' : 
                          type === 'danger' ? 'alert-danger' : 
                          type === 'warning' ? 'alert-warning' : 'alert-info';
        
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        $('#alert-container').html(alertHtml);
        
        if (autoHide && type === 'success') {
            setTimeout(() => {
                $('#alert-container .alert').fadeOut();
            }, 5000);
        }
    }

    function flashScreen() {
        $('body').css('background-color', '#d4edda');
        setTimeout(() => {
            $('body').css('background-color', '');
        }, 200);
    }

    // Cleanup when page is unloaded
    $(window).on('beforeunload', function() {
        stopQRScanner();
    });
});
</script>
@endpush
