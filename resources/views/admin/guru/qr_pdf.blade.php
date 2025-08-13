<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>QR Guru</title>
<style>
body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; }
.grid { display: flex; flex-wrap: wrap; }
.item { width: 33.33%; box-sizing: border-box; padding: 10px; text-align: center; }
.item div { margin-top: 5px; }
.qr { border:1px solid #ccc; padding:6px; border-radius:6px; }
</style>
</head>
<body>
<h3 style="text-align:center">QR Code Semua Guru</h3>
<p>Total Guru: {{ $guru->count() }} | Generated: {{ now()->format('d/m/Y H:i') }}</p>
<div class="grid">
@foreach($guru as $g)
    @php
        if(!$g->qr_code){ $g->qr_code = 'GRU' . str_pad($g->id,3,'0',STR_PAD_LEFT); }
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&bgcolor=2196F3&color=FFFFFF&data=' . urlencode($g->qr_code);
    @endphp
    <div class="item">
        <div class="qr">
            <img src="{{ $qrUrl }}" alt="QR {{ $g->name }}" style="width:180px;height:180px;">
            <div><strong>{{ $g->name }}</strong></div>
            <div>{{ $g->nip ?? 'NIP-' . $g->id }}</div>
            <div>{{ $g->qr_code }}</div>
        </div>
    </div>
@endforeach
</div>
</body>
</html>
