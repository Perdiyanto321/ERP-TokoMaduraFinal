<?php

namespace App\Http\Controllers\Pembelian;

use App\Http\Controllers\Controller;
use App\Models\Pembelian;
use App\Models\Produk;
use App\Models\Supplier;
use App\Services\PembelianService;
use App\Services\RestokService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PembelianController extends Controller
{
    public function __construct(
        private readonly PembelianService $pembelianService,
        private readonly RestokService $restokService
    ) {}

    public function index(Request $request): View
    {
        $query = Pembelian::with(['supplier', 'pembuat.karyawan'])
            ->orderByDesc('tanggal')
            ->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $daftarPo = $query->paginate(25)->withQueryString();

        return view('pembelian.po.index', compact('daftarPo'));
    }

    public function tambah(Request $request): View
    {
        $daftarSupplier = Supplier::orderBy('nama')->get();
        $daftarProduk = Produk::aktif()->orderBy('nama')->get();

        // Jika dibuka dari tombol "Buat PO dari Saran Restok"
        $itemAwal = [];
        if ($request->boolean('dari_restok')) {
            $saran = $this->restokService->daftarSaranRestok();
            foreach ($saran as $s) {
                $itemAwal[] = [
                    'produk_id' => $s->produk->id,
                    'qty_pesan' => $s->saran_satuan_beli,
                    'harga_beli' => (float) $s->produk->harga_beli,
                ];
            }
        }

        if (empty($itemAwal)) {
            $itemAwal[] = ['produk_id' => '', 'qty_pesan' => 1, 'harga_beli' => 0];
        }

        return view('pembelian.po.create', compact('daftarSupplier', 'daftarProduk', 'itemAwal'));
    }

    public function simpan(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'integer', Rule::exists('supplier', 'id')],
            'tanggal' => ['required', 'date'],
            'jenis_bayar' => ['required', Rule::in(['tunai', 'kredit'])],
            'jatuh_tempo' => ['nullable', 'required_if:jenis_bayar,kredit', 'date', 'after_or_equal:tanggal'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.produk_id' => ['required', 'integer', Rule::exists('produk', 'id')],
            'items.*.qty_pesan' => ['required', 'integer', 'min:1'],
            'items.*.harga_beli' => ['required', 'numeric', 'min:0'],
        ], [
            'jatuh_tempo.required_if' => 'Tanggal jatuh tempo wajib diisi untuk pembelian kredit.',
        ]);

        try {
            $po = $this->pembelianService->buatPo($data, $data['items'], Auth::id());
        } catch (DomainException $e) {
            return back()->withInput()->with('galat', $e->getMessage());
        }

        return redirect()->route('po.detail', $po)->with('sukses', "Purchase Order {$po->nomor_po} berhasil dibuat.");
    }

    public function detail(Pembelian $po): View
    {
        $po->load([
            'supplier',
            'pembuat.karyawan',
            'penyetuju.karyawan',
            'detail.produk',
            'detail.detailPenerimaan',
            'penerimaan.penerima.karyawan',
            'pembayaran',
        ]);

        return view('pembelian.po.show', compact('po'));
    }

    public function ubahStatus(Request $request, Pembelian $po): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['disetujui', 'dikirim', 'dibatalkan'])],
        ]);

        if (in_array($data['status'], ['disetujui', 'dibatalkan'], true) && ! Auth::user()->hasRole('admin')) {
            abort(403, 'Hanya Admin yang dapat menyetujui atau membatalkan PO.');
        }

        try {
            $this->pembelianService->ubahStatus($po->id, $data['status'], Auth::id());
        } catch (DomainException $e) {
            return back()->with('galat', $e->getMessage());
        }

        return back()->with('sukses', 'Status Purchase Order berhasil diperbarui.');
    }

    public function formTerima(Pembelian $po): View
    {
        $po->load(['supplier', 'detail.produk', 'detail.detailPenerimaan']);

        return view('pembelian.po.terima', compact('po'));
    }

    public function prosesTerima(Request $request, Pembelian $po): RedirectResponse
    {
        $data = $request->validate([
            'terima' => ['required', 'array'],
            'terima.*' => ['nullable', 'integer', 'min:0'],
        ]);

        try {
            $grn = $this->pembelianService->terimaBarang($po->id, $data['terima'], Auth::id());
        } catch (DomainException $e) {
            return back()->withInput()->with('galat', $e->getMessage());
        }

        return redirect()->route('po.detail', $po)->with('sukses', "Penerimaan {$grn->nomor} dicatat. Stok dan HPP produk telah diperbarui.");
    }
}
