<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download QR Siswa - ZIP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <style>
        .download-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .download-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        .download-icon {
            font-size: 4rem;
            color: #9C27B0;
            margin-bottom: 20px;
        }
        .progress-container {
            margin: 30px 0;
        }
        .btn-download {
            background: linear-gradient(135deg, #9C27B0, #673AB7);
            border: none;
            border-radius: 50px;
            padding: 15px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
        }
        .btn-download:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(156, 39, 176, 0.3);
            color: white;
        }
        .qr-count {
            color: #9C27B0;
            font-weight: 600;
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <div class="download-container">
        <div class="download-card">
            <div class="download-icon">
                <i class="fas fa-file-archive"></i>
            </div>
            
            <h2 class="mb-3">Download QR Code Siswa</h2>
            @if(isset($kelasInfo))
            <p class="text-muted mb-2">Kelas <strong>{{ $kelasInfo['tingkat'] }} {{ $kelasInfo['nama_kelas'] }}</strong></p>
            <p class="text-muted mb-4">Jurusan: {{ $kelasInfo['jurusan'] }} | Siap mengunduh <span class="qr-count">{{ count($qrData) }}</span> QR code siswa dalam format ZIP</p>
            @else
            <p class="text-muted mb-4">Siap mengunduh <span class="qr-count">{{ count($qrData) }}</span> QR code siswa dalam format ZIP</p>
            @endif
            
            <div class="progress-container" id="progressContainer" style="display: none;">
                <div class="progress mb-3" style="height: 10px;">
                    <div class="progress-bar" role="progressbar" style="width: 0%; background: linear-gradient(90deg, #9C27B0, #673AB7);" id="progressBar"></div>
                </div>
                <small class="text-muted" id="progressText">Memproses...</small>
            </div>
            
            <button class="btn btn-download" onclick="downloadZip()" id="downloadBtn">
                <i class="fas fa-download me-2"></i>
                Download ZIP
            </button>
            
            <div class="mt-4">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    ZIP extension tidak tersedia, menggunakan JavaScript fallback
                </small>
            </div>
        </div>
    </div>

    <script>
        const qrData = @json($qrData);
        const zipFilename = @json($filename);
        
        async function downloadZip() {
            const downloadBtn = document.getElementById('downloadBtn');
            const progressContainer = document.getElementById('progressContainer');
            const progressBar = document.getElementById('progressBar');
            const progressText = document.getElementById('progressText');
            
            // Disable button and show progress
            downloadBtn.disabled = true;
            downloadBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
            progressContainer.style.display = 'block';
            
            try {
                const zip = new JSZip();
                
                for (let i = 0; i < qrData.length; i++) {
                    const item = qrData[i];
                    
                    // Update progress
                    const progress = ((i + 1) / qrData.length) * 100;
                    progressBar.style.width = progress + '%';
                    progressText.textContent = `Memproses ${i + 1} dari ${qrData.length} file...`;
                    
                    // Convert base64 to binary
                    const base64Data = item.data.split(',')[1];
                    const binaryData = atob(base64Data);
                    const bytes = new Uint8Array(binaryData.length);
                    for (let j = 0; j < binaryData.length; j++) {
                        bytes[j] = binaryData.charCodeAt(j);
                    }
                    
                    // Add file to ZIP
                    zip.file(item.filename, bytes);
                    
                    // Small delay to allow UI updates
                    await new Promise(resolve => setTimeout(resolve, 10));
                }
                
                progressText.textContent = 'Membuat file ZIP...';
                
                // Generate ZIP
                const zipBlob = await zip.generateAsync({
                    type: "blob",
                    compression: "DEFLATE",
                    compressionOptions: {
                        level: 6
                    }
                });
                
                // Download ZIP
                const link = document.createElement('a');
                link.href = URL.createObjectURL(zipBlob);
                link.download = zipFilename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                // Success
                progressText.textContent = 'Download berhasil!';
                progressBar.style.width = '100%';
                
                setTimeout(() => {
                    downloadBtn.disabled = false;
                    downloadBtn.innerHTML = '<i class="fas fa-download me-2"></i>Download ZIP';
                    progressContainer.style.display = 'none';
                }, 2000);
                
            } catch (error) {
                console.error('Error creating ZIP:', error);
                alert('Gagal membuat file ZIP: ' + error.message);
                
                downloadBtn.disabled = false;
                downloadBtn.innerHTML = '<i class="fas fa-download me-2"></i>Download ZIP';
                progressContainer.style.display = 'none';
            }
        }
        
        // Auto-trigger download after 2 seconds
        setTimeout(() => {
            downloadZip();
        }, 2000);
    </script>
</body>
</html>
