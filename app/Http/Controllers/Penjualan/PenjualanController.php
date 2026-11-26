<?php

namespace App\Http\Controllers\Penjualan;

use App\Http\Controllers\Controller;
use App\Models\Penjualan;
use App\Services\PenjualanService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PenjualanController extends Controller
{
    public function __construct(
        private readonly PenjualanService $penjualanService
    ) {}

    public function index(Request $request): View
    {
        $query = Penjualan::with(['kasir.karyawan', 'pelanggan'])
            ->withCount('detail')
            ->orderByDesc('tanggal')
            ->orderByDesc('id');

        if ($request->filled('cari')) {
            $query->where('nomor_struk', 'like', '%' . $request->string('cari') . '%');
        }

        if ($request->filled('metode')) {
            $query->where('metode_bayar', $request->string('metode'));
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->date('tanggal'));
        }

        $daftarPenjualan = $query->paginate(25)->withQueryString();

        return view('penjualan.transaksi.index', compact('daftarPenjualan'));
    }

    public function detail(Penjualan $penjualan): View
    {
        $penjualan->load([
            'kasir.karyawan',
            'pelanggan',
            'detail.produk',
            'piutang',
            'retur.detail.detailPenjualan.produk',
        ]);

        $bisaDiretur = $penjualan->status === 'selesai'
            && $penjualan->tanggal->diffInDays(now()) <= 10;

        return view('penjualan.transaksi.show', compact('penjualan', 'bisaDiretur'));
    }

    public function batalkan(Request $request, Penjualan $penjualan): RedirectResponse
    {
        if (! Auth::user()->hasRole('admin')) {
            abort(403, 'Hanya Admin yang dapat membatalkan transaksi penjualan.');
        }

        $data = $request->validate([
            'alasan' => ['required', 'string', 'min:5', 'max:300'],
        ]);

        try {
            $this->penjualanService->batalkanTransaksi($penjualan->id, $data['alasan'], Auth::id());
        } catch (DomainException $e) {
            return back()->with('galat', $e->getMessage());
        }

        return redirect()->route('penjualan.detail', $penjualan)->with('sukses', 'Transaksi berhasil dibatalkan dan stok dikembalikan.');
    }
}
