# Simplified Import System - Documentation

## Overview

Sistem import siswa telah dirombak ulang untuk menjadi lebih sederhana dan user-friendly. Format baru hanya memerlukan **5 kolom wajib** dibandingkan format lama yang memerlukan 9 kolom.

## Perubahan Utama

### Format Lama (Kompleks)
- **9 kolom:** nis, nama, tingkat, kelas, jurusan, jenis_kelamin, tanggal_lahir, alamat, nomor_hp
- Perlu membuat jurusan dan kelas secara manual di sistem terlebih dahulu
- Validasi ketat terhadap data yang sudah ada
- Format tanggal birth yang kompleks

### Format Baru (Simplified)
- **5 kolom:** nama_siswa, nis, jenis_kelamin, jurusan, kelas
- **Auto-create:** Sistem otomatis membuat jurusan dan kelas jika belum ada
- **Format gabungan:** Kolom "kelas" menggabungkan tingkat dan nama kelas
- **QR Code otomatis:** Digenerate otomatis untuk setiap siswa

## Format Data Baru

### Kolom yang Diperlukan

| Kolom | Tipe | Required | Deskripsi | Contoh |
|-------|------|----------|-----------|---------|
| `nama_siswa` | String | ✅ | Nama lengkap siswa | Ahmad Fauzi |
| `nis` | String | ✅ | Nomor Induk Siswa (unik) | 2024001 |
| `jenis_kelamin` | String | ✅ | L atau P | L |
| `jurusan` | String | ✅ | Nama jurusan lengkap | Teknik Komputer dan Jaringan |
| `kelas` | String | ✅ | Format: "tingkat nama_kelas" | 10 A, 11 IPA 1, 12 TKJ 2 |

### Contoh Data

```csv
nama_siswa,nis,jenis_kelamin,jurusan,kelas
Ahmad Fauzi,2024001,L,Teknik Komputer dan Jaringan,10 A
Siti Nurhaliza,2024002,P,Rekayasa Perangkat Lunak,10 B
Budi Santoso,2024003,L,Teknik Kendaraan Ringan,11 A
Dewi Sartika,2024004,P,Akuntansi dan Keuangan Lembaga,11 AKL 1
Ridwan Kamil,2024005,L,Multimedia,12 MM 2
```

## Parsing Kelas Field

Sistem akan parse kolom `kelas` dengan format berikut:

### Format yang Didukung
- `"10 A"` → tingkat: 10, nama_kelas: A
- `"11 IPA 1"` → tingkat: 11, nama_kelas: IPA 1
- `"12 TKJ 2"` → tingkat: 12, nama_kelas: TKJ 2

### Regex Pattern
```php
/^(\d{1,2})\s+(.+)$/
```

## File Changes

### 1. `app/Imports/SiswaImport.php`
**Perubahan Utama:**
- Menggunakan Laravel Excel interfaces: `ToModel`, `WithHeadingRow`, `WithValidation`
- Implementasi auto-create untuk jurusan dan kelas
- Parsing kolom kelas gabungan
- Error handling yang lebih baik
- Comprehensive logging

**Key Methods:**
- `model()`: Transform data ke Siswa model
- `parseKelasField()`: Parse format kelas gabungan
- `getImportStats()`: Statistik import
- `rules()`: Validasi rules

### 2. `app/Http/Controllers/SiswaController.php`
**Method `importExcel()`:**
- Menggunakan `SiswaImport` yang baru
- Validasi file type yang lebih ketat
- Error handling yang improved
- Logging yang comprehensive

**Method `downloadTemplate()`:**
- Template baru dengan 5 kolom
- Contoh data yang realistic
- Filename: `Template_Siswa_Simplified.csv`

### 3. `resources/views/siswa/import.blade.php`
**UI Improvements:**
- Informasi format baru yang jelas
- Tabel contoh data
- Alert keunggulan format baru
- Instruksi yang lebih user-friendly

## Keunggulan Format Baru

### 1. Simplicity
- Hanya 5 kolom vs 9 kolom sebelumnya
- Data yang benar-benar essential saja

### 2. Auto-Create Feature
- Sistem otomatis membuat `Jurusan` jika belum ada
- Sistem otomatis membuat `Kelas` jika belum ada
- Kapasitas default kelas: 40 siswa

### 3. Smart Parsing
- Kolom kelas menggabungkan tingkat dan nama kelas
- Support format yang fleksibel

### 4. Error Handling
- Validasi yang comprehensive
- Pesan error yang jelas dan detail
- Logging untuk debugging

### 5. Data Integrity
- QR code digenerate otomatis
- Status aktif default: true
- Update/Create based on NIS

## Validation Rules

### Built-in Laravel Validation
```php
[
    'nama_siswa' => 'required|string|max:255',
    'nis' => 'required|string|max:20',
    'jenis_kelamin' => 'required|in:L,P,l,p',
    'jurusan' => 'required|string|max:100',
    'kelas' => 'required|string|max:50'
]
```

### Custom Validation
- Parsing format kelas
- Normalisasi jenis kelamin (uppercase)
- Trim whitespace

## Error Handling

### Import Errors
- Data tidak lengkap
- Format kelas tidak valid
- Jenis kelamin tidak valid
- Database errors

### Error Response
```json
{
    "processed": 100,
    "success": 95,
    "failed": 5,
    "errors": [
        "Baris 10: Format kelas '10X' tidak valid. Gunakan format: '10 A'",
        "Baris 15: Jenis kelamin harus 'L' atau 'P', diterima: 'X'"
    ]
}
```

## Testing

### Manual Testing
1. Download template dari sistem
2. Isi dengan data test
3. Upload file
4. Verify hasil import
5. Check database records

### Validation Testing
- Test dengan data tidak lengkap
- Test dengan format kelas salah
- Test dengan jenis kelamin salah
- Test dengan file corrupted

## Usage Guide

### Untuk Admin
1. Akses menu **Siswa** → **Import**
2. Download template (CSV atau Excel)
3. Isi data sesuai format:
   - nama_siswa: Nama lengkap
   - nis: Nomor unik
   - jenis_kelamin: L atau P
   - jurusan: Nama lengkap jurusan
   - kelas: Format "tingkat nama_kelas"
4. Upload file
5. Review hasil import

### Untuk User
- Format lebih sederhana
- Template sudah include contoh data
- Error message yang jelas
- Auto-create jurusan/kelas

## Migration dari Format Lama

Jika masih ada file dengan format lama:
1. Download template baru
2. Copy data ke format baru:
   - `nama` → `nama_siswa`
   - Gabungkan `tingkat` + `kelas` → `kelas`
   - Field lain sesuaikan

## Database Impact

### Auto-Created Records
```sql
-- Jurusan baru
INSERT INTO jurusan (nama_jurusan, keterangan) 
VALUES ('Teknik Komputer dan Jaringan', 'Auto-created from import');

-- Kelas baru
INSERT INTO kelas (tingkat, nama_kelas, jurusan_id, kapasitas, status_aktif)
VALUES (10, 'A', 1, 40, 1);
```

### Updated/Created Siswa
```sql
-- UpdateOrCreate based on NIS
INSERT INTO siswa (nis, nama, jenis_kelamin, kelas_id, qr_code, status_aktif)
VALUES ('2024001', 'Ahmad Fauzi', 'L', 1, 'QR_AUTO_GENERATED', 1)
ON DUPLICATE KEY UPDATE nama = VALUES(nama), jenis_kelamin = VALUES(jenis_kelamin), kelas_id = VALUES(kelas_id);
```

## Performance

### Optimizations
- Use `firstOrCreate()` for auto-create
- Batch processing untuk large files
- Memory efficient dengan Laravel Excel
- Comprehensive logging tanpa performance hit

### Scalability
- Support file hingga 2MB
- Handle thousands of records
- Error recovery mechanism
- Progress tracking

## Support

### File Formats
- ✅ Excel (.xlsx, .xls)
- ✅ CSV (.csv)
- ❌ ODS, Numbers, etc.

### Character Encoding
- UTF-8 BOM untuk Excel compatibility
- Support karakter Indonesia

### Browser Compatibility
- Modern browsers
- File upload progress
- Responsive design

---

**Date:** December 2024  
**Version:** Laravel 12 Simplified Import System  
**Status:** Production Ready
