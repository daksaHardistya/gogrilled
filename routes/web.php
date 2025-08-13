<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\custommerController;
use Illuminate\Support\Facades\Route;

// Halaman Depan (Customer)
Route::get('/', fn() => view('index'));
Route::get('/paket', [custommerController::class, 'showPakets'])->name('pakets.show');
Route::get('/produk', [custommerController::class, 'showProduk'])->name('produk.show');
Route::get('/form', [custommerController::class, 'showForm'])->name('form.show');
Route::get('/metodePembayaran', [custommerController::class, 'showMetodePembayaran'])->name('metodePembayaran.show');
Route::get('/pembayaranTransfer', [custommerController::class, 'showPembayaranTransfer'])->name('pembayaranTransfer.show');
Route::post('/upload-bukti', [custommerController::class, 'uploadBuktiTf'])->name('upload.bukti');
Route::get('/invoice', [custommerController::class, 'showInvoice'])->name('invoice.show');
Route::post('/simpanTransaksi', [custommerController::class, 'simpanOrderan']);
Route::get('/cart', [custommerController::class, 'showCart'])->name('cart.show');
Route::post('/cek-nomor-wa', [CustommerController::class, 'cekNomorWA']);

Route::get('/admin', function () {
    return redirect()->route('admin.login');
});

// Grup admin
Route::prefix('admin')->name('admin.')->group(function () {
    // Login & Logout (tidak butuh middleware)
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    // Semua halaman admin diproteksi auth:admin
    Route::middleware('auth.admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/order', [AdminController::class, 'orderShow'])->name('order.show');
        Route::put('/order/{id}/update', [AdminController::class, 'orderUpdate'])->name('order.update');
        Route::get('/pelanggan/{id}', [AdminController::class, 'pelangganDetail'])->name('pelanggan.detail');

        // Paket
        Route::get('/paket', [AdminController::class, 'paketShow'])->name('paket.show');
        Route::get('/paket/create', [AdminController::class, 'paketCreate'])->name('paket.create');
        Route::post('/paket/store', [AdminController::class, 'paketStore'])->name('paket.store');
        Route::get('/paket/{id_paket}/edit', [AdminController::class, 'paketEdit'])->name('paket.edit');
        Route::put('/paket/{id}/update', [AdminController::class, 'paketUpdate'])->name('paket.update');
        Route::get('/paket/{id}/delete', [AdminController::class, 'paketDelete'])->name('paket.delete');
        Route::put('/paket/update-stock/{id}', [AdminController::class, 'updateStockPaket'])->name('paket.stock.update');

        // Produk
        Route::get('/produk', [AdminController::class, 'produkShow'])->name('produk.show');
        Route::get('/produk/create', [AdminController::class, 'produkCreate'])->name('produk.create');
        Route::post('/produk/store', [AdminController::class, 'produkStore'])->name('produk.store');
        Route::get('/produk/{id}/edit', [AdminController::class, 'produkEdit'])->name('produk.edit');
        Route::put('/produk/{id}/update', [AdminController::class, 'produkUpdate'])->name('produk.update');
        Route::get('/produk/{id}/delete', [AdminController::class, 'produkDelete'])->name('produk.delete');
        Route::put('/produk/update-stock/{id}', [AdminController::class, 'updateStockProduk'])->name('produk.stock.update');

        // Pembukuan
        Route::get('/pembukuan', [AdminController::class, 'pembukuanShow'])->name('pembukuan.show');
    });
});
