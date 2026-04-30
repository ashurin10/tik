<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CaptchaController;
use Illuminate\Support\Facades\Route;

Route::get('/captcha', [CaptchaController::class, 'generate'])->name('captcha.generate');

Route::redirect('/', '/login');


use App\Http\Controllers\LaporanMingguanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MenuController;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaporanAktivitasKerjaController;

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    // Laporan Aktivitas Kerja
    Route::get('/laporan-aktivitas-kerja', [LaporanAktivitasKerjaController::class, 'index'])->name('laporan-aktivitas-kerja.index');
    Route::get('/laporan-aktivitas-kerja/show', [LaporanAktivitasKerjaController::class, 'show'])->name('laporan-aktivitas-kerja.show');
    Route::get('/laporan-aktivitas-kerja/cetak', [LaporanAktivitasKerjaController::class, 'cetak'])->name('laporan-aktivitas-kerja.cetak');
    Route::post('/laporan-aktivitas-kerja/sync', [LaporanAktivitasKerjaController::class, 'sync'])->name('laporan-aktivitas-kerja.sync');
    Route::post('/laporan-aktivitas-kerja/reset', [LaporanAktivitasKerjaController::class, 'reset'])->name('laporan-aktivitas-kerja.reset');
    Route::post('/laporan-aktivitas-kerja/store', [LaporanAktivitasKerjaController::class, 'store'])->name('laporan-aktivitas-kerja.store');
    Route::post('/laporan-aktivitas-kerja/copy-multiple', [LaporanAktivitasKerjaController::class, 'copyMultiple'])->name('laporan-aktivitas-kerja.copy-multiple');
    Route::put('/laporan-aktivitas-kerja/{laporanAktivitasKerja}', [LaporanAktivitasKerjaController::class, 'update'])->name('laporan-aktivitas-kerja.update');
    Route::delete('/laporan-aktivitas-kerja/{laporanAktivitasKerja}', [LaporanAktivitasKerjaController::class, 'destroy'])->name('laporan-aktivitas-kerja.destroy');

    Route::prefix('laporan-mingguan')->name('laporan-mingguan.')->group(function () {
        Route::get('/dashboard', [LaporanMingguanController::class, 'dashboard'])->name('dashboard');
        Route::get('/export', [LaporanMingguanController::class, 'export'])->name('export');
        Route::get('/pics/search', [LaporanMingguanController::class, 'searchPics'])->name('pics.search');
        Route::get('/kegiatan/search', [LaporanMingguanController::class, 'searchKegiatan'])->name('kegiatan.search');
        Route::post('/parse-text', [LaporanMingguanController::class, 'parseText'])->name('parse-text');
        Route::post('/bulk-store', [LaporanMingguanController::class, 'bulkStore'])->name('bulk-store');
        
        Route::get('/', [LaporanMingguanController::class, 'index'])->name('index');
        Route::post('/', [LaporanMingguanController::class, 'store'])->name('store');
        Route::put('/{laporanMingguan}', [LaporanMingguanController::class, 'update'])->name('update');
        Route::delete('/{laporanMingguan}', [LaporanMingguanController::class, 'destroy'])->name('destroy');
    });
    
    // User Management Route
    Route::resource('users', UserController::class);
    
    // Menu Management Route
    // Menu Management Route
    Route::resource('menus', MenuController::class);
    
    // Modul URL Shortener
    Route::resource('urls', \App\Http\Controllers\UrlController::class);
    
    // Modul Inventaris (Barang Habis Pakai)
    Route::resource('inventaris', \App\Http\Controllers\Inventaris\InventarisController::class)->parameters(['inventaris' => 'inventaris']);
    Route::post('/inventaris/{inventaris}/transaksi', [\App\Http\Controllers\Inventaris\TransaksiInventarisController::class, 'store'])->name('inventaris.transaksi.store');
});

// Modul URL Public Redirect
Route::get('/go/{shortCode}', [\App\Http\Controllers\UrlController::class, 'redirect'])->name('urls.redirect');

// Sistem Pendataan Aset TIK (ISO 27001 compliant)
Route::middleware(['auth', 'verified'])->prefix('aset')->name('aset.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\Asset\DashboardController::class, 'index'])->name('dashboard');

    // Modul 1: Master Data Aset
    Route::prefix('master')->name('master.')->group(function () {
        Route::get('data/template-import', [\App\Http\Controllers\Asset\MasterAsetController::class, 'templateImport'])->name('data.template');
        Route::post('data/import', [\App\Http\Controllers\Asset\MasterAsetController::class, 'import'])->name('data.import');
        Route::get('data/{asetTik}/print-label', [\App\Http\Controllers\Asset\MasterAsetController::class, 'printLabel'])->name('data.print-label');
        Route::resource('data', \App\Http\Controllers\Asset\MasterAsetController::class)->parameters(['data' => 'asetTik']);

        // Placeholder for Master Kategori & Lokasi so the sidebar doesn't crash from missing route
        Route::get('kategori', [\App\Http\Controllers\Asset\DashboardController::class, 'kategoriPlaceholder'])->name('kategori.index');
    });

    // Modul 3: Mutasi
    Route::prefix('mutasi')->name('mutasi.')->group(function () {
        Route::get('/penerimaan', [\App\Http\Controllers\Asset\MutasiController::class, 'penerimaan'])->name('penerimaan.index');
        Route::get('/penerimaan/create', [\App\Http\Controllers\Asset\MutasiController::class, 'createPenerimaan'])->name('penerimaan.create');
        Route::post('/penerimaan/store', [\App\Http\Controllers\Asset\MutasiController::class, 'storePenerimaanAset'])->name('penerimaan.storeAset');
        Route::post('/penerimaan/import', [\App\Http\Controllers\Asset\MutasiController::class, 'importPenerimaan'])->name('penerimaan.import');
        Route::get('/check-out', [\App\Http\Controllers\Asset\MutasiController::class, 'checkout'])->name('checkout.index');
        Route::post('/check-out', [\App\Http\Controllers\Asset\MutasiController::class, 'storeCheckout'])->name('checkout.store');
        Route::get('/check-in', [\App\Http\Controllers\Asset\MutasiController::class, 'checkin'])->name('checkin.index');
        Route::post('/check-in', [\App\Http\Controllers\Asset\MutasiController::class, 'storeCheckin'])->name('checkin.store');
        Route::get('/approval', [\App\Http\Controllers\Asset\MutasiController::class, 'approval'])->name('approval.index');
    });

    // Modul 4: Pemeliharaan
    Route::prefix('maintenance')->name('maintenance.')->group(function () {
        Route::get('/jadwal', [\App\Http\Controllers\Asset\MaintenanceController::class, 'jadwal'])->name('jadwal.index');
        Route::get('/kondisi', [\App\Http\Controllers\Asset\MaintenanceController::class, 'kondisi'])->name('kondisi.index');
    });

    // Modul 5: Laporan & Audit
    Route::prefix('audit')->name('audit.')->group(function () {
        Route::get('/log', [\App\Http\Controllers\Asset\ReportController::class, 'log'])->name('log.index');
        Route::get('/opname', [\App\Http\Controllers\Asset\ReportController::class, 'opname'])->name('opname.index');
        Route::get('/rekap', [\App\Http\Controllers\Asset\ReportController::class, 'rekap'])->name('rekap.index');
    });
});




Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
