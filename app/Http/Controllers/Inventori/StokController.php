<?php

namespace App\Http\Controllers\Inventori;

use App\Http\Controllers\Controller;
use App\Models\PenyesuaianStok;
use App\Models\PergerakanStok;
use App\Models\Produk;
use App\Services\InventoriService;
use App\Services\LogAktivitasService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StokController extends Controller
{
    public function __construct(
        private readonly InventoriService $inventoriService,
        private readonly LogAktivitasService $logService
    ) {}

    public function index(Request $request): View
    {
        $query = PergerakanStok::with(['produk', 'user.karyawan'])->orderByDesc('created_at')->orderByDesc('id');

        if ($request->filled('produk_id')) {
            $query->where('produk_id', $request->integer('produk_id'));
        }

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->string('jenis'));
        }

        $kartuStok = $query->paginate(25)->withQueryString();
        $daftarProduk = Produk::aktif()->orderBy('nama')->get(['id', 'kode', 'nama', 'satuan', 'stok']);

        $daftarPenyesuaian = PenyesuaianStok::with(['produk', 'pengaju.karyawan', 'penyetuju.karyawan'])
            ->orderByRaw("CASE WHEN status = 'menunggu' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('inventori.stok.index', compact('kartuStok', 'daftarProduk', 'daftarPenyesuaian'));
    }

    public function ajukanPenyesuaian(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'produk_id' => ['required', 'integer', Rule::exists('produk', 'id')],
            'qty_selisih' => ['required', 'integer', 'not_in:0'],
            'alasan' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'qty_selisih.not_in' => 'Selisih kuantitas tidak boleh 0.',
        ]);

        $penyesuaian = PenyesuaianStok::create([
            'produk_id' => $data['produk_id'],
            'diajukan_oleh' => Auth::id(),
            'qty_selisih' => $data['qty_selisih'],
            'alasan' => $data['alasan'],
            'status' => 'menunggu',
        ]);

        $this->logService->catat('ajukan_penyesuaian_stok', 'penyesuaian_stok', $penyesuaian->id, [
            'produk_id' => $penyesuaian->produk_id,
            'qty_selisih' => $penyesuaian->qty_selisih,
        ]);

        return redirect()->route('stok.index')->with('sukses', 'Pengajuan penyesuaian stok dicatat dan menunggu persetujuan Admin.');
    }

    public function prosesPenyesuaian(Request $request, PenyesuaianStok $penyesuaian): RedirectResponse
    {
        if (! Auth::user()->hasRole('admin')) {
            abort(403, 'Hanya Admin yang dapat menyetujui atau menolak penyesuaian stok.');
        }

        $data = $request->validate([
            'keputusan' => ['required', Rule::in(['disetujui', 'ditolak'])],
        ]);

        try {
            DB::transaction(function () use ($penyesuaian, $data) {
                $terkunci = PenyesuaianStok::whereKey($penyesuaian->id)->lockForUpdate()->firstOrFail();

                if ($terkunci->status !== 'menunggu') {
                    throw new DomainException('Pengajuan penyesuaian ini sudah diproses sebelumnya.');
                }

                $terkunci->status = $data['keputusan'];
                $terkunci->disetujui_oleh = Auth::id();
                $terkunci->save();

                if ($data['keputusan'] === 'disetujui') {
                    $this->inventoriService->ubahStok(
                        produkId: $terkunci->produk_id,
                        qtyBertanda: $terkunci->qty_selisih,
                        jenis: 'penyesuaian',
                        userId: Auth::id(),
                        sumberTipe: 'penyesuaian_stok',
                        sumberId: $terkunci->id
                    );
                }

                $this->logService->catat('proses_penyesuaian_stok', 'penyesuaian_stok', $terkunci->id, [
                    'keputusan' => $data['keputusan'],
                    'qty_selisih' => $terkunci->qty_selisih,
                ]);
            });
        } catch (DomainException $e) {
            return back()->with('galat', $e->getMessage());
        }

        $pesan = $data['keputusan'] === 'disetujui'
            ? 'Penyesuaian stok disetujui dan saldo stok telah diperbarui.'
            : 'Pengajuan penyesuaian stok ditolak.';

        return redirect()->route('stok.index')->with('sukses', $pesan);
    }
}
