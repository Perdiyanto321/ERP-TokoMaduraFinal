<?php

namespace App\Http\Controllers\Penjualan;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Pelanggan;
use App\Models\Piutang;
use App\Models\Produk;
use App\Services\PenjualanService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KasirController extends Controller
{
    public function __construct(
        private readonly PenjualanService $penjualanService
    ) {}

    public function index(): View
    {
        $daftarProduk = Produk::with('kategori')
            ->aktif()
            ->orderBy('nama')
            ->get(['id', 'kategori_id', 'kode', 'nama', 'satuan', 'harga_jual', 'stok']);

        $daftarKategori = Kategori::orderBy('nama')->get(['id', 'nama']);

        $piutangAktifMap = Piutang::whereIn('status', ['belum_lunas', 'sebagian'])
            ->selectRaw('pelanggan_id, SUM(sisa) as total_sisa')
            ->groupBy('pelanggan_id')
            ->pluck('total_sisa', 'pelanggan_id');

        $daftarPelanggan = Pelanggan::orderBy('nama')->get()->map(function (Pelanggan $p) use ($piutangAktifMap) {
            $terpakai = (float) ($piutangAktifMap[$p->id] ?? 0);

            return [
                'id' => $p->id,
                'nama' => $p->nama,
                'no_hp' => $p->no_hp,
                'batas_kasbon' => (float) $p->batas_kasbon,
                'piutang_aktif' => $terpakai,
                'sisa_limit' => max(0, (float) $p->batas_kasbon - $terpakai),
            ];
        });

        return view('penjualan.kasir.index', compact('daftarProduk', 'daftarKategori', 'daftarPelanggan'));
    }

    public function simpan(Request $request): JsonResponse
    {
        $data = $request->validate([
            'metode_bayar' => ['required', Rule::in(['tunai', 'transfer', 'kasbon'])],
            'pelanggan_id' => ['nullable', 'required_if:metode_bayar,kasbon', 'integer', Rule::exists('pelanggan', 'id')],
            'diskon' => ['nullable', 'numeric', 'min:0'],
            'jumlah_bayar' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.produk_id' => ['required', 'integer', Rule::exists('produk', 'id')],
            'items.*.qty' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $penjualan = $this->penjualanService->prosesTransaksi($data, Auth::id());

            return response()->json([
                'sukses' => true,
                'pesan' => "Transaksi {$penjualan->nomor_struk} berhasil disimpan.",
                'nomor_struk' => $penjualan->nomor_struk,
                'redirect_struk' => route('penjualan.detail', $penjualan),
            ]);
        } catch (DomainException $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => $e->getMessage(),
            ], 422);
        }
    }
}
