<?php

namespace App\Services;

use App\Models\DetailPenjualan;
use App\Models\DetailRetur;
use App\Models\Pembayaran;
use App\Models\Penjualan;
use App\Models\Piutang;
use App\Models\ReturPenjualan;
use App\Support\NomorDokumen;
use DomainException;
use Illuminate\Support\Facades\DB;

class ReturService
{
    public const BATAS_HARI_RETUR = 10;

    public function __construct(
        private readonly InventoriService $inventoriService,
        private readonly LogAktivitasService $logService
    ) {}

    public function prosesRetur(int $penjualanId, array $itemsRetur, string $alasan, int $userId): ReturPenjualan
    {
        return DB::transaction(function () use ($penjualanId, $itemsRetur, $alasan, $userId) {
            $penjualan = Penjualan::with('detail')->whereKey($penjualanId)->lockForUpdate()->firstOrFail();

            if ($penjualan->status !== 'selesai') {
                throw new DomainException('Hanya transaksi berstatus selesai yang dapat diretur.');
            }

            // Aturan Bisnis D-10: Maksimal 10 hari sejak tanggal transaksi
            if ($penjualan->tanggal->diffInDays(now()) > self::BATAS_HARI_RETUR) {
                throw new DomainException('Batas waktu retur (maksimal 10 hari sejak transaksi) telah terlampaui.');
            }

            $daftarDetail = $penjualan->detail->keyBy('id');
            $sudahDireturMap = DetailRetur::whereIn('detail_penjualan_id', $daftarDetail->keys())
                ->selectRaw('detail_penjualan_id, SUM(qty_retur) as total_retur')
                ->groupBy('detail_penjualan_id')
                ->pluck('total_retur', 'detail_penjualan_id');

            $barisValid = [];
            $totalNilaiRetur = 0;

            foreach ($itemsRetur as $detailId => $baris) {
                $qtyRetur = (int) ($baris['qty'] ?? 0);
                if ($qtyRetur <= 0) {
                    continue;
                }

                /** @var DetailPenjualan|null $detailJual */
                $detailJual = $daftarDetail->get((int) $detailId);
                if (! $detailJual) {
                    throw new DomainException('Item transaksi tidak ditemukan.');
                }

                $sudahDiretur = (int) ($sudahDireturMap[$detailJual->id] ?? 0);
                $sisaBisaRetur = $detailJual->qty - $sudahDiretur;

                if ($qtyRetur > $sisaBisaRetur) {
                    throw new DomainException("Qty retur melebihi sisa item yang dapat diretur (maksimal {$sisaBisaRetur}).");
                }

                $kondisi = ($baris['kondisi'] ?? 'layak_jual') === 'rusak' ? 'rusak' : 'layak_jual';
                $subtotalRetur = (float) $detailJual->harga_jual * $qtyRetur;
                $totalNilaiRetur += $subtotalRetur;

                $barisValid[] = [
                    'detail_jual' => $detailJual,
                    'qty_retur' => $qtyRetur,
                    'kondisi' => $kondisi,
                    'subtotal' => $subtotalRetur,
                ];
            }

            if (empty($barisValid)) {
                throw new DomainException('Isi minimal 1 barang yang akan diretur.');
            }

            // Tentukan penyelesaian: potong piutang jika masih ada sisa kasbon, atau kembalikan tunai
            $piutang = $penjualan->metode_bayar === 'kasbon'
                ? Piutang::where('penjualan_id', $penjualan->id)->lockForUpdate()->first()
                : null;

            $penyelesaian = ($piutang && (float) $piutang->sisa >= $totalNilaiRetur)
                ? 'potong_piutang'
                : 'tunai_kembali';

            $retur = ReturPenjualan::create([
                'penjualan_id' => $penjualan->id,
                'diproses_oleh' => $userId,
                'nomor' => NomorDokumen::buat('RTR', 'retur_penjualan', 'nomor'),
                'tanggal' => now(),
                'penyelesaian' => $penyelesaian,
                'total_retur' => $totalNilaiRetur,
                'alasan' => $alasan,
            ]);

            usort($barisValid, fn ($a, $b) => $a['detail_jual']->produk_id <=> $b['detail_jual']->produk_id);

            foreach ($barisValid as $b) {
                /** @var DetailPenjualan $dj */
                $dj = $b['detail_jual'];

                DetailRetur::create([
                    'retur_id' => $retur->id,
                    'detail_penjualan_id' => $dj->id,
                    'qty_retur' => $b['qty_retur'],
                    'kondisi' => $b['kondisi'],
                    'subtotal' => $b['subtotal'],
                ]);

                // Kembalikan stok hanya jika kondisi barang layak_jual
                if ($b['kondisi'] === 'layak_jual') {
                    $this->inventoriService->ubahStok(
                        produkId: $dj->produk_id,
                        qtyBertanda: $b['qty_retur'],
                        jenis: 'retur',
                        userId: $userId,
                        sumberTipe: 'retur_penjualan',
                        sumberId: $retur->id
                    );
                }
            }

            if ($penyelesaian === 'potong_piutang' && $piutang) {
                $piutang->sisa = max(0, (float) $piutang->sisa - $totalNilaiRetur);
                $piutang->status = $piutang->sisa <= 0 ? 'lunas' : 'sebagian';
                $piutang->save();
            } else {
                Pembayaran::create([
                    'penjualan_id' => $penjualan->id,
                    'user_id' => $userId,
                    'arah' => 'keluar',
                    'jumlah' => $totalNilaiRetur,
                    'tanggal' => now(),
                    'metode' => 'tunai',
                ]);
            }

            $this->logService->catat('retur_penjualan', 'retur_penjualan', $retur->id, [
                'nomor_retur' => $retur->nomor,
                'nomor_struk' => $penjualan->nomor_struk,
                'total_retur' => $totalNilaiRetur,
                'penyelesaian' => $penyelesaian,
            ], $userId);

            return $retur;
        });
    }
}
