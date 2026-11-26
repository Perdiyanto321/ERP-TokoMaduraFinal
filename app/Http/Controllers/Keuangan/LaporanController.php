<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\DetailPenjualan;
use App\Models\Pengeluaran;
use App\Models\Penjualan;
use App\Models\Produk;
use App\Models\ReturPenjualan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LaporanController extends Controller
{
    public function index(Request $request): View
    {
        $dari = $request->get('dari', now()->startOfMonth()->toDateString());
        $sampai = $request->get('sampai', now()->toDateString());

        $omzetKotor = (float) Penjualan::where('status', 'selesai')
            ->whereDate('tanggal', '>=', $dari)
            ->whereDate('tanggal', '<=', $sampai)
            ->sum('total');

        $totalRetur = (float) ReturPenjualan::whereDate('tanggal', '>=', $dari)
            ->whereDate('tanggal', '<=', $sampai)
            ->sum('total_retur');

        $omzetBersih = $omzetKotor - $totalRetur;

        $hppTerjual = (float) DetailPenjualan::whereHas('penjualan', function ($q) use ($dari, $sampai) {
            $q->where('status', 'selesai')
                ->whereDate('tanggal', '>=', $dari)
                ->whereDate('tanggal', '<=', $sampai);
        })->selectRaw('COALESCE(SUM(qty * hpp_saat_itu), 0) as total_hpp')->value('total_hpp');

        $labaKotor = $omzetBersih - $hppTerjual;

        $biayaOperasional = (float) Pengeluaran::whereDate('tanggal', '>=', $dari)
            ->whereDate('tanggal', '<=', $sampai)
            ->sum('nominal');

        $labaBersih = $labaKotor - $biayaOperasional;

        $produkTerlaris = DetailPenjualan::with('produk')
            ->whereHas('penjualan', function ($q) use ($dari, $sampai) {
                $q->where('status', 'selesai')
                    ->whereDate('tanggal', '>=', $dari)
                    ->whereDate('tanggal', '<=', $sampai);
            })
            ->selectRaw('produk_id, SUM(qty) as total_qty, SUM(subtotal) as total_omzet, SUM(qty * hpp_saat_itu) as total_hpp')
            ->groupBy('produk_id')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        $valuasiStok = (float) Produk::aktif()
            ->selectRaw('COALESCE(SUM(stok * hpp_rata_rata), 0) as nilai_aset')
            ->value('nilai_aset');

        return view('keuangan.laporan.index', compact(
            'dari',
            'sampai',
            'omzetKotor',
            'totalRetur',
            'omzetBersih',
            'hppTerjual',
            'labaKotor',
            'biayaOperasional',
            'labaBersih',
            'produkTerlaris',
            'valuasiStok'
        ));
    }
}
