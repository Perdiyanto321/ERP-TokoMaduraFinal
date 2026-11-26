<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RekapKasController extends Controller
{
    public function index(Request $request): View
    {
        $dari = $request->get('dari', now()->startOfMonth()->toDateString());
        $sampai = $request->get('sampai', now()->toDateString());

        $query = Pembayaran::with(['user.karyawan', 'penjualan', 'pembelian'])
            ->whereDate('tanggal', '>=', $dari)
            ->whereDate('tanggal', '<=', $sampai)
            ->orderByDesc('tanggal')
            ->orderByDesc('id');

        $totalMasuk = (float) (clone $query)->where('arah', 'masuk')->sum('jumlah');
        $totalKeluar = (float) (clone $query)->where('arah', 'keluar')->sum('jumlah');
        $saldoBersih = $totalMasuk - $totalKeluar;

        $mutasiKas = $query->paginate(30)->withQueryString();

        return view('keuangan.kas.index', compact('mutasiKas', 'dari', 'sampai', 'totalMasuk', 'totalKeluar', 'saldoBersih'));
    }
}
