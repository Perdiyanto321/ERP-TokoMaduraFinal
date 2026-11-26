<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DasborController extends Controller
{
    public function index(Request $request): View
    {
        $periode = $request->get('periode', 'bulan');
        if (! in_array($periode, ['hari', 'minggu', 'bulan', 'tahun'], true)) {
            $periode = 'bulan';
        }

        $sekarang = Carbon::now();

        [$mulaiIni, $selesaiIni, $mulaiLalu, $selesaiLalu, $labelPeriode] = match ($periode) {
            'hari' => [
                $sekarang->copy()->startOfDay(),
                $sekarang->copy()->endOfDay(),
                $sekarang->copy()->subDay()->startOfDay(),
                $sekarang->copy()->subDay()->endOfDay(),
                'hari ini vs kemarin',
            ],
            'minggu' => [
                $sekarang->copy()->startOfWeek(),
                $sekarang->copy()->endOfWeek(),
                $sekarang->copy()->subWeek()->startOfWeek(),
                $sekarang->copy()->subWeek()->endOfWeek(),
                'minggu ini vs minggu lalu',
            ],
            'tahun' => [
                $sekarang->copy()->startOfYear(),
                $sekarang->copy()->endOfYear(),
                $sekarang->copy()->subYear()->startOfYear(),
                $sekarang->copy()->subYear()->endOfYear(),
                'tahun ini vs tahun lalu',
            ],
            default => [
                $sekarang->copy()->startOfMonth(),
                $sekarang->copy()->endOfMonth(),
                $sekarang->copy()->subMonth()->startOfMonth(),
                $sekarang->copy()->subMonth()->endOfMonth(),
                'dari bulan lalu',
            ],
        };

        // 1. KPI Omzet Penjualan
        $omzetIni = (float) Penjualan::where('status', 'selesai')
            ->whereBetween('tanggal', [$mulaiIni, $selesaiIni])
            ->sum('total');

        $omzetLalu = (float) Penjualan::where('status', 'selesai')
            ->whereBetween('tanggal', [$mulaiLalu, $selesaiLalu])
            ->sum('total');

        $persenOmzet = $omzetLalu > 0
            ? round((($omzetIni - $omzetLalu) / $omzetLalu) * 100, 1)
            : ($omzetIni > 0 ? 100.0 : 0.0);

        // 2. KPI Jumlah Transaksi
        $trxIni = Penjualan::where('status', 'selesai')
            ->whereBetween('tanggal', [$mulaiIni, $selesaiIni])
            ->count();

        $trxLalu = Penjualan::where('status', 'selesai')
            ->whereBetween('tanggal', [$mulaiLalu, $selesaiLalu])
            ->count();

        $persenTrx = $trxLalu > 0
            ? round((($trxIni - $trxLalu) / $trxLalu) * 100, 1)
            : ($trxIni > 0 ? 100.0 : 0.0);

        // 3. KPI Produk Aktif & Stok Menipis
        $totalProdukAktif = Produk::aktif()->count();
        $jumlahMenipis = Produk::dibawahMinimum()->count();
        $persenStokAman = $totalProdukAktif > 0
            ? (int) round((($totalProdukAktif - $jumlahMenipis) / $totalProdukAktif) * 100)
            : 100;

        // 4. KPI Piutang Kasbon Aktif & Nilai Pembelian PO
        $totalPiutang = Schema::hasTable('piutang') ? (float) DB::table('piutang')->whereIn('status', ['belum_lunas', 'sebagian'])->sum('sisa') : 0.0;
        $totalBelanjaPo = (float) Pembelian::whereIn('status', ['disetujui', 'dikirim', 'diterima_sebagian', 'selesai'])
            ->whereBetween('tanggal', [$mulaiIni->toDateString(), $selesaiIni->toDateString()])
            ->sum('total');

        // 5. Grafik Batang 6 Bulan Terakhir (Jan - Jun atau 6 bulan terakhir berjalan)
        $grafikBulanan = [];
        $nilaiMaks = 0;
        for ($i = 5; $i >= 0; $i--) {
            $bulanTarget = $sekarang->copy()->subMonths($i);
            $nilai = (float) Penjualan::where('status', 'selesai')
                ->whereYear('tanggal', $bulanTarget->year)
                ->whereMonth('tanggal', $bulanTarget->month)
                ->sum('total');

            if ($nilai > $nilaiMaks) {
                $nilaiMaks = $nilai;
            }

            $grafikBulanan[] = [
                'label' => $bulanTarget->translatedFormat('M'),
                'bulan_penuh' => $bulanTarget->translatedFormat('F Y'),
                'nilai' => $nilai,
                'aktif' => $i === 0,
            ];
        }

        // Hitung persentase tinggi bar (minimal 14% agar tetap terlihat estetis saat 0)
        $pembagiGrafik = $nilaiMaks > 0 ? $nilaiMaks : 1;
        foreach ($grafikBulanan as &$bar) {
            $bar['persen_tinggi'] = $nilaiMaks > 0
                ? max(14, (int) round(($bar['nilai'] / $pembagiGrafik) * 100))
                : 22;
        }
        unset($bar);

        // 6. Strip Kalender 5 Hari (H-2 s.d. H+2)
        $stripKalender = [];
        for ($d = -2; $d <= 2; $d++) {
            $tgl = $sekarang->copy()->addDays($d);
            $stripKalender[] = [
                'hari' => $tgl->translatedFormat('D'),
                'tanggal' => $tgl->format('d'),
                'hari_ini' => $d === 0,
            ];
        }

        // 7. Aktivitas Terbaru (Gabungan Penjualan & Purchase Order Terbaru)
        $penjualanTerbaru = Penjualan::with(['kasir.karyawan', 'detail'])
            ->orderByDesc('tanggal')
            ->limit(5)
            ->get();

        $poTerbaru = Pembelian::with('supplier')
            ->orderByDesc('tanggal')
            ->limit(3)
            ->get();

        return view('dasbor', compact(
            'periode',
            'labelPeriode',
            'mulaiIni',
            'selesaiIni',
            'omzetIni',
            'persenOmzet',
            'trxIni',
            'persenTrx',
            'totalProdukAktif',
            'jumlahMenipis',
            'persenStokAman',
            'totalPiutang',
            'totalBelanjaPo',
            'grafikBulanan',
            'nilaiMaks',
            'stripKalender',
            'penjualanTerbaru',
            'poTerbaru'
        ));
    }
}
