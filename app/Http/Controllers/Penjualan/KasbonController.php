<?php

namespace App\Http\Controllers\Penjualan;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Piutang;
use App\Services\LogAktivitasService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KasbonController extends Controller
{
    public function __construct(
        private readonly LogAktivitasService $logService
    ) {}

    public function index(): View
    {
        $piutangAktif = Piutang::with(['pelanggan', 'penjualan'])
            ->whereIn('status', ['belum_lunas', 'sebagian'])
            ->orderBy('jatuh_tempo')
            ->orderByDesc('id')
            ->get();

        $sisaPerPelanggan = $piutangAktif->groupBy('pelanggan_id')->map->sum('sisa');

        $daftarPelanggan = Pelanggan::orderBy('nama')->get()->map(function (Pelanggan $p) use ($sisaPerPelanggan) {
            $p->total_kasbon_aktif = (float) ($sisaPerPelanggan[$p->id] ?? 0);

            return $p;
        });

        return view('penjualan.kasbon.index', compact('piutangAktif', 'daftarPelanggan'));
    }

    public function simpanPelanggan(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:120'],
            'no_hp' => ['nullable', 'string', 'max:30'],
            'batas_kasbon' => ['required', 'numeric', 'min:0'],
        ]);

        $pelanggan = Pelanggan::create($data);
        $this->logService->catat('buat_pelanggan', 'pelanggan', $pelanggan->id, ['nama' => $pelanggan->nama]);

        return redirect()->route('kasbon.index')->with('sukses', 'Pelanggan kasbon baru berhasil ditambahkan.');
    }

    public function bayarCicilan(Request $request, Piutang $piutang): RedirectResponse
    {
        $data = $request->validate([
            'jumlah' => ['required', 'numeric', 'min:100'],
            'metode' => ['required', Rule::in(['tunai', 'transfer'])],
        ]);

        try {
            DB::transaction(function () use ($piutang, $data) {
                $terkunci = Piutang::whereKey($piutang->id)->lockForUpdate()->firstOrFail();

                if (! in_array($terkunci->status, ['belum_lunas', 'sebagian'], true)) {
                    throw new DomainException('Piutang ini sudah lunas atau dibatalkan.');
                }

                $bayar = (float) $data['jumlah'];
                if ($bayar > (float) $terkunci->sisa) {
                    throw new DomainException('Nominal pembayaran melebihi sisa tagihan kasbon.');
                }

                $terkunci->sisa = round((float) $terkunci->sisa - $bayar, 2);
                $terkunci->status = $terkunci->sisa <= 0 ? 'lunas' : 'sebagian';
                $terkunci->save();

                Pembayaran::create([
                    'penjualan_id' => $terkunci->penjualan_id,
                    'piutang_id' => $terkunci->id,
                    'user_id' => Auth::id(),
                    'arah' => 'masuk',
                    'jumlah' => $bayar,
                    'tanggal' => now(),
                    'metode' => $data['metode'],
                ]);

                $this->logService->catat('bayar_kasbon', 'piutang', $terkunci->id, [
                    'pelanggan_id' => $terkunci->pelanggan_id,
                    'jumlah_bayar' => $bayar,
                    'sisa_baru' => $terkunci->sisa,
                ]);
            });
        } catch (DomainException $e) {
            return back()->with('galat', $e->getMessage());
        }

        return redirect()->route('kasbon.index')->with('sukses', 'Pembayaran kasbon berhasil dicatat ke kas.');
    }
}
