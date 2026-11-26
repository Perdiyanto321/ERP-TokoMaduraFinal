<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Pengeluaran;
use App\Services\LogAktivitasService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BiayaOperasionalController extends Controller
{
    public function __construct(
        private readonly LogAktivitasService $logService
    ) {}

    public function index(Request $request): View
    {
        $query = Pengeluaran::with('user.karyawan')->orderByDesc('tanggal')->orderByDesc('id');

        if ($request->filled('kategori_biaya')) {
            $query->where('kategori_biaya', $request->string('kategori_biaya'));
        }

        $daftarBiaya = $query->paginate(25)->withQueryString();
        $totalBulanIni = (float) Pengeluaran::whereYear('tanggal', now()->year)
            ->whereMonth('tanggal', now()->month)
            ->sum('nominal');

        return view('keuangan.biaya.index', compact('daftarBiaya', 'totalBulanIni'));
    }

    public function simpan(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'kategori_biaya' => ['required', Rule::in(['listrik', 'gaji', 'sewa', 'kemasan', 'lainnya'])],
            'nominal' => ['required', 'numeric', 'min:100'],
            'tanggal' => ['required', 'date'],
            'keterangan' => ['required', 'string', 'min:3', 'max:300'],
        ]);

        DB::transaction(function () use ($data) {
            $biaya = Pengeluaran::create([
                'user_id' => Auth::id(),
                'kategori_biaya' => $data['kategori_biaya'],
                'nominal' => $data['nominal'],
                'tanggal' => $data['tanggal'],
                'keterangan' => $data['keterangan'],
            ]);

            Pembayaran::create([
                'user_id' => Auth::id(),
                'arah' => 'keluar',
                'jumlah' => $data['nominal'],
                'tanggal' => now(),
                'metode' => 'tunai',
            ]);

            $this->logService->catat('catat_biaya_operasional', 'pengeluaran', $biaya->id, [
                'kategori' => $biaya->kategori_biaya,
                'nominal' => $biaya->nominal,
            ]);
        });

        return redirect()->route('biaya.index')->with('sukses', 'Biaya operasional berhasil dicatat.');
    }
}
