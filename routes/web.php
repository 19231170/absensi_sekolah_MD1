<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\QrController;
use App\Http\Controllers\JadwalKelasController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\JurusanController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\QrAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return redirect()->route('qr.login.form');
});

// Route untuk QR Authentication (Staff Login)
Route::prefix('auth')->name('qr.login.')->group(function () {
    Route::get('/qr-login', [QrAuthController::class, 'showLoginForm'])->name('form');
    Route::post('/scan', [QrAuthController::class, 'scanQr'])->name('scan');
    Route::post('/pin', [QrAuthController::class, 'verifyPin'])->name('pin');
    Route::post('/clear', [QrAuthController::class, 'clearSession'])->name('clear');
    Route::post('/logout', [QrAuthController::class, 'logout'])->name('logout');
});

// Protected Routes - Authenticated Users Only
Route::middleware(['auth.qr'])->group(function () {
    
    // Admin Dashboard
    Route::get('/dashboard/admin', function() {
        $user = Auth::user();
        if ($user->role !== 'admin') {
            return redirect()->route('guru.dashboard')->with('error', 'Akses ditolak!');
        }
        return view('dashboard.admin', compact('user'));
    })->name('admin.dashboard');
    
    // Guru Dashboard
    Route::get('/dashboard/guru', function() {
        $user = Auth::user();
        return view('dashboard.guru', compact('user'));
    })->name('guru.dashboard');

    // Absensi Routes (Admin & Guru)
    Route::prefix('absensi')->name('absensi.')->group(function () {
        Route::get('/', [AbsensiController::class, 'index'])->name('index');
        Route::post('/scan', [AbsensiController::class, 'scanQr'])->name('scan');
        Route::get('/laporan', [AbsensiController::class, 'laporan'])->name('laporan');
    });

    // QR Code Routes (Admin & Guru) - Guru hanya untuk siswa kelasnya
    Route::prefix('qr')->name('qr.')->group(function () {
        Route::get('/', [QrController::class, 'index'])->name('index');
        Route::get('/{nis}', [QrController::class, 'show'])->name('show');
        Route::get('/download/{nis}', [QrController::class, 'download'])->name('download');
        Route::get('/image/{nis}', [QrController::class, 'image'])->name('image');
        Route::get('/download-all/zip', [QrController::class, 'downloadAll'])->name('download.all');
        Route::get('/download-all/pdf', [QrController::class, 'downloadAllPdf'])->name('download.all.pdf');
        
        // Staff QR Code
        Route::get('/staff/generate', [QrController::class, 'generateStaffQr'])->name('staff.generate');
        Route::get('/staff/download', [QrController::class, 'downloadStaffQr'])->name('staff.download');
    });

    // Jadwal Kelas Routes - Admin Full CRUD, Guru Read Only
    Route::get('/jadwal-kelas', [JadwalKelasController::class, 'index'])->name('jadwal-kelas.index');
    Route::get('/jadwal-kelas/{jadwalKelas}', [JadwalKelasController::class, 'show'])->name('jadwal-kelas.show');

    // Admin Only Routes
    Route::middleware('role:admin')->group(function () {
        // Jadwal Kelas CRUD (Admin Only)
        Route::post('/jadwal-kelas', [JadwalKelasController::class, 'store'])->name('jadwal-kelas.store');
        Route::get('/jadwal-kelas/create', [JadwalKelasController::class, 'create'])->name('jadwal-kelas.create');
        Route::get('/jadwal-kelas/{jadwalKelas}/edit', [JadwalKelasController::class, 'edit'])->name('jadwal-kelas.edit');
        Route::put('/jadwal-kelas/{jadwalKelas}', [JadwalKelasController::class, 'update'])->name('jadwal-kelas.update');
        Route::delete('/jadwal-kelas/{jadwalKelas}', [JadwalKelasController::class, 'destroy'])->name('jadwal-kelas.destroy');
        Route::patch('/jadwal-kelas/{jadwalKelas}/toggle-active', [JadwalKelasController::class, 'toggleActive'])
            ->name('jadwal-kelas.toggle-active');

        // Kelas AJAX (Admin Only)
        Route::prefix('kelas')->name('kelas.')->group(function () {
            Route::post('/store', [KelasController::class, 'store'])->name('store');
            Route::get('/jurusan', [KelasController::class, 'getJurusan'])->name('jurusan');
        });

        // Jurusan AJAX (Admin Only)
        Route::prefix('jurusan')->name('jurusan.')->group(function () {
            Route::post('/store', [JurusanController::class, 'store'])->name('store');
        });

        // Admin Routes
        Route::prefix('admin')->name('admin.')->group(function () {
            Route::get('/generate-qr', [AdminController::class, 'generateAdminQr'])->name('generate-qr');
            Route::delete('/delete-dummy', [AdminController::class, 'deleteDummyData'])->name('delete-dummy');
            Route::get('/download-qr', [AdminController::class, 'downloadQr'])->name('download-qr');
        });
    });
});

// Debug route (hanya untuk development)
if (config('app.debug')) {
    Route::get('/debug', function() {
        return view('debug.index');
    })->name('debug.index');
    
    Route::post('/debug/absensi', function(Request $request) {
        return response()->json([
            'request_data' => $request->all(),
            'headers' => $request->headers->all(),
            'method' => $request->method(),
            'url' => $request->url()
        ]);
    })->name('debug.absensi');
    
    // Test Timezone Route
    Route::get('/test-timezone', function() {
        return response()->json([
            'config_timezone' => config('app.timezone'),
            'php_timezone' => date_default_timezone_get(),
            'carbon_now' => \Carbon\Carbon::now(),
            'carbon_now_jakarta' => \Carbon\Carbon::now('Asia/Jakarta'),
            'php_date' => date('Y-m-d H:i:s'),
            'laravel_now' => now(),
            'laravel_today' => today(),
        ]);
    })->name('test.timezone');
    
    // Test Database Timezone
    Route::get('/test-db-timezone', function() {
        $latestAbsensi = \App\Models\Absensi::latest()->first();
        return response()->json([
            'current_time_system' => now(),
            'current_time_carbon' => \Carbon\Carbon::now(),
            'latest_absensi' => $latestAbsensi,
            'created_at_formatted' => $latestAbsensi ? $latestAbsensi->created_at->format('Y-m-d H:i:s T') : null,
            'tanggal_raw' => $latestAbsensi ? $latestAbsensi->tanggal : null,
        ]);
    })->name('test.db.timezone');
}
