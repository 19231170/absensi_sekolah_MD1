# Dokumentasi Sistem Jadwal Kelas - QR Absensi

## 📚 Overview
Sistem manajemen jadwal kelas untuk 6 kelas lab (X PPLG 1&2, XI RPL 1&2, XII RPL 1&2) dengan fitur CRUD lengkap, deteksi konflik waktu, dan toggle status aktif/nonaktif.

## 🎯 Fitur Utama

### 1. **CRUD Operations**
- ✅ **Create**: Tambah jadwal baru dengan validasi konflik
- ✅ **Read**: Daftar jadwal dengan pencarian dan filter
- ✅ **Update**: Edit jadwal dengan pengecualian konflik untuk jadwal sendiri
- ✅ **Delete**: Hapus jadwal dengan konfirmasi

### 2. **Manajemen Waktu**
- ⏰ Jam masuk dan keluar
- 🕐 Batas telat (opsional)
- 📏 Kalkulasi durasi otomatis
- 🔍 Deteksi konflik waktu antar jadwal

### 3. **Filter & Search**
- 📅 Filter berdasarkan hari
- 🏫 Filter berdasarkan kelas
- 📊 Status aktif/nonaktif
- 🔎 Pencarian mata pelajaran dan guru

### 4. **UI/UX Features**
- 📱 Responsive design dengan Bootstrap 5
- 🎨 Status badges dan color coding
- ⚡ Real-time validation
- 🖱️ Interactive toggles dan konfirmasi

## 🗄️ Database Structure

### Tabel: `jadwal_kelas`
```sql
- id (Primary Key)
- kelas_id (Foreign Key → kelas.id)
- hari (enum: senin-jumat)
- jam_masuk (time)
- jam_keluar (time)
- batas_telat (time, nullable)
- mata_pelajaran (string, nullable)
- guru_pengampu (string, nullable)
- keterangan (text, nullable)
- is_active (boolean, default: true)
- created_at/updated_at (timestamps)
```

### Relationships
- **belongsTo**: Kelas (dengan Jurusan)
- **Index**: kelas_id, hari, jam_masuk untuk performa query

## 🛠️ File Structure

### Backend (Laravel)
```
app/
├── Http/Controllers/
│   └── JadwalKelasController.php    # CRUD + business logic
├── Models/
│   └── JadwalKelas.php              # Model dengan accessors
database/
├── migrations/
│   └── 2025_08_07_092814_create_jadwal_kelas_table.php
├── seeders/
│   └── JadwalKelasSeeder.php        # Data sample 6 kelas
```

### Frontend (Blade Templates)
```
resources/views/jadwal-kelas/
├── index.blade.php                  # Daftar jadwal + filter
├── create.blade.php                 # Form tambah jadwal
├── edit.blade.php                   # Form edit jadwal
└── show.blade.php                   # Detail jadwal
```

### Routes
```php
Route::resource('jadwal-kelas', JadwalKelasController::class);
Route::patch('jadwal-kelas/{jadwalKelas}/toggle-active', 
    [JadwalKelasController::class, 'toggleActive'])
    ->name('jadwal-kelas.toggle-active');
```

## 🎛️ Controller Methods

### 1. **index()** - Daftar Jadwal
- Filter berdasarkan hari dan kelas
- Pagination otomatis
- Search functionality
- Eager loading relationships

### 2. **create()** - Form Tambah
- Load data kelas dan hari
- Validation rules setup
- Error handling

### 3. **store()** - Simpan Jadwal Baru
```php
// Validasi input
$validated = $request->validate([
    'kelas_id' => 'required|exists:kelas,id',
    'hari' => 'required|in:senin,selasa,rabu,kamis,jumat',
    'jam_masuk' => 'required|date_format:H:i',
    'jam_keluar' => 'required|date_format:H:i|after:jam_masuk',
    // ... validasi lainnya
]);

// Cek konflik waktu
$konflik = JadwalKelas::where('kelas_id', $validated['kelas_id'])
    ->where('hari', $validated['hari'])
    ->where('is_active', true)
    ->where(function($query) use ($validated) {
        $query->whereBetween('jam_masuk', [$jamMasuk, $jamKeluar])
              ->orWhereBetween('jam_keluar', [$jamMasuk, $jamKeluar])
              ->orWhere(function($q) use ($jamMasuk, $jamKeluar) {
                  $q->where('jam_masuk', '<=', $jamMasuk)
                    ->where('jam_keluar', '>=', $jamKeluar);
              });
    })->exists();
```

### 4. **show()** - Detail Jadwal
- Tampilkan informasi lengkap
- Status badges
- Action buttons (edit, delete, toggle)

### 5. **edit()** - Form Edit
- Pre-fill form dengan data existing
- Same validation as create

### 6. **update()** - Update Jadwal
- Validasi dengan pengecualian jadwal sendiri
- Konflik detection excluding current record

### 7. **destroy()** - Hapus Jadwal
- Soft delete atau hard delete
- Konfirmasi via JavaScript

### 8. **toggleActive()** - Toggle Status
```php
public function toggleActive(JadwalKelas $jadwalKelas)
{
    $jadwalKelas->update([
        'is_active' => !$jadwalKelas->is_active
    ]);
    
    $status = $jadwalKelas->is_active ? 'diaktifkan' : 'dinonaktifkan';
    return redirect()->back()
        ->with('success', "Jadwal berhasil {$status}!");
}
```

## 🎨 Model Features

### Accessors
```php
// Format nama hari
public function getNamaHariAttribute()
{
    $hari = [
        'senin' => 'Senin', 'selasa' => 'Selasa',
        'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => 'Jumat'
    ];
    return $hari[$this->hari] ?? $this->hari;
}

// Kalkulasi durasi
public function getDurasiAttribute()
{
    $masuk = Carbon::parse($this->jam_masuk);
    $keluar = Carbon::parse($this->jam_keluar);
    return $masuk->diff($keluar)->format('%H jam %I menit');
}
```

### Scopes
```php
// Filter berdasarkan hari
public function scopeHari($query, $hari)
{
    return $query->where('hari', $hari);
}

// Filter berdasarkan kelas
public function scopeKelas($query, $kelasId)
{
    return $query->where('kelas_id', $kelasId);
}

// Hanya jadwal aktif
public function scopeActive($query)
{
    return $query->where('is_active', true);
}
```

## 📊 Data Sample (Seeder)

### 6 Kelas Lab:
1. **X PPLG 1** - Pengembangan Perangkat Lunak dan Gim
2. **X PPLG 2** - Pengembangan Perangkat Lunak dan Gim
3. **XI RPL 1** - Rekayasa Perangkat Lunak
4. **XI RPL 2** - Rekayasa Perangkat Lunak
5. **XII RPL 1** - Rekayasa Perangkat Lunak
6. **XII RPL 2** - Rekayasa Perangkat Lunak

### Sample Schedules:
```php
// Contoh jadwal yang di-seed
[
    'kelas_id' => 1, // X PPLG 1
    'hari' => 'senin',
    'jam_masuk' => '07:30:00',
    'jam_keluar' => '10:00:00',
    'batas_telat' => '07:45:00',
    'mata_pelajaran' => 'Pemrograman Dasar',
    'guru_pengampu' => 'Pak Ahmad',
    'keterangan' => 'Lab Komputer 1'
]
```

## 🚀 Cara Penggunaan

### 1. **Akses Sistem**
```
URL: http://127.0.0.1:8000/jadwal-kelas
```

### 2. **Tambah Jadwal Baru**
- Klik tombol "Tambah Jadwal"
- Pilih kelas, hari, dan waktu
- Isi mata pelajaran dan guru (opsional)
- Submit form

### 3. **Edit Jadwal**
- Klik ikon edit (✏️) pada daftar
- Atau masuk ke detail → klik "Edit"
- Ubah data yang diperlukan
- Submit perubahan

### 4. **Toggle Status**
- Klik tombol toggle pada daftar
- Atau dari halaman detail
- Konfirmasi perubahan

### 5. **Filter & Search**
- Gunakan dropdown filter hari/kelas
- Ketik di search box untuk mata pelajaran/guru
- Reset filter dengan memilih "Semua"

## ⚠️ Validasi & Error Handling

### Business Rules:
1. **Jam keluar harus setelah jam masuk**
2. **Tidak boleh ada konflik waktu untuk kelas yang sama**
3. **Kelas harus terdaftar di sistem**
4. **Hari harus senin-jumat**
5. **Batas telat harus setelah jam masuk**

### Error Messages:
- Konflik waktu: "Terdapat konflik jadwal dengan [detail konflik]"
- Validasi waktu: "Jam keluar harus setelah jam masuk"
- Data tidak ditemukan: "Jadwal tidak ditemukan"

## 🔧 Maintenance

### Database Optimization:
```sql
-- Index untuk performa
CREATE INDEX idx_jadwal_kelas_lookup ON jadwal_kelas(kelas_id, hari, jam_masuk);
CREATE INDEX idx_jadwal_active ON jadwal_kelas(is_active);
```

### Backup Strategy:
```bash
# Backup data jadwal
php artisan db:seed --class=JadwalKelasSeeder --force

# Export ke Excel/PDF (jika diperlukan)
# Implementasi controller export
```

## 📈 Future Enhancements

### Possible Improvements:
1. **Notifikasi konflik real-time**
2. **Import jadwal dari Excel**
3. **Export ke berbagai format**
4. **Integrasi dengan sistem absensi**
5. **Mobile app support**
6. **Automated conflict resolution**

---

## 🎉 Status: COMPLETED ✅

Sistem jadwal kelas telah **berhasil dibuat lengkap** dengan:
- ✅ Database migration & seeder
- ✅ CRUD controller dengan business logic
- ✅ Model dengan relationships & accessors
- ✅ UI responsif dengan Bootstrap 5
- ✅ Validasi & error handling
- ✅ Conflict detection
- ✅ Status management (active/inactive)

**Ready for production use!** 🚀
