<?php

namespace App\Services;

use App\Models\DetailPenjualan;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Penjualan;
use App\Models\Piutang;
use App\Models\Produk;
use App\Support\NomorDokumen;
use DomainException;
use Illuminate\Support\Facades\DB;

class PenjualanService
{
    public function __construct(
        private readonly InventoriService $inventoriService,
        private readonly LogAktivitasService $logService
    ) {}

    public function prosesTransaksi(array $payload, int $userId): Penjualan
    {
        return DB::transaction(function () use ($payload, $userId) {
            $items = collect($payload['items'] ?? [])
                ->filter(fn ($i) => (int) ($i['qty'] ?? 0) > 0)
                ->groupBy(fn ($i) => (int) $i['produk_id'])
                ->map(fn ($group, $produkId) => [
                    'produk_id' => (int) $produkId,
                    'qty' => (int) $group->sum('qty'),
                ])
                ->sortBy('produk_id')
                ->values();

            if ($items->isEmpty()) {
                throw new DomainException('Keranjang belanja masih kosong.');
            }

            $metode = $payload['metode_bayar']; // tunai | transfer | kasbon
            $pelangganId = ! empty($payload['pelanggan_id']) ? (int) $payload['pelanggan_id'] : null;

            if ($metode === 'kasbon' && ! $pelangganId) {
                throw new DomainException('Transaksi kasbon wajib memilih pelanggan terdaftar.');
            }

            // Kunci & validasi stok setiap produk secara berurutan (anti-deadlock)
            $barisSiap = [];
            $subtotal = 0;

            foreach ($items as $item) {
                $produk = Produk::whereKey($item['produk_id'])->lockForUpdate()->firstOrFail();

                if ($produk->status !== 'aktif') {
                    throw new DomainException("Produk '{$produk->nama}' sedang nonaktif.");
                }

                if ($produk->stok < $item['qty']) {
                    throw new DomainException("Stok '{$produk->nama}' tidak mencukupi (tersedia: {$produk->stok} {$produk->satuan}).");
                }

                $hargaJual = (float) $produk->harga_jual;
                $barisSubtotal = $hargaJual * $item['qty'];
                $subtotal += $barisSubtotal;

                $barisSiap[] = [
                    'produk' => $produk,
                    'qty' => $item['qty'],
                    'harga_jual' => $hargaJual,
                    'hpp_saat_itu' => (float) $produk->hpp_rata_rata,
                    'subtotal' => $barisSubtotal,
                ];
            }

            $diskon = max(0, (float) ($payload['diskon'] ?? 0));
            if ($diskon > $subtotal) {
                throw new DomainException('Diskon tidak boleh melebihi subtotal belanja.');
            }

            $total = $subtotal - $diskon;
            $bayar = $metode === 'kasbon' ? 0.0 : (float) ($payload['jumlah_bayar'] ?? $total);

            if ($metode !== 'kasbon' && $bayar < $total) {
                throw new DomainException('Jumlah pembayaran kurang dari total tagihan.');
            }

            $kembalian = $metode === 'kasbon' ? 0.0 : ($bayar - $total);

            // Validasi limit kasbon pelanggan jika metode = kasbon
            $pelanggan = null;
            if ($metode === 'kasbon') {
                $pelanggan = Pelanggan::whereKey($pelangganId)->lockForUpdate()->firstOrFail();
                $piutangAktif = (float) Piutang::where('pelanggan_id', $pelanggan->id)
                    ->whereIn('status', ['belum_lunas', 'sebagian'])
                    ->sum('sisa');

                if (($piutangAktif + $total) > (float) $pelanggan->batas_kasbon) {
                    $sisaLimit = max(0, (float) $pelanggan->batas_kasbon - $piutangAktif);
                    throw new DomainException('Total kasbon melebihi batas limit pelanggan (sisa limit: Rp' . number_format($sisaLimit, 0, ',', '.') . ').');
                }
            }

            $penjualan = Penjualan::create([
                'kasir_id' => $userId,
                'pelanggan_id' => $pelangganId,
                'nomor_struk' => NomorDokumen::buat('TRX', 'penjualan', 'nomor_struk'),
                'tanggal' => now(),
                'metode_bayar' => $metode,
                'subtotal' => $subtotal,
                'diskon' => $diskon,
                'total' => $total,
                'bayar' => $bayar,
                'kembalian' => $kembalian,
                'status' => 'selesai',
            ]);

            foreach ($barisSiap as $baris) {
                /** @var Produk $produk */
                $produk = $baris['produk'];

                DetailPenjualan::create([
                    'penjualan_id' => $penjualan->id,
                    'produk_id' => $produk->id,
                    'qty' => $baris['qty'],
                    'harga_jual' => $baris['harga_jual'],
                    'hpp_saat_itu' => $baris['hpp_saat_itu'],
                    'subtotal' => $baris['subtotal'],
                ]);

                $this->inventoriService->ubahStok(
                    produkId: $produk->id,
                    qtyBertanda: -$baris['qty'],
                    jenis: 'penjualan',
                    userId: $userId,
                    sumberTipe: 'penjualan',
                    sumberId: $penjualan->id
                );
            }

            if ($metode === 'kasbon') {
                Piutang::create([
                    'pelanggan_id' => $pelanggan->id,
                    'penjualan_id' => $penjualan->id,
                    'jumlah' => $total,
                    'sisa' => $total,
                    'jatuh_tempo' => now()->addDays(14)->toDateString(),
                    'status' => 'belum_lunas',
                ]);
            } elseif ($total > 0) {
                Pembayaran::create([
                    'penjualan_id' => $penjualan->id,
                    'user_id' => $userId,
                    'arah' => 'masuk',
                    'jumlah' => $total,
                    'tanggal' => now(),
                    'metode' => $metode,
                ]);
            }

            $this->logService->catat('transaksi_penjualan', 'penjualan', $penjualan->id, [
                'nomor_struk' => $penjualan->nomor_struk,
                'metode' => $metode,
                'total' => $total,
            ], $userId);

            return $penjualan;
        });
    }

    public function batalkanTransaksi(int $penjualanId, string $alasan, int $userId): Penjualan
    {
        return DB::transaction(function () use ($penjualanId, $alasan, $userId) {
            $penjualan = Penjualan::with('detail')->whereKey($penjualanId)->lockForUpdate()->firstOrFail();

            if ($penjualan->status !== 'selesai') {
                throw new DomainException('Transaksi ini sudah dibatalkan sebelumnya.');
            }

            if (ReturPenjualan::where('penjualan_id', $penjualan->id)->exists()) {
                throw new DomainException('Transaksi yang sudah memiliki retur tidak dapat dibatalkan.');
            }

            if ($penjualan->metode_bayar === 'kasbon') {
                $piutang = Piutang::where('penjualan_id', $penjualan->id)->lockForUpdate()->first();
                if ($piutang) {
                    if ((float) $piutang->sisa < (float) $piutang->jumlah) {
                        throw new DomainException('Piutang kasbon transaksi ini sudah dicicil sebagian dan tidak dapat dibatalkan.');
                    }
                    $piutang->sisa = 0;
                    $piutang->status = 'batal';
                    $piutang->save();
                }
            } elseif ((float) $penjualan->total > 0) {
                Pembayaran::create([
                    'penjualan_id' => $penjualan->id,
                    'user_id' => $userId,
                    'arah' => 'keluar',
                    'jumlah' => $penjualan->total,
                    'tanggal' => now(),
                    'metode' => 'tunai',
                ]);
            }

            $detailUrut = $penjualan->detail->sortBy('produk_id');
            foreach ($detailUrut as $d) {
                $this->inventoriService->ubahStok(
                    produkId: $d->produk_id,
                    qtyBertanda: $d->qty,
                    jenis: 'pembatalan',
                    userId: $userId,
                    sumberTipe: 'penjualan',
                    sumberId: $penjualan->id
                );
            }

            $penjualan->status = 'batal';
            $penjualan->save();

            $this->logService->catat('batalkan_penjualan', 'penjualan', $penjualan->id, [
                'nomor_struk' => $penjualan->nomor_struk,
                'alasan' => $alasan,
            ], $userId);

            return $penjualan;
        });
    }
}
