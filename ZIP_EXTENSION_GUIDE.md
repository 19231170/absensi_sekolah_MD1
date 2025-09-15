# Panduan Mengaktifkan PHP ZIP Extension

## Status Saat Ini
- ✅ ZIP module terdeteksi di PHP info
- ❌ ZipArchive class tidak tersedia
- ❌ extension_loaded('zip') mengembalikan false

## Cara Mengaktifkan ZIP Extension

### 1. Cari File php.ini
Jalankan perintah berikut untuk menemukan lokasi php.ini:
```bash
php --ini
```

### 2. Edit File php.ini
Buka file php.ini dengan text editor dan cari baris berikut:
```ini
;extension=zip
```

Hapus tanda `;` di depannya sehingga menjadi:
```ini
extension=zip
```

### 3. Restart Web Server
- Jika menggunakan XAMPP: Restart Apache di Control Panel
- Jika menggunakan WAMP: Restart semua services
- Jika menggunakan Laragon: Restart
- Jika menggunakan built-in server: Hentikan dan jalankan ulang `php artisan serve`

### 4. Verifikasi
Setelah restart, jalankan perintah berikut untuk memverifikasi:
```bash
php -r "var_dump(extension_loaded('zip')); var_dump(class_exists('ZipArchive'));"
```

Hasilnya harus:
```
bool(true)
bool(true)
```

## Alternatif untuk Windows (XAMPP/WAMP)

### XAMPP:
1. Buka XAMPP Control Panel
2. Klik "Config" di sebelah Apache
3. Pilih "PHP (php.ini)"
4. Cari dan uncomment: `extension=zip`
5. Save dan restart Apache

### WAMP:
1. Klik icon WAMP di system tray
2. PHP → PHP Extensions → zip (centang)
3. Restart All Services

## Troubleshooting

### Jika masih tidak berfungsi:
1. Pastikan versi PHP yang digunakan sama dengan yang di-edit php.ini-nya
2. Cek apakah ada multiple instalasi PHP
3. Restart komputer jika perlu

### Cek Multiple PHP Installation:
```bash
php -v
where php
```

### Cek PHP Info untuk ZIP:
```bash
php -r "phpinfo();" | findstr -i zip
```

## Setelah ZIP Extension Aktif

Setelah ZIP extension berhasil diaktifkan, fitur-fitur berikut akan tersedia:

✅ **Download ZIP QR Codes** - Download semua QR code siswa dalam satu file ZIP
✅ **Batch Processing** - Proses multiple QR codes sekaligus
✅ **Better Performance** - File handling yang lebih efisien

## Test Fungsi ZIP

Setelah mengaktifkan, test dengan mengakses:
- Menu Kelas → Detail Kelas → Download QR → Download ZIP
- Atau dari Daftar Kelas → tombol "ZIP QR" di setiap kelas

Jika masih error, cek log Laravel di `storage/logs/laravel.log`
