<?php

use App\Http\Controllers\Admin\LogAktivitasController;
use App\Http\Controllers\Admin\PenggunaController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DasborController;
use App\Http\Controllers\Inventori\KategoriController;
use App\Http\Controllers\Inventori\ProdukController;
use App\Http\Controllers\Inventori\StokController;
use App\Http\Controllers\Keuangan\BiayaOperasionalController;
use App\Http\Controllers\Keuangan\LaporanController;
use App\Http\Controllers\Keuangan\RekapKasController;
use App\Http\Controllers\Keuangan\UtangController;
use App\Http\Controllers\Pembelian\PembelianController;
use App\Http\Controllers\Pembelian\RestokController;
use App\Http\Controllers\Pembelian\SupplierController;
use App\Http\Controllers\Penjualan\KasbonController;
use App\Http\Controllers\Penjualan\KasirController;
use App\Http\Controllers\Penjualan\PenjualanController;
use App\Http\Controllers\Penjualan\ReturController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dasbor')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'tampilLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'prosesLogin'])->name('login.proses');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'peran'])->group(function () {

    Route::get('/dasbor', [DasborController::class, 'index'])->name('dasbor');

    Route::middleware('peran:admin')->group(function () {
        Route::get('/pengguna', [PenggunaController::class, 'index'])->name('pengguna.index');
        Route::post('/pengguna', [PenggunaController::class, 'simpan'])->name('pengguna.simpan');
        Route::get('/pengguna/{pengguna}/edit', [PenggunaController::class, 'edit'])->name('pengguna.edit');
        Route::put('/pengguna/{pengguna}', [PenggunaController::class, 'perbarui'])->name('pengguna.perbarui');

        Route::get('/log', [LogAktivitasController::class, 'index'])->name('log.index');
    });

    Route::middleware('peran:admin,kasir')->group(function () {
        Route::get('/kasir', [KasirController::class, 'index'])->name('kasir.index');
        Route::post('/kasir', [KasirController::class, 'simpan'])->name('kasir.simpan');

        Route::get('/penjualan', [PenjualanController::class, 'index'])->name('penjualan.index');
        Route::get('/penjualan/{penjualan}', [PenjualanController::class, 'detail'])->name('penjualan.detail');
        Route::post('/penjualan/{penjualan}/batalkan', [PenjualanController::class, 'batalkan'])->name('penjualan.batalkan');

        Route::get('/retur', [ReturController::class, 'index'])->name('retur.index');
        Route::get('/retur/buat/{penjualan}', [ReturController::class, 'tambah'])->name('retur.tambah');
        Route::post('/retur/buat/{penjualan}', [ReturController::class, 'simpan'])->name('retur.simpan');

        Route::get('/kasbon', [KasbonController::class, 'index'])->name('kasbon.index');
        Route::post('/kasbon/pelanggan', [KasbonController::class, 'simpanPelanggan'])->name('kasbon.pelanggan.simpan');
        Route::post('/kasbon/{piutang}/bayar', [KasbonController::class, 'bayarCicilan'])->name('kasbon.bayar');
    });

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

    Route::middleware('peran:admin,keuangan')->group(function () {
        Route::get('/utang', [UtangController::class, 'index'])->name('utang.index');
        Route::post('/utang/{utang}/bayar', [UtangController::class, 'bayar'])->name('utang.bayar');

        Route::get('/biaya', [BiayaOperasionalController::class, 'index'])->name('biaya.index');
        Route::post('/biaya', [BiayaOperasionalController::class, 'simpan'])->name('biaya.simpan');

        Route::get('/kas', [RekapKasController::class, 'index'])->name('kas.index');

        Route::get('/laporan/penjualan', [LaporanController::class, 'index'])->name('laporan.penjualan');
    });
});
