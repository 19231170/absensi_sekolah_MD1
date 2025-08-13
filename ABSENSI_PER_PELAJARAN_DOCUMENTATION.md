# Implementasi Absensi Per Pelajaran - QR Attendance System

## 📚 Overview
Sistem absensi telah diubah dari model **per sesi** menjadi **per pelajaran** berdasarkan jadwal kelas yang sudah ada. Setiap jadwal pelajaran yang aktif dan sedang berlangsung akan menampilkan tombol "Absen Sekarang" untuk akses langsung ke halaman scan QR.

## 🎯 Perubahan Utama

### 1. **Sistem Absensi Baru**
- ✅ **Absensi Per Pelajaran**: Berdasarkan `jadwal_kelas` bukan `jam_sekolah`
- ✅ **Tombol Dinamis**: Tombol "Absen Sekarang" muncul otomatis pada jadwal aktif di waktu yang tepat
- ✅ **Validasi Waktu**: Sistem memvalidasi waktu pelajaran dengan toleransi ±15 menit
- ✅ **Validasi Kelas**: Siswa hanya bisa absen di pelajaran kelasnya

### 2. **Interface Baru**
- ✅ **Halaman Jadwal Enhanced**: Tombol absen muncul dengan animasi pulse pada jadwal yang sedang berlangsung
- ✅ **Halaman Scan Khusus**: Interface scan QR yang disesuaikan untuk pelajaran tertentu
- ✅ **Informasi Pelajaran**: Menampilkan detail mata pelajaran, guru, dan kelas

## 🗄️ Database Structure

### Tabel Baru: `absensi_pelajaran`
```sql
CREATE TABLE absensi_pelajaran (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    nis VARCHAR(20) NOT NULL,
    jadwal_kelas_id BIGINT NOT NULL,
    tanggal DATE NOT NULL,
    jam_masuk TIME NULL,
    jam_keluar TIME NULL,
    status_masuk ENUM('hadir', 'telat', 'tidak_hadir') DEFAULT 'tidak_hadir',
    status_keluar ENUM('sudah_keluar', 'belum_keluar') NULL,
    keterangan TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (nis) REFERENCES siswa(nis) ON DELETE CASCADE,
    FOREIGN KEY (jadwal_kelas_id) REFERENCES jadwal_kelas(id) ON DELETE CASCADE,
    
    INDEX idx_nis_tanggal (nis, tanggal),
    INDEX idx_jadwal_tanggal (jadwal_kelas_id, tanggal),
    UNIQUE KEY unique_absensi_pelajaran (nis, jadwal_kelas_id, tanggal)
);
```

## 🛠️ File Structure

### Backend (Laravel)
```
app/
├── Http/Controllers/
│   └── AbsensiPelajaranController.php   # Controller untuk absensi per pelajaran
├── Models/
│   └── AbsensiPelajaran.php             # Model untuk tabel absensi_pelajaran
database/
├── migrations/
│   └── 2025_08_13_104427_create_absensi_pelajaran_table.php
```

### Frontend (Blade Templates)
```
resources/views/
├── absensi/
│   └── pelajaran.blade.php              # Halaman scan QR untuk pelajaran
├── jadwal-kelas/
│   └── index.blade.php                  # Enhanced dengan tombol absen dinamis
```

### Routes
```php
Route::prefix('absensi')->name('absensi.')->group(function () {
    // Absensi Per Pelajaran Routes
    Route::get('/pelajaran/{jadwalKelas}', [AbsensiPelajaranController::class, 'index'])
         ->name('pelajaran');
    Route::post('/pelajaran/scan', [AbsensiPelajaranController::class, 'scanQr'])
         ->name('pelajaran.scan');
});
```

## 🎛️ Controller Methods

### AbsensiPelajaranController

#### 1. **index($jadwalKelasId)** - Halaman Scan Pelajaran
- Validasi jadwal aktif dan waktu pelajaran
- Menentukan jenis absen (masuk/keluar) berdasarkan waktu
- Load informasi pelajaran lengkap

#### 2. **scanQr(Request $request)** - Proses Scan QR
- Validasi QR code siswa
- Validasi kelas siswa dengan jadwal pelajaran
- Validasi waktu pelajaran
- Proses absen masuk/keluar

#### 3. **validatePelajaranTime()** - Validasi Waktu
- Cek hari sesuai jadwal
- Cek status aktif jadwal
- Validasi range waktu absen masuk (±15 menit dari jam masuk)
- Validasi range waktu absen keluar (±15 menit dari jam keluar)

## 🎨 Model Features

### AbsensiPelajaran Model

#### Relations
```php
public function siswa(): BelongsTo
public function jadwalKelas(): BelongsTo
```

#### Scopes
```php
public function scopeUntukTanggal($query, $tanggal)
public function scopeUntukJadwal($query, $jadwalKelasId)
public function scopeUntukSiswa($query, $nis)
```

#### Static Methods
```php
public static function sudahAbsenMasuk($nis, $tanggal, $jadwalKelasId)
public static function sudahAbsenKeluar($nis, $tanggal, $jadwalKelasId)
```

#### Accessors
```php
public function getStatusDisplayAttribute(): string
public function getDurasiPelajaranAttribute(): ?string
```

## 🚀 Cara Penggunaan

### 1. **Akses Halaman Jadwal**
```
URL: http://127.0.0.1:8000/jadwal-kelas
```

### 2. **Tombol Absen Dinamis**
- Tombol "🔥 ABSEN SEKARANG" muncul otomatis pada jadwal yang:
  - ✅ Statusnya aktif (`is_active = true`)
  - ✅ Hari ini sesuai dengan jadwal
  - ✅ Waktu sekarang dalam range ±15 menit dari jam pelajaran
- Tombol memiliki animasi pulse untuk menarik perhatian

### 3. **Proses Absensi**
- Klik tombol "Absen Sekarang" → Redirect ke halaman scan QR pelajaran
- Scan QR code siswa atau input manual
- Sistem otomatis validasi:
  - Kelas siswa harus sesuai dengan jadwal pelajaran
  - Waktu absen harus dalam range yang diizinkan
  - Siswa belum absen untuk pelajaran ini hari ini

### 4. **Jenis Absensi Otomatis**
- **Masuk**: Jika waktu dalam range ±15 menit dari jam masuk
- **Keluar**: Jika waktu dalam range ±15 menit dari jam keluar atau selama pelajaran berlangsung

## ⚠️ Validasi & Error Handling

### Business Rules:
1. **Siswa hanya bisa absen di kelas yang sesuai**
2. **Tidak boleh absen di luar waktu pelajaran**
3. **Tidak boleh absen masuk dua kali untuk pelajaran yang sama**
4. **Tidak boleh absen keluar tanpa absen masuk**
5. **Jadwal harus aktif dan hari harus sesuai**

### Error Messages:
- Validasi kelas: "Siswa tidak terdaftar di kelas untuk jadwal pelajaran ini!"
- Validasi waktu: "Bukan waktu untuk pelajaran ini!"
- Duplicate absen: "Anda sudah melakukan absen masuk untuk pelajaran ini hari ini!"
- Jadwal nonaktif: "Jadwal pelajaran ini sedang tidak aktif."

## 🎨 UI/UX Features

### 1. **Tombol Dinamis dengan Animasi**
```css
.pulse-animation {
    animation: pulse-glow 2s infinite;
    box-shadow: 0 0 20px rgba(40, 167, 69, 0.6);
}
```

### 2. **Informasi Pelajaran Lengkap**
- Mata pelajaran dan guru pengampu
- Kelas dan jurusan
- Waktu pelajaran dan durasi
- Status jadwal (aktif/nonaktif)

### 3. **Scanner QR Enhanced**
- Library HTML5-QRCode v2.3.8
- Support fallback manual input
- Debug tools untuk testing
- Real-time status feedback

## 📊 Data Sample

### Contoh Data AbsensiPelajaran:
```php
[
    'nis' => '15021001',
    'jadwal_kelas_id' => 1,
    'tanggal' => '2025-08-13',
    'jam_masuk' => '07:35:00',
    'jam_keluar' => '10:25:00',
    'status_masuk' => 'hadir',
    'status_keluar' => 'sudah_keluar'
]
```

## 🔧 Technical Implementation

### 1. **Dynamic Button Logic**
```php
@php
    $now = \Carbon\Carbon::now('Asia/Jakarta');
    $jamMasuk = \Carbon\Carbon::parse($sesi->jam_masuk);
    $jamKeluar = \Carbon\Carbon::parse($sesi->jam_keluar);
    $isJadwalHariIni = $namaHari == $hariHariIni;
    $isWaktuPelajaran = $now->between($jamMasuk->copy()->subMinutes(15), $jamKeluar->copy()->addMinutes(15));
    $canAbsenNow = $isJadwalHariIni && $isWaktuPelajaran && $sesi->is_active;
@endphp
```

### 2. **Time Validation**
```php
private function validatePelajaranTime($jadwalKelas, $now, $type)
{
    // Validasi hari
    if (strtolower($jadwalKelas->hari) !== $this->getHariIndonesia()) {
        return ['valid' => false, 'message' => 'Hari tidak sesuai'];
    }
    
    // Validasi waktu berdasarkan jenis absen
    // ... logic validasi
}
```

### 3. **QR Processing**
```javascript
function processQRCode(qrCode) {
    $.ajax({
        url: '{{ route("absensi.pelajaran.scan") }}',
        method: 'POST',
        data: {
            qr_code: qrCode,
            jadwal_kelas_id: jadwalKelasId,
            type: absenType
        },
        // ... ajax handling
    });
}
```

## 📈 Future Enhancements

### Planned Improvements:
1. **Laporan Absensi Per Pelajaran**
2. **Dashboard Analytics Guru**
3. **Export Data Absensi Pelajaran**
4. **Notifikasi Real-time**
5. **Mobile App Integration**
6. **Rekap Kehadiran Siswa Per Mata Pelajaran**

## 🎉 Status: COMPLETED ✅

✅ **Controller**: AbsensiPelajaranController implementasi lengkap  
✅ **Model**: AbsensiPelajaran dengan relations dan methods  
✅ **Migration**: Tabel absensi_pelajaran dengan foreign keys  
✅ **Views**: Halaman scan QR khusus pelajaran  
✅ **Routes**: Route absensi pelajaran terdaftar  
✅ **UI Enhancement**: Tombol dinamis dengan animasi  
✅ **Validation**: Business rules dan error handling  
✅ **Testing**: Ready untuk testing dan deployment  

---

## 📝 Testing Instructions

### 1. **Setup Database**
```bash
php artisan migrate
```

### 2. **Test Route**
```bash
php artisan route:list --name=absensi
```

### 3. **Access Jadwal Page**
```
http://127.0.0.1:8000/jadwal-kelas
```

### 4. **Look for Active Lessons**
- Tombol "🔥 ABSEN SEKARANG" akan muncul pada jadwal yang aktif di waktu yang tepat
- Klik tombol untuk masuk ke halaman scan QR pelajaran

### 5. **Test QR Scanning**
- Gunakan QR code siswa yang valid
- Atau gunakan fitur "Input Manual QR Code"
- Test dengan berbagai skenario (waktu, kelas, dll.)

🚀 **Sistem Absensi Per Pelajaran siap digunakan!**
