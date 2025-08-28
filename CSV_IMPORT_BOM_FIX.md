 CSV Import BOM and Database Fix

## Masalah yang Ditemukan

Dari log dan testing, ditemukan 2 masalah utama:

### 1. **BOM (Byte Order Mark) Issue**
```
"ï»¿nama_siswa":"Ahmad Fauzi" 
```
- Template CSV menggunakan UTF-8 BOM
- Field key menjadi `ï»¿nama_siswa` bukan `nama_siswa`
- Mapping field gagal karena key name tidak match

### 2. **Database tanggal_lahir Issue**
```
SQLSTATE[HY000]: General error: 1364 Field 'tanggal_lahir' doesn't have a default value
```
- Field `tanggal_lahir` di tabel tidak memiliki default value
- Import tidak menyertakan tanggal_lahir
- MySQL error saat insert

## Root Cause Analysis

### BOM Issue
1. **Template Generation**: `SiswaController::downloadTemplate()` menambahkan BOM
   ```php
   fwrite($csv, "\xEF\xBB\xBF"); // Menambahkan BOM
   ```
2. **Import Processing**: `SiswaImport::processRow()` tidak handle BOM
3. **Field Mapping**: `nama_siswa` vs `ï»¿nama_siswa` tidak match

### Database Issue
1. **Missing Field**: `tanggal_lahir` tidak diisi saat `updateOrCreate`
2. **No Default**: Tabel tidak memiliki default value untuk field ini
3. **MySQL Strict Mode**: Error karena field required tapi tidak diisi

## Solusi yang Diterapkan

### 1. Remove BOM dari Template Generation

**File**: `app/Http/Controllers/SiswaController.php`

```php
// SEBELUM (Menambahkan BOM)
fwrite($csv, "\xEF\xBB\xBF");
fputcsv($csv, $header);

// SESUDAH (Tanpa BOM)
fputcsv($csv, $header); // Langsung tulis header
```

### 2. Add BOM Removal di Import Processing

**File**: `app/Imports/SiswaImport.php`

```php
// Method baru untuk remove BOM
private function removeBOM($string)
{
    if (substr($string, 0, 3) === "\xEF\xBB\xBF") {
        return substr($string, 3);
    }
    return $string;
}

// Update processRow untuk handle BOM
foreach ($row as $key => $value) {
    $cleanKey = $this->removeBOM(trim($key)); // Remove BOM dari key
    $normalizedKey = strtolower($cleanKey);
    $normalizedRow[$normalizedKey] = $value;
}
```

### 3. Add tanggal_lahir Default Value

**File**: `app/Imports/SiswaImport.php`

```php
$siswa = Siswa::updateOrCreate(
    ['nis' => trim($nis)],
    [
        'nama' => trim($nama),
        'jenis_kelamin' => $jenisKelaminNormalized,
        'tanggal_lahir' => null, // Tambah default null
        'kelas_id' => $kelasModel->id,
        'qr_code' => $qrCode,
        'status_aktif' => true
    ]
);
```

## Files Modified

### 1. `app/Http/Controllers/SiswaController.php`
- **Fixed**: Remove BOM dari template generation
- **Line 312**: Hapus `fwrite($csv, "\xEF\xBB\xBF");`

### 2. `app/Imports/SiswaImport.php`
- **Added**: `removeBOM()` method
- **Fixed**: BOM handling di `processRow()`
- **Fixed**: Add `tanggal_lahir => null` di `updateOrCreate`

### 3. Clean Test Files
- **Created**: `test_clean_import.csv` tanpa BOM
- **Removed**: Template file lama yang mengandung BOM

## Testing

### Test File Format (Bersih)
```csv
nama_siswa,nis,jenis_kelamin,jurusan,kelas
Ahmad Rizki,12345001,L,Teknik Komputer dan Jaringan,10 A
Siti Aminah,12345002,P,Rekayasa Perangkat Lunak,11 B
Budi Pratama,12345003,L,Multimedia,12 C
```

### Expected Results
✅ **BOM Issue**: Field mapping berhasil (nama_siswa recognized)
✅ **Database Issue**: Insert berhasil dengan tanggal_lahir = null
✅ **Auto-create**: Jurusan dan kelas dibuat otomatis
✅ **Validation**: Error messages lebih informatif

## Next Steps

1. **Download template baru** (tanpa BOM)
2. **Test dengan template baru**
3. **Test dengan file test yang clean**
4. **Verify import berhasil dan data masuk database**

Template yang baru akan di-generate tanpa BOM sehingga field mapping akan berhasil.
