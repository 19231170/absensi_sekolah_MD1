<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\QrController;
use App\Http\Controllers\JadwalKelasController;
use Illuminate\Http\Request;

Route::get('/', function () {
    return redirect()->route('absensi.index');
});

// Route untuk Absensi
Route::prefix('absensi')->name('absensi.')->group(function () {
    Route::get('/', [AbsensiController::class, 'index'])->name('index');
    Route::post('/scan', [AbsensiController::class, 'scanQr'])->name('scan');
    Route::get('/laporan', [AbsensiController::class, 'laporan'])->name('laporan');
});

// Route untuk QR Code (Testing)
Route::prefix('qr')->name('qr.')->group(function () {
    Route::get('/', [QrController::class, 'index'])->name('index');
    Route::get('/{nis}', [QrController::class, 'show'])->name('show');
    Route::get('/download/{nis}', [QrController::class, 'download'])->name('download');
    Route::get('/image/{nis}', [QrController::class, 'image'])->name('image');
    Route::get('/download-all/zip', [QrController::class, 'downloadAll'])->name('download.all');
    Route::get('/download-all/pdf', [QrController::class, 'downloadAllPdf'])->name('download.all.pdf');
});

// Route untuk Jadwal Persesi (CRUD)
Route::resource('jadwal-kelas', JadwalKelasController::class)->parameters([
    'jadwal-kelas' => 'jadwalKelas'
]);
Route::patch('jadwal-kelas/{jadwalKelas}/toggle-active', [JadwalKelasController::class, 'toggleActive'])
    ->name('jadwal-kelas.toggle-active');

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
