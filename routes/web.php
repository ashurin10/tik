<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CaptchaController;
use Illuminate\Support\Facades\Route;

Route::get('/captcha', [CaptchaController::class, 'generate'])->name('captcha.generate');

Route::redirect('/', '/login');


use App\Http\Controllers\LaporanMingguanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\AsetTikController;

use App\Http\Controllers\DatabaseBackupController;

Route::get('/dashboard', [ServiceController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/portal', [ServiceController::class, 'index'])->middleware(['auth', 'verified'])->name('portal');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/database-backup', [DatabaseBackupController::class, 'index'])->name('database-backup.index');
    Route::post('/database-backup', [DatabaseBackupController::class, 'store'])->name('database-backup.store');

    Route::prefix('laporan-mingguan')->name('laporan-mingguan.')->group(function () {
        Route::get('/dashboard', [LaporanMingguanController::class, 'dashboard'])->name('dashboard');
        Route::get('/export', [LaporanMingguanController::class, 'export'])->name('export');
        Route::get('/pics/search', [LaporanMingguanController::class, 'searchPics'])->name('pics.search');
        Route::get('/kegiatan/search', [LaporanMingguanController::class, 'searchKegiatan'])->name('kegiatan.search');
        Route::post('/parse-text', [LaporanMingguanController::class, 'parseText'])->name('parse-text');
        Route::post('/bulk-store', [LaporanMingguanController::class, 'bulkStore'])->name('bulk-store');

        // Admin-only: reset semua data laporan mingguan
        Route::delete('/reset-all', [LaporanMingguanController::class, 'resetAll'])->name('reset-all');

        Route::get('/', [LaporanMingguanController::class, 'index'])->name('index');
        Route::post('/', [LaporanMingguanController::class, 'store'])->name('store');
        Route::put('/{laporanMingguan}', [LaporanMingguanController::class, 'update'])->name('update');
        Route::delete('/{laporanMingguan}', [LaporanMingguanController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('aset-tik')->name('aset-tik.')->group(function () {
        Route::get('/', [AsetTikController::class, 'page'])->name('dashboard')->defaults('page', 'dashboard');
        Route::post('/master/{type}', [AsetTikController::class, 'storeMaster'])->name('master.store');
        Route::put('/master/{type}/{item}', [AsetTikController::class, 'updateMaster'])->name('master.update');
        Route::delete('/master/{type}/{item}', [AsetTikController::class, 'destroyMaster'])->name('master.destroy');
        Route::post('/data-aset', [AsetTikController::class, 'storeAset'])->name('data-aset.store');
        Route::put('/data-aset/{aset}', [AsetTikController::class, 'updateAset'])->name('data-aset.update');
        Route::delete('/data-aset/{aset}', [AsetTikController::class, 'destroyAset'])->name('data-aset.destroy');
        Route::get('/data-aset/{aset}/label', [AsetTikController::class, 'labelAset'])->name('data-aset.label');
        Route::post('/transaksi/{type}', [AsetTikController::class, 'storeTransaksi'])->name('transaksi.store');
        Route::delete('/transaksi/{transaksi}', [AsetTikController::class, 'destroyTransaksi'])->name('transaksi.destroy');
        foreach ([
            'kategori',
            'data-aset',
            'lokasi',
            'penanggung-jawab',
            'vendor',
            'aset-masuk',
            'aset-keluar',
            'mutasi',
            'maintenance',
            'penghapusan',
            'riwayat',
            'qr-tracking',
            'laporan-aset-masuk',
            'laporan-aset-keluar',
            'laporan-stok',
            'laporan-terpakai',
        ] as $page) {
            Route::get('/' . $page, [AsetTikController::class, 'page'])->name($page)->defaults('page', $page);
        }
    });
    
    // User Management Route
    Route::resource('users', UserController::class);
    
    // Menu Management Route
    // Menu Management Route
    Route::resource('menus', MenuController::class);
    
    // Modul URL Shortener
    Route::resource('urls', \App\Http\Controllers\UrlController::class);

    Route::post('/portal/services', [ServiceController::class, 'store'])->name('services.store');
    Route::put('/portal/services/{service}', [ServiceController::class, 'update'])->name('services.update');
    Route::delete('/portal/services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');
    
});

// Modul URL Public Redirect
Route::get('/go/{shortCode}', [\App\Http\Controllers\UrlController::class, 'redirect'])->name('urls.redirect');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
