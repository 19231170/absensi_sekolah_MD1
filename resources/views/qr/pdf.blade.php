<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>QR Codes - All Students</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }
        
        .header p {
            margin: 5px 0;
            color: #666;
        }
        
        .qr-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }
        
        .qr-item {
            width: 48%;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            box-sizing: border-box;
            page-break-inside: avoid;
        }
        
        .qr-item img {
            width: 150px;
            height: 150px;
            border: 1px solid #eee;
            border-radius: 4px;
        }
        
        .qr-info {
            margin-top: 10px;
        }
        
        .qr-info .name {
            font-weight: bold;
            color: #333;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .qr-info .details {
            color: #666;
            font-size: 11px;
            line-height: 1.4;
        }
        
        .qr-code {
            background-color: #f8f9fa;
            padding: 5px;
            border-radius: 3px;
            margin-top: 5px;
            font-family: monospace;
            font-size: 10px;
        }
        
        @media print {
            body { margin: 15px; }
            .qr-item { break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>QR Codes - Sistem Absensi Sekolah</h1>
        <p><strong>Generated:</strong> {{ \Carbon\Carbon::now('Asia/Jakarta')->format('d/m/Y H:i:s') }}</p>
        <p><strong>Total Siswa:</strong> {{ $siswa->count() }} siswa</p>
    </div>

    <div class="qr-grid">
        @foreach($siswa as $s)
        <div class="qr-item">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&bgcolor=FFFFFF&color=000000&data={{ urlencode($s->qr_code) }}" 
                 alt="QR Code {{ $s->nama }}">
            
            <div class="qr-info">
                <div class="name">{{ $s->nama }}</div>
                <div class="details">
                    <strong>NIS:</strong> {{ $s->nis }}<br>
                    <strong>Kelas:</strong> {{ $s->kelas->nama_lengkap }}<br>
                    <strong>Jurusan:</strong> {{ $s->kelas->jurusan->nama_jurusan }}
                </div>
                <div class="qr-code">
                    {{ $s->qr_code }}
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div style="margin-top: 30px; text-align: center; color: #999; font-size: 10px; border-top: 1px solid #ddd; padding-top: 15px;">
        <p>Sistem Absensi QR - Generated on {{ \Carbon\Carbon::now('Asia/Jakarta')->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
