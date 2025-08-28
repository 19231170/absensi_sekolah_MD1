# Copilot Instructions for Absensi QR System

## Project Architecture

This is a Laravel 12 school attendance system with dual attendance models:
- **Legacy System**: `Absensi` model for session-based attendance (`jam_sekolah`)
- **Current System**: `AbsensiPelajaran` model for lesson-based attendance (`jadwal_kelas`)

## Key Components

### Core Models
- `Siswa`: Student data with QR codes (`qr_code` field for scanning)
- `Kelas`: Class information with safe accessors (use null checks for `getNamaLengkapAttribute()`)
- `JadwalKelas`: Lesson schedules with time validation (`jam_masuk`, `jam_keluar`, `batas_telat`)
- `AbsensiPelajaran`: Current attendance system (stores `nis`, `jadwal_kelas_id`, dates/times)

### Critical Patterns

#### Safe Model Accessors
Always use null-safe patterns when accessing related models:
```php
'kelas' => ($siswa->kelas ? "{$siswa->kelas->tingkat} {$siswa->kelas->nama_kelas}" : 'Kelas tidak diketahui'),
'jurusan' => ($siswa->kelas && $siswa->kelas->jurusan ? $siswa->kelas->jurusan->nama_jurusan : 'Jurusan tidak diketahui'),
```

#### Time-based Attendance Logic
- Attendance type (`masuk`/`keluar`) determined by current time vs lesson schedule
- If `batas_telat` is empty, attendance allowed anytime (no time restriction)
- If `batas_telat` is set, use as cutoff for late arrival

#### Comprehensive Logging
All attendance operations use detailed logging:
```php
Log::info('Processing absensi', [
    'type' => $type,
    'siswa_nis' => $siswa->nis,
    'jadwal_id' => $jadwalKelas->id
]);
```

### QR Scanner Workflow
1. Frontend sends: `qr_code`, `jadwal_kelas_id`, `type` (masuk/keluar)
2. Controller validates: student exists, class matches, time windows
3. Process: Create/update `AbsensiPelajaran` record
4. Response: Include `jenis_absensi` field for UI display

### Development Commands
```bash
# Clear all caches after model changes
php artisan config:clear && php artisan cache:clear && php artisan view:clear && php artisan route:clear

# Monitor logs for debugging
Get-Content "storage\logs\laravel.log" | Select-Object -Last 30
```

### Database Relationships
- `AbsensiPelajaran` belongs to `Siswa` (via `nis` field)
- `AbsensiPelajaran` belongs to `JadwalKelas`
- Always eager load relations: `with(['siswa.kelas.jurusan', 'jadwalKelas'])`

### Report Integration
`AbsensiController::laporan()` combines both old and new attendance systems for unified reporting.

## Common Issues
- **"Separation symbol" errors**: Caused by unsafe model accessors - always use null checks
- **Missing data in reports**: Ensure both `Absensi` and `AbsensiPelajaran` are queried
- **Time validation fails**: Check `batas_telat` null handling in `validatePelajaranTime()`
