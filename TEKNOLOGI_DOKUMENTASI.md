# Dokumentasi Teknologi - Sistem Absensi QR

## 📱 Overview Sistem

Sistem Absensi QR adalah aplikasi manajemen kehadiran berbasis web untuk sekolah dengan fitur utama QR code scanning untuk proses absensi yang cepat dan efisien. Sistem ini mengelola data siswa, jadwal kelas, absensi, dan menghasilkan laporan kehadiran yang komprehensif.

## 🚀 Teknologi Utama

### 1. **Framework: Laravel 12**
Laravel adalah framework PHP modern yang digunakan sebagai fondasi utama aplikasi dengan fitur:

- **MVC Architecture**: Memisahkan Model, View, dan Controller untuk code organization yang baik
- **Eloquent ORM**: Manipulasi database melalui model objek yang intuitif
- **Blade Templating**: Template engine untuk views yang powerful
- **Routing System**: Routing yang ekspresif dan mudah dikonfigurasi
- **Middleware**: Filtering HTTP requests sebelum mencapai controller
- **Migration System**: Versioning skema database
- **Artisan CLI**: Command-line tool untuk otomatisasi tugas
- **Authentication**: Sistem autentikasi bawaan dengan role-based access
- **Queue System**: Pengolahan background jobs untuk task yang membutuhkan waktu

```php
// Contoh penggunaan Eloquent ORM di Model Siswa
public function kelas(): BelongsTo
{
    return $this->belongsTo(Kelas::class);
}

// Contoh Route di web.php
Route::resource('jadwal-kelas', JadwalKelasController::class);
```

### 2. **Frontend: Tailwind CSS 4.0**
Framework CSS utility-first untuk styling aplikasi:

- **Utility Classes**: Membangun UI dengan menggabungkan class-class kecil
- **Responsive Design**: Mendukung berbagai ukuran layar
- **Custom Design System**: Kemudahan customization
- **JIT Compiler**: Kompilasi CSS on-demand untuk file size yang optimal

```html
<!-- Contoh penggunaan Tailwind CSS -->
<div class="bg-white shadow rounded-lg p-6 hover:shadow-lg transition-all">
    <h2 class="text-xl font-semibold text-gray-800">Data Siswa</h2>
</div>
```

### 3. **Build Tool: Vite 7.0**
Module bundler dan dev server:

- **Hot Module Replacement**: Instant preview perubahan tanpa refresh
- **Lightning Fast**: Startup dan rebuild yang sangat cepat
- **Asset Optimization**: Bundling dan minifikasi CSS dan JavaScript
- **Plugin System**: Ekstensibilitas melalui plugin seperti Tailwind

```javascript
// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
```

### 4. **Database: MySQL**
Relational database management system:

- **Schema Design**: Relational design dengan foreign keys
- **Transactions**: ACID compliance untuk data integrity
- **Indexing**: Optimized queries melalui indexing
- **Laravel Migrations**: Version control untuk skema database

```php
// Contoh migration create_absensi_table
public function up(): void
{
    Schema::create('absensi', function (Blueprint $table) {
        $table->id();
        $table->string('nis');
        $table->foreignId('jam_sekolah_id')->constrained();
        $table->date('tanggal');
        $table->time('jam_masuk')->nullable();
        $table->time('jam_keluar')->nullable();
        $table->string('status_masuk')->nullable();
        $table->string('status_keluar')->nullable();
        $table->text('keterangan')->nullable();
        $table->timestamps();
        
        $table->foreign('nis')->references('nis')->on('siswa');
    });
}
```

### 5. **QR Code Technology: Endroid QR Code 6.0**
Library untuk generate dan scan QR codes:

- **QR Generation**: Membuat QR codes untuk setiap siswa
- **Customization**: Size, color, dan error correction settings
- **Image Output**: Berbagai format output (PNG, SVG, etc.)
- **Error Correction**: Level error correction untuk readability

```php
// Contoh penggunaan Endroid QR Code
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

$qrCode = QrCode::create($siswa->qr_code)
    ->setSize(300)
    ->setMargin(10)
    ->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH);
    
$writer = new PngWriter();
$result = $writer->write($qrCode);
```

### 6. **PDF Generation: Laravel DomPDF**
Library untuk generate PDF reports:

- **HTML to PDF**: Konversi template Blade ke PDF
- **Custom Styling**: CSS untuk styling reports
- **Header & Footer**: Customize header dan footer
- **Pagination**: Support untuk pagination pada dokumen panjang

```php
// Contoh penggunaan Laravel DomPDF
use Barryvdh\DomPDF\Facade\Pdf;

$pdf = PDF::loadView('reports.absensi', [
    'siswa' => $siswa,
    'absensi' => $absensi
]);

return $pdf->download('laporan-absensi.pdf');
```

### 7. **Excel Export: Maatwebsite/Excel**
Package untuk ekspor data ke Excel:

- **Export Classes**: Class-based export configuration
- **Styling & Formatting**: Custom styling untuk spreadsheets
- **Multiple Sheets**: Support untuk multiple worksheets
- **Custom Headers**: Formatting headers and columns

```php
// Contoh penggunaan Laravel Excel
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AbsensiExport;

return Excel::download(new AbsensiExport($data), 'absensi.xlsx');
```

## 🧩 Integrasi Teknologi

### **JavaScript Frontend Integration**
Interaksi client-side menggunakan JavaScript modern:

1. **QR Scanner**: 
   - Akses webcam dengan MediaDevices API
   - Process image frames untuk QR detection
   - Real-time feedback dengan status updates

```javascript
// Contoh QR scanning dengan JavaScript
navigator.mediaDevices.getUserMedia({ 
    video: { 
        facingMode: { ideal: "environment" },
        width: { ideal: 1920 },
        height: { ideal: 1080 },
        frameRate: { ideal: 30 }
    } 
})
.then(function(stream) {
    // Process video stream untuk QR detection
});
```

2. **Axios HTTP Client**:
   - AJAX requests ke Laravel backend
   - Promise-based API
   - JSON response handling

```javascript
// Contoh request dengan Axios
axios.post('/api/absensi/scan', {
    qrCode: detectedCode,
    type: 'masuk'
})
.then(response => {
    // Handle successful scan
})
.catch(error => {
    // Handle errors
});
```

### **Laravel Backend Integration**

1. **Authentication System**:
   - Multi-role (Admin, Guru)
   - QR-based authentication for staff
   - Session management

```php
// Contoh middleware untuk role-based access
public function handle(Request $request, Closure $next)
{
    if (auth()->check() && auth()->user()->isAdmin()) {
        return $next($request);
    }
    
    return redirect('/')->with('error', 'Unauthorized access');
}
```

2. **Business Logic**:
   - Controllers untuk handle requests
   - Services untuk complex business logic
   - Repository pattern untuk data access

```php
// Contoh controller method
public function scan(Request $request)
{
    $qrCode = $request->input('qrCode');
    $type = $request->input('type');
    
    $siswa = Siswa::where('qr_code', $qrCode)->first();
    
    if (!$siswa) {
        return response()->json(['status' => 'error', 'message' => 'QR Code tidak valid'], 404);
    }
    
    // Process absensi logic
}
```

## 📂 Struktur Database

### **Core Tables**:

1. **users**: Staff and administrators
   - Authentication fields (email, password)
   - Role-based access control
   - QR code for staff login

2. **siswa**: Student information
   - Primary key: `nis` (Nomor Induk Siswa)
   - Personal information
   - QR code for attendance

3. **kelas**: Classes
   - Relationships to jurusan (majors)
   - Class level (X, XI, XII)

4. **jurusan**: Majors/Departments
   - PPLG (Pengembangan Perangkat Lunak dan Gim)
   - RPL (Rekayasa Perangkat Lunak)

5. **absensi**: Attendance records
   - Timestamps for entry/exit
   - Status (on-time, late, absent)
   - Relations to student and schedule

6. **jadwal_kelas**: Class schedules
   - Time slots
   - Subject information
   - Teacher assignments

7. **jam_sekolah**: School hours configuration
   - Session times (morning/afternoon)
   - Late thresholds

## 🔒 Sistem Keamanan

1. **Authentication & Authorization**:
   - Laravel's authentication system
   - Role-based access control
   - Session management
   - CSRF protection

2. **QR Code Security**:
   - Unique generation algorithm
   - Time-based validation
   - QR code encryption options

3. **Data Protection**:
   - Input validation and sanitization
   - Protection against SQL injection
   - XSS prevention

```php
// Contoh validation di controller
$validated = $request->validate([
    'nis' => 'required|exists:siswa,nis',
    'tanggal' => 'required|date',
    'jam_sekolah_id' => 'required|exists:jam_sekolah,id',
]);
```

## 🛠️ Development Tools

1. **Laravel Pint**: Code styling and standard enforcement
2. **PHPUnit**: Unit testing framework
3. **Laravel Sail**: Docker development environment
4. **Laravel Pail**: Enhanced console output for logging
5. **Concurrently**: Run multiple development processes simultaneously

## 📊 Fitur Highlight

### 1. **Enhanced QR Scanner**
QR scanner yang dioptimasi dengan:
- Resolusi HD (1920x1080)
- Multi-strategy detection algorithm
- Auto focus dan exposure
- Real-time visual feedback
- Flash/torch support

### 2. **Jadwal Kelas System**
Sistem manajemen jadwal dengan:
- Card-based layout
- Deteksi konflik otomatis
- Pembagian sesi (pagi/siang)
- Status toggle aktif/nonaktif

### 3. **Role-Based System**
Sistem berbasis role dengan:
- Admin: Akses penuh ke semua fitur
- Guru: Akses ke absensi kelas yang diampu
- Siswa: Scan QR untuk presensi

## 🚀 Deployment Requirements

1. **Server Requirements**:
   - PHP 8.2+
   - MySQL 8.0+
   - Composer
   - Node.js & NPM (development)
   - Web server (Apache/Nginx)

2. **Environment Setup**:
   - `.env` configuration
   - Database setup
   - Storage permissions
   - Queue configuration (optional)

3. **Build Process**:
   ```bash
   # Install PHP dependencies
   composer install --optimize-autoloader --no-dev
   
   # Install Node.js dependencies
   npm install
   
   # Build frontend assets
   npm run build
   
   # Generate application key
   php artisan key:generate
   
   # Run migrations
   php artisan migrate
   
   # Optimize application
   php artisan optimize
   ```

## 📈 Future Technology Enhancements

1. **Real-time Notifications**:
   - WebSocket integration
   - Push notifications
   - Real-time dashboards

2. **Mobile Application**:
   - Native mobile apps dengan framework seperti React Native/Flutter
   - Offline capabilities
   - Push notifications

3. **AI/ML Integration**:
   - Attendance pattern analysis
   - Predictive analytics
   - Anomaly detection

4. **Enhanced Security**:
   - Biometric authentication
   - Two-factor authentication
   - Advanced encryption

## 🔍 Kesimpulan

Sistem Absensi QR ini dibangun dengan teknologi modern menggunakan Laravel 12 sebagai backend framework, Tailwind CSS untuk UI, dan berbagai library seperti Endroid QR Code, DomPDF, dan Maatwebsite/Excel untuk fungsionalitas spesifik. Arsitektur modular dan penggunaan best practices dalam pengembangan memastikan sistem yang skalabel, aman, dan mudah dirawat.

---

Dokumentasi ini disusun pada: 8 Agustus 2025
