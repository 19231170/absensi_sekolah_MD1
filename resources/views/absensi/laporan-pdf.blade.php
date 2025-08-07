<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Absensi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
        }
        .info {
            margin-bottom: 20px;
        }
        .info p {
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .bg-success {
            background-color: #28a745;
            color: white;
        }
        .bg-warning {
            background-color: #ffc107;
            color: black;
        }
        .bg-primary {
            background-color: #007bff;
            color: white;
        }
        .bg-secondary {
            background-color: #6c757d;
            color: white;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
        }
        .statistics {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
        }
        .stat-box {
            text-align: center;
            padding: 10px;
            border: 1px solid #ddd;
            flex: 1;
            margin: 0 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Absensi Siswa</h1>
        <p>Sistem Absensi QR Code</p>
    </div>

    <div class="info">
        <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($tanggal)->format('d F Y') }}</p>
        @if($selectedSesi)
            <p><strong>Sesi:</strong> {{ $selectedSesi->nama_sesi }} ({{ $selectedSesi->jam_masuk }} - {{ $selectedSesi->jam_keluar }})</p>
        @else
            <p><strong>Sesi:</strong> Semua Sesi</p>
        @endif
        <p><strong>Total Data:</strong> {{ $absensi->count() }} siswa</p>
    </div>

    <!-- Statistik -->
    <div class="statistics">
        <div class="stat-box">
            <strong>{{ $absensi->where('status_masuk', 'hadir')->count() }}</strong><br>
            <small>Hadir</small>
        </div>
        <div class="stat-box">
            <strong>{{ $absensi->where('status_masuk', 'telat')->count() }}</strong><br>
            <small>Telat</small>
        </div>
        <div class="stat-box">
            <strong>{{ $absensi->where('status_keluar', 'sudah_keluar')->count() }}</strong><br>
            <small>Sudah Keluar</small>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 10%;">NIS</th>
                <th style="width: 20%;">Nama Siswa</th>
                <th style="width: 10%;">Kelas</th>
                <th style="width: 15%;">Jurusan</th>
                <th style="width: 15%;">Sesi</th>
                <th style="width: 8%;">Jam Masuk</th>
                <th style="width: 8%;">Jam Keluar</th>
                <th style="width: 9%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($absensi as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->nis }}</td>
                <td>{{ $item->siswa->nama }}</td>
                <td>{{ $item->siswa->kelas->nama_lengkap }}</td>
                <td>{{ $item->siswa->kelas->jurusan->nama_jurusan }}</td>
                <td>{{ $item->jamSekolah->nama_sesi }}</td>
                <td class="text-center">{{ $item->jam_masuk ?? '-' }}</td>
                <td class="text-center">{{ $item->jam_keluar ?? '-' }}</td>
                <td class="text-center">
                    @if($item->status_masuk == 'hadir')
                        Hadir
                    @elseif($item->status_masuk == 'telat')
                        Telat
                    @else
                        Alpha
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center" style="padding: 20px;">
                    Tidak ada data absensi untuk tanggal yang dipilih.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ \Carbon\Carbon::now('Asia/Jakarta')->format('d F Y H:i:s') }} WIB</p>
    </div>
</body>
</html>
