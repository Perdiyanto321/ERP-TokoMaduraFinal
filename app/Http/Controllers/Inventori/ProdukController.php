<?php

namespace App\Http\Controllers\Inventori;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventori\PerbaruiProdukRequest;
use App\Http\Requests\Inventori\SimpanProdukRequest;
use App\Models\Kategori;
use App\Models\Produk;
use App\Services\InventoriService;
use App\Services\LogAktivitasService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProdukController extends Controller
{
    public function __construct(
        private readonly InventoriService $inventoriService,
        private readonly LogAktivitasService $logService
    ) {}

    public function index(Request $request): View
    {
        $query = Produk::with('kategori')->orderBy('nama');

        if ($request->filled('cari')) {
            $kata = '%' . $request->string('cari') . '%';
            $query->where(function ($q) use ($kata) {
                $q->where('nama', 'like', $kata)->orWhere('kode', 'like', $kata);
            });
        }

        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->integer('kategori_id'));
        }

        if ($request->boolean('menipis')) {
            $query->whereColumn('stok', '<=', 'stok_minimum');
        }

        $daftarProduk = $query->paginate(25)->withQueryString();
        $daftarKategori = Kategori::orderBy('nama')->get();

        return view('inventori.produk.index', compact('daftarProduk', 'daftarKategori'));
    }

    public function tambah(): View
    {
        $daftarKategori = Kategori::orderBy('nama')->get();

        return view('inventori.produk.form', [
            'produk' => new Produk([
                'isi_per_satuan_beli' => 1,
                'stok_minimum' => 5,
                'stok_target' => 20,
                'status' => 'aktif',
            ]),
            'daftarKategori' => $daftarKategori,
            'modeEdit' => false,
        ]);
    }

    public function simpan(SimpanProdukRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $stokAwal = (int) ($data['stok_awal'] ?? 0);
        $hppAwal = round((float) $data['harga_beli'] / (int) $data['isi_per_satuan_beli'], 2);

        DB::transaction(function () use ($data, $stokAwal, $hppAwal) {
            $produk = Produk::create([
                'kategori_id' => $data['kategori_id'],
                'kode' => strtoupper($data['kode']),
                'nama' => $data['nama'],
                'satuan' => $data['satuan'],
                'satuan_beli' => $data['satuan_beli'],
                'isi_per_satuan_beli' => $data['isi_per_satuan_beli'],
                'harga_beli' => $data['harga_beli'],
                'harga_jual' => $data['harga_jual'],
                'hpp_rata_rata' => $hppAwal,
                'stok' => 0,
                'stok_minimum' => $data['stok_minimum'],
                'stok_target' => $data['stok_target'],
                'status' => $data['status'],
            ]);

            if ($stokAwal > 0) {
                $this->inventoriService->ubahStok(
                    produkId: $produk->id,
                    qtyBertanda: $stokAwal,
                    jenis: 'stok_awal',
                    userId: Auth::id(),
                    sumberTipe: 'produk',
                    sumberId: $produk->id
                );
            }

            $this->logService->catat('buat_produk', 'produk', $produk->id, [
                'kode' => $produk->kode,
                'nama' => $produk->nama,
                'stok_awal' => $stokAwal,
            ]);
        });

        return redirect()->route('produk.index')->with('sukses', 'Produk baru berhasil disimpan.');
    }

    public function edit(Produk $produk): View
    {
        $daftarKategori = Kategori::orderBy('nama')->get();

        return view('inventori.produk.form', [
            'produk' => $produk,
            'daftarKategori' => $daftarKategori,
            'modeEdit' => true,
        ]);
    }

    public function perbarui(PerbaruiProdukRequest $request, Produk $produk): RedirectResponse
    {
        $data = $request->validated();
        $hargaJualLama = (float) $produk->harga_jual;

        $produk->update([
            'kategori_id' => $data['kategori_id'],
            'kode' => strtoupper($data['kode']),
            'nama' => $data['nama'],
            'satuan' => $data['satuan'],
            'satuan_beli' => $data['satuan_beli'],
            'isi_per_satuan_beli' => $data['isi_per_satuan_beli'],
            'harga_beli' => $data['harga_beli'],
            'harga_jual' => $data['harga_jual'],
            'stok_minimum' => $data['stok_minimum'],
            'stok_target' => $data['stok_target'],
            'status' => $data['status'],
        ]);

        $this->logService->catat('ubah_produk', 'produk', $produk->id, [
            'kode' => $produk->kode,
            'harga_jual_lama' => $hargaJualLama,
            'harga_jual_baru' => (float) $produk->harga_jual,
        ]);

        return redirect()->route('produk.index')->with('sukses', 'Data produk berhasil diperbarui.');
    }
}
