<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Utang;
use App\Services\LogAktivitasService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UtangController extends Controller
{
    public function __construct(
        private readonly LogAktivitasService $logService
    ) {}

    public function index(): View
    {
        $daftarUtang = Utang::with(['supplier', 'pembelian'])
            ->orderByRaw("CASE WHEN status IN ('belum_lunas', 'sebagian') THEN 0 ELSE 1 END")
            ->orderBy('jatuh_tempo')
            ->paginate(25);

        $totalSisaUtang = (float) Utang::whereIn('status', ['belum_lunas', 'sebagian'])->sum('sisa');

        return view('keuangan.utang.index', compact('daftarUtang', 'totalSisaUtang'));
    }

    public function bayar(Request $request, Utang $utang): RedirectResponse
    {
        $data = $request->validate([
            'jumlah' => ['required', 'numeric', 'min:100'],
            'metode' => ['required', Rule::in(['tunai', 'transfer'])],
        ]);

        try {
            DB::transaction(function () use ($utang, $data) {
                $terkunci = Utang::whereKey($utang->id)->lockForUpdate()->firstOrFail();

                if (! in_array($terkunci->status, ['belum_lunas', 'sebagian'], true)) {
                    throw new DomainException('Utang supplier ini sudah lunas.');
                }

                $bayar = (float) $data['jumlah'];
                if ($bayar > (float) $terkunci->sisa) {
                    throw new DomainException('Nominal pembayaran melebihi sisa utang.');
                }

                $terkunci->sisa = round((float) $terkunci->sisa - $bayar, 2);
                $terkunci->status = $terkunci->sisa <= 0 ? 'lunas' : 'sebagian';
                $terkunci->save();

                Pembayaran::create([
                    'pembelian_id' => $terkunci->pembelian_id,
                    'utang_id' => $terkunci->id,
                    'user_id' => Auth::id(),
                    'arah' => 'keluar',
                    'jumlah' => $bayar,
                    'tanggal' => now(),
                    'metode' => $data['metode'],
                ]);

                $this->logService->catat('bayar_utang_supplier', 'utang', $terkunci->id, [
                    'supplier_id' => $terkunci->supplier_id,
                    'jumlah_bayar' => $bayar,
                    'sisa_baru' => $terkunci->sisa,
                ]);
            });
        } catch (DomainException $e) {
            return back()->with('galat', $e->getMessage());
        }

        return redirect()->route('utang.index')->with('sukses', 'Pembayaran utang supplier berhasil dicatat.');
    }
}
