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
    if (Auth::check()) {
        $user = Auth::user();
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        } else {
            return redirect()->route('guru.dashboard');
        }
    }
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

// Simplified URL for QR Login
Route::get('/login/qr', function() {
    return redirect()->route('qr.login.form');
});

// Debug route untuk test QR data (no auth required)
if (config('app.debug')) {
    Route::get('/debug/qr-test/{kelasId}', function($kelasId) {
        try {
            $kelas = \App\Models\Kelas::findOrFail($kelasId);
            $siswaList = $kelas->siswa()->where('status_aktif', true)->limit(2)->get();
            
            $result = [
                'success' => true,
                'timestamp' => now()->format('Y-m-d H:i:s'),
                'kelas_info' => [
                    'id' => $kelas->id,
                    'tingkat' => $kelas->tingkat,
                    'nama_kelas' => $kelas->nama_kelas,
                    'jurusan' => $kelas->jurusan ? $kelas->jurusan->nama_jurusan : 'Unknown'
                ],
                'siswa_count' => $siswaList->count(),
                'sample_siswa' => $siswaList->map(function($siswa) {
                    return [
                        'nis' => $siswa->nis,
                        'nama' => $siswa->nama,
                        'has_qr' => !empty($siswa->qr_code),
                        'qr_preview' => substr($siswa->qr_code ?? '', 0, 50) . '...'
                    ];
                })
            ];
            
            return response()->json($result, 200, [], JSON_PRETTY_PRINT);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500, [], JSON_PRETTY_PRINT);
        }
    });
}

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

    // Absensi Per Pelajaran Routes (Admin & Guru)
    Route::prefix('absensi')->name('absensi.')->group(function () {
        Route::get('/laporan', [AbsensiController::class, 'laporan'])->name('laporan');
        
        // Absensi Per Pelajaran Routes
        Route::get('/pelajaran/{jadwalKelas}', [\App\Http\Controllers\AbsensiPelajaranController::class, 'index'])->name('pelajaran');
        Route::post('/pelajaran/scan', [\App\Http\Controllers\AbsensiPelajaranController::class, 'scanQr'])->name('pelajaran.scan');
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

    // JadwalKelas routes (basic views)
    Route::get('/jadwal-kelas', [JadwalKelasController::class, 'index'])->name('jadwal-kelas.index');
    Route::get('/jadwal-kelas/create', [JadwalKelasController::class, 'create'])->name('jadwal-kelas.create');
    Route::post('/jadwal-kelas', [JadwalKelasController::class, 'store'])->name('jadwal-kelas.store');
    // More specific routes first
    Route::get('/jadwal-kelas/guru/{id}', [JadwalKelasController::class, 'getGuruData'])->name('jadwal-kelas.guru-data');
    Route::get('/jadwal-kelas/{jadwalKelas}/edit', [JadwalKelasController::class, 'edit'])->name('jadwal-kelas.edit');
    Route::put('/jadwal-kelas/{jadwalKelas}', [JadwalKelasController::class, 'update'])->name('jadwal-kelas.update');
    Route::delete('/jadwal-kelas/{jadwalKelas}', [JadwalKelasController::class, 'destroy'])->name('jadwal-kelas.destroy');
    Route::patch('/jadwal-kelas/{jadwalKelas}/toggle-active', [JadwalKelasController::class, 'toggleActive'])
        ->name('jadwal-kelas.toggle-active');
    
    // Route show bisa diakses oleh admin dan guru
    // General route should be last to avoid conflicts
    Route::get('/jadwal-kelas/{jadwalKelas}', [JadwalKelasController::class, 'show'])->name('jadwal-kelas.show');

    // Admin Only Routes
    Route::middleware('role:admin')->group(function () {
        // Kelas Management Routes
        Route::resource('kelas', KelasController::class, ['parameters' => [
            'kelas' => 'kelas'  // Isso define o parâmetro 'kelas' na URL em vez de 'kela'
        ]]);
        Route::prefix('kelas')->name('kelas.')->group(function () {
            Route::get('/jurusan', [KelasController::class, 'getJurusan'])->name('jurusan');
            Route::get('/{kelas}/download-qr', [KelasController::class, 'downloadQrCodes'])->name('download.qr');
            Route::get('/{kelas}/download-qr-js', [KelasController::class, 'downloadQrCodesJs'])->name('download-qr-js');
            Route::get('/{kelasId}/qr-codes-data', [KelasController::class, 'getQrCodesData'])->name('qr-codes-data');
            Route::get('/{kelas}/download-multiple', [KelasController::class, 'downloadMultipleQr'])->name('download-multiple');
            Route::get('/{kelas}/qr-codes', [KelasController::class, 'showQrCodes'])->name('qr.show');
            Route::get('/{kelas}/siswa/{siswa}/qr', [KelasController::class, 'downloadStudentQr'])->name('siswa.qr')->where('siswa', '[A-Za-z0-9]+');
            
            // Global download routes untuk semua siswa
            Route::get('/download/all/zip', [KelasController::class, 'downloadAllSiswaZip'])->name('download.all.zip');
            Route::get('/download/all/pdf', [KelasController::class, 'downloadAllSiswaPdf'])->name('download.all.pdf');
        });

        // Jurusan Management Routes
        Route::resource('jurusan', JurusanController::class, ['parameters' => [
            'jurusan' => 'jurusan'  // Isso define o parâmetro 'jurusan' na URL em vez de 'jurusan'
        ]]);
        Route::prefix('jurusan')->name('jurusan.')->group(function () {
            Route::post('/store-form', [JurusanController::class, 'storeForm'])->name('storeForm');
            Route::get('/get-all', [JurusanController::class, 'getAllJurusan'])->name('getAll');
        });

        // Admin Routes
        Route::prefix('admin')->name('admin.')->group(function () {
            Route::get('/generate-qr', [AdminController::class, 'generateAdminQr'])->name('generate-qr');
            Route::delete('/delete-dummy', [AdminController::class, 'deleteDummyData'])->name('delete-dummy');
            Route::get('/download-qr', [AdminController::class, 'downloadQr'])->name('download-qr');
        });
        
        // Siswa Management Routes (Admin Only)
        Route::resource('siswa', \App\Http\Controllers\SiswaController::class);
        Route::prefix('siswa')->name('siswa.')->group(function () {
            Route::get('/import/form', [\App\Http\Controllers\SiswaController::class, 'importForm'])->name('import');
            Route::post('/import/excel', [\App\Http\Controllers\SiswaController::class, 'importExcel'])->name('import.excel');
            Route::get('/template/download', [\App\Http\Controllers\SiswaController::class, 'downloadTemplate'])->name('template.download');
            Route::get('/template/download/excel', [\App\Http\Controllers\SiswaController::class, 'downloadTemplateExcel'])->name('template.download.excel');
        });

        // Guru Management Routes (Admin Only)
        Route::resource('guru', \App\Http\Controllers\GuruController::class)->except(['show']);
        Route::prefix('guru')->name('guru.')->group(function() {
            Route::get('/{guru}/qr', [\App\Http\Controllers\GuruController::class, 'downloadQr'])->name('qr');
            Route::get('/download/all/zip', [\App\Http\Controllers\GuruController::class, 'downloadAllZip'])->name('download.all.zip');
            Route::get('/download/all/pdf', [\App\Http\Controllers\GuruController::class, 'downloadAllPdf'])->name('download.all.pdf');
        });
    });
});

// Debug route (hanya untuk development)
if (config('app.debug')) {
    Route::get('/debug', function() {
        return view('debug.index');
    })->name('debug.index');
    
    // Debug auth & routes
    Route::get('/debug-auth', function() {
        return response()->json([
            'authenticated' => Auth::check(),
            'user' => Auth::user(),
            'role' => Auth::user()?->role ?? 'no user',
            'routes_exist' => [
                'jadwal-kelas.index' => route('jadwal-kelas.index'),
                'jadwal-kelas.create' => Auth::check() && Auth::user()?->role === 'admin' ? route('jadwal-kelas.create') : 'Not accessible - need admin role'
            ]
        ]);
    })->name('debug.auth');
    
    // Test route tanpa middleware
    Route::get('/test-create', [JadwalKelasController::class, 'create'])->name('test.create');
    
    // Debug middleware route
    Route::get('/debug-middleware', function() {
        return response()->json([
            'auth_check' => Auth::check(),
            'user' => Auth::user(),
            'role' => Auth::user()?->role ?? 'no user',
            'middleware_passed' => 'This route has no middleware protection'
        ]);
    })->name('debug.middleware');
    
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
    
    // Test Import View
    Route::get('/test-import-view', function() {
        // Set up the session with the correct variables
        session()->flash('import_errors', [
            'Row 1: Invalid data',
            'Row 2: Missing required field'
        ]);
        
        // Render the view
        return view('siswa.import');
    })->name('test.import.view');
}
