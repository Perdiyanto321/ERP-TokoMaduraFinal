<?php

namespace App\Http\Controllers\Penjualan;

use App\Http\Controllers\Controller;
use App\Models\DetailRetur;
use App\Models\Penjualan;
use App\Models\ReturPenjualan;
use App\Services\ReturService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReturController extends Controller
{
    public function __construct(
        private readonly ReturService $returService
    ) {}

    public function index(Request $request): View
    {
        $daftarRetur = ReturPenjualan::with(['penjualan.pelanggan', 'pemroses.karyawan'])
            ->withCount('detail')
            ->orderByDesc('tanggal')
            ->paginate(20);

        // Transaksi selesai yang masih masuk masa retur D-10
        $transaksiEligibel = Penjualan::with('pelanggan')
            ->where('status', 'selesai')
            ->where('tanggal', '>=', now()->subDays(ReturService::BATAS_HARI_RETUR))
            ->orderByDesc('tanggal')
            ->limit(30)
            ->get();

        return view('penjualan.retur.index', compact('daftarRetur', 'transaksiEligibel'));
    }

    public function tambah(Penjualan $penjualan): View|RedirectResponse
    {
        if ($penjualan->status !== 'selesai' || $penjualan->tanggal->diffInDays(now()) > ReturService::BATAS_HARI_RETUR) {
            return redirect()->route('retur.index')->with('galat', 'Transaksi ini tidak memenuhi syarat retur (maksimal 10 hari dan berstatus selesai).');
        }

        $penjualan->load(['detail.produk', 'pelanggan', 'piutang']);

        $sudahDireturMap = DetailRetur::whereIn('detail_penjualan_id', $penjualan->detail->pluck('id'))
            ->selectRaw('detail_penjualan_id, SUM(qty_retur) as total_retur')
            ->groupBy('detail_penjualan_id')
            ->pluck('total_retur', 'detail_penjualan_id');

        return view('penjualan.retur.create', compact('penjualan', 'sudahDireturMap'));
    }

    public function simpan(Request $request, Penjualan $penjualan): RedirectResponse
    {
        $data = $request->validate([
            'alasan' => ['required', 'string', 'min:5', 'max:500'],
            'retur' => ['required', 'array'],
            'retur.*.qty' => ['nullable', 'integer', 'min:0'],
            'retur.*.kondisi' => ['nullable', 'in:layak_jual,rusak'],
        ]);

        try {
            $retur = $this->returService->prosesRetur($penjualan->id, $data['retur'], $data['alasan'], Auth::id());
        } catch (DomainException $e) {
            return back()->withInput()->with('galat', $e->getMessage());
        }

        return redirect()->route('retur.index')->with('sukses', "Dokumen retur {$retur->nomor} berhasil diproses.");
    }
}
