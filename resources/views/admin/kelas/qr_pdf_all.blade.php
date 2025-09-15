<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>QR Siswa</title>
<style>
body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; }
.header { text-align: center; margin-bottom: 20px; }
.class-section { margin-bottom: 30px; page-break-inside: avoid; }
.class-title { 
    background: #9C27B0; 
    color: white; 
    padding: 8px; 
    margin-bottom: 15px; 
    border-radius: 4px;
    text-align: center;
    font-weight: bold;
}
.grid { display: flex; flex-wrap: wrap; }
.item { width: 33.33%; box-sizing: border-box; padding: 8px; text-align: center; }
.item div { margin-top: 4px; font-size: 10px; }
.qr { 
    border: 2px solid #9C27B0; 
    padding: 6px; 
    border-radius: 6px; 
    background: white;
}
.qr img { width: 120px; height: 120px; }
</style>
</head>
<body>
<div class="header">
    <h2 style="color: #9C27B0;">QR Code Semua Siswa</h2>
    <p>Total Siswa: {{ $siswaByKelas->flatten()->count() }} | Generated: {{ now()->format('d/m/Y H:i') }}</p>
</div>

@foreach($siswaByKelas as $kelasName => $siswasInKelas)
<div class="class-section">
    <div class="class-title">{{ $kelasName }} ({{ $siswasInKelas->count() }} siswa)</div>
    <div class="grid">
        @foreach($siswasInKelas as $siswa)
            @php
                // Generate QR code URL with purple background
                $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&bgcolor=FFFFFF&color=000000&data=' . urlencode($siswa->qr_code);
            @endphp
            <div class="item">
                <div class="qr">
                    <img src="{{ $qrUrl }}" alt="QR {{ $siswa->nama }}">
                    <div><strong>{{ $siswa->nama }}</strong></div>
                    <div>NIS: {{ $siswa->nis }}</div>
                    <div style="color: #9C27B0; font-weight: bold;">{{ $siswa->qr_code }}</div>
                    <div style="color: #9C27B0;">SISWA</div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endforeach
</body>
</html>
