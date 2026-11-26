<?php

use App\Http\Controllers\Admin\LogAktivitasController;
use App\Http\Controllers\Admin\PenggunaController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DasborController;
use App\Http\Controllers\Inventori\KategoriController;
use App\Http\Controllers\Inventori\ProdukController;
use App\Http\Controllers\Inventori\StokController;
use App\Http\Controllers\Pembelian\PembelianController;
use App\Http\Controllers\Pembelian\RestokController;
use App\Http\Controllers\Pembelian\SupplierController;
use Illuminate\Support\Facades\Route;

// Halaman Awal -> Arahkan ke Login atau Dasbor
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dasbor')
        : redirect()->route('login');
});

// Autentikasi
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'tampilLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'prosesLogin'])->name('login.proses');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Area Terproteksi Login
Route::middleware(['auth', 'peran'])->group(function () {

    // Dasbor Sementara (akan diganti DasborController penuh pada tahap berikutnya)
    Route::get('/dasbor', [DasborController::class, 'index'])->name('dasbor');

    // Modul Administrasi (Khusus Admin)
    Route::middleware('peran:admin')->group(function () {
        Route::get('/pengguna', [PenggunaController::class, 'index'])->name('pengguna.index');
        Route::post('/pengguna', [PenggunaController::class, 'simpan'])->name('pengguna.simpan');
        Route::get('/pengguna/{pengguna}/edit', [PenggunaController::class, 'edit'])->name('pengguna.edit');
        Route::put('/pengguna/{pengguna}', [PenggunaController::class, 'perbarui'])->name('pengguna.perbarui');

        Route::get('/log', [LogAktivitasController::class, 'index'])->name('log.index');
    });

    // Modul Inventori & Pengadaan (Admin & Gudang)
    Route::middleware('peran:admin,gudang')->group(function () {
        Route::get('/kategori', [KategoriController::class, 'index'])->name('kategori.index');
        Route::post('/kategori', [KategoriController::class, 'simpan'])->name('kategori.simpan');
        Route::put('/kategori/{kategori}', [KategoriController::class, 'perbarui'])->name('kategori.perbarui');

        Route::get('/produk', [ProdukController::class, 'index'])->name('produk.index');
        Route::get('/produk/tambah', [ProdukController::class, 'tambah'])->name('produk.tambah');
        Route::post('/produk', [ProdukController::class, 'simpan'])->name('produk.simpan');
        Route::get('/produk/{produk}/edit', [ProdukController::class, 'edit'])->name('produk.edit');
        Route::put('/produk/{produk}', [ProdukController::class, 'perbarui'])->name('produk.perbarui');

        Route::get('/stok', [StokController::class, 'index'])->name('stok.index');
        Route::post('/stok/penyesuaian', [StokController::class, 'ajukanPenyesuaian'])->name('stok.penyesuaian.ajukan');
        Route::post('/stok/penyesuaian/{penyesuaian}/proses', [StokController::class, 'prosesPenyesuaian'])->name('stok.penyesuaian.proses');

        Route::get('/supplier', [SupplierController::class, 'index'])->name('supplier.index');
        Route::post('/supplier', [SupplierController::class, 'simpan'])->name('supplier.simpan');
        Route::put('/supplier/{supplier}', [SupplierController::class, 'perbarui'])->name('supplier.perbarui');

        Route::get('/restok', [RestokController::class, 'index'])->name('restok.index');

        Route::get('/po', [PembelianController::class, 'index'])->name('po.index');
        Route::get('/po/tambah', [PembelianController::class, 'tambah'])->name('po.tambah');
        Route::post('/po', [PembelianController::class, 'simpan'])->name('po.simpan');
        Route::get('/po/{po}', [PembelianController::class, 'detail'])->name('po.detail');
        Route::post('/po/{po}/status', [PembelianController::class, 'ubahStatus'])->name('po.status');
        Route::get('/po/{po}/terima', [PembelianController::class, 'formTerima'])->name('po.terima.form');
        Route::post('/po/{po}/terima', [PembelianController::class, 'prosesTerima'])->name('po.terima.proses');
    });

    // Placeholder Route agar Sidebar tidak error sebelum Step 16-18 selesai
    $belumDibangun = fn () => redirect()->route('dasbor')->with('galat', 'Modul ini sedang disiapkan pada langkah berikutnya.');
    Route::get('/kasir', $belumDibangun)->name('kasir.index');
    Route::get('/penjualan', $belumDibangun)->name('penjualan.index');
    Route::get('/retur', $belumDibangun)->name('retur.index');
    Route::get('/kasbon', $belumDibangun)->name('kasbon.index');
    Route::get('/utang', $belumDibangun)->name('utang.index');
    Route::get('/biaya', $belumDibangun)->name('biaya.index');
    Route::get('/kas', $belumDibangun)->name('kas.index');
    Route::get('/laporan/penjualan', $belumDibangun)->name('laporan.penjualan');
});
