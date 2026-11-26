<?php

namespace App\Services;

use App\Models\DetailPembelian;
use App\Models\DetailPenerimaan;
use App\Models\Pembayaran;
use App\Models\Pembelian;
use App\Models\PenerimaanBarang;
use App\Models\Produk;
use App\Models\Utang;
use App\Support\NomorDokumen;
use DomainException;
use Illuminate\Support\Facades\DB;

class PembelianService
{
    public function __construct(
        private readonly InventoriService $inventoriService,
        private readonly HppCalculator $hppCalculator,
        private readonly LogAktivitasService $logService
    ) {}

    public function buatPo(array $header, array $items, int $userId): Pembelian
    {
        return DB::transaction(function () use ($header, $items, $userId) {
            $total = 0;
            $barisDetail = [];

            foreach ($items as $item) {
                $qty = (int) $item['qty_pesan'];
                $harga = (float) $item['harga_beli'];
                if ($qty <= 0) {
                    continue;
                }
                $total += ($qty * $harga);
                $barisDetail[] = [
                    'produk_id' => (int) $item['produk_id'],
                    'qty_pesan' => $qty,
                    'harga_beli' => $harga,
                ];
            }

            if (empty($barisDetail)) {
                throw new DomainException('Minimal harus ada 1 produk dengan kuantitas pesan lebih dari 0.');
            }

            $po = Pembelian::create([
                'supplier_id' => $header['supplier_id'],
                'dibuat_oleh' => $userId,
                'nomor_po' => NomorDokumen::buat('PO', 'pembelian', 'nomor_po'),
                'tanggal' => $header['tanggal'],
                'status' => 'draf',
                'jenis_bayar' => $header['jenis_bayar'],
                'jatuh_tempo' => $header['jenis_bayar'] === 'kredit' ? $header['jatuh_tempo'] : null,
                'total' => $total,
            ]);

            foreach ($barisDetail as $baris) {
                $po->detail()->create($baris);
            }

            $this->logService->catat('buat_po', 'pembelian', $po->id, [
                'nomor_po' => $po->nomor_po,
                'total' => $total,
            ], $userId);

            return $po;
        });
    }

    public function ubahStatus(int $pembelianId, string $statusBaru, int $userId): Pembelian
    {
        return DB::transaction(function () use ($pembelianId, $statusBaru, $userId) {
            $po = Pembelian::whereKey($pembelianId)->lockForUpdate()->firstOrFail();

            $transisiSah = match ($po->status) {
                'draf' => ['disetujui', 'dibatalkan'],
                'disetujui' => ['dikirim', 'dibatalkan'],
                default => [],
            };

            if (! in_array($statusBaru, $transisiSah, true)) {
                throw new DomainException("Status PO '{$po->status}' tidak dapat diubah menjadi '{$statusBaru}'.");
            }

            $po->status = $statusBaru;
            if ($statusBaru === 'disetujui') {
                $po->disetujui_oleh = $userId;
            }
            $po->save();

            $this->logService->catat('ubah_status_po', 'pembelian', $po->id, [
                'nomor_po' => $po->nomor_po,
                'status_baru' => $statusBaru,
            ], $userId);

            return $po;
        });
    }

    public function terimaBarang(int $pembelianId, array $inputTerima, int $userId): PenerimaanBarang
    {
        return DB::transaction(function () use ($pembelianId, $inputTerima, $userId) {
            $po = Pembelian::with('detail.detailPenerimaan')
                ->whereKey($pembelianId)
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($po->status, ['disetujui', 'dikirim', 'diterima_sebagian'], true)) {
                throw new DomainException('PO ini belum disetujui atau sudah selesai/dibatalkan.');
            }

            $daftarDetailPo = $po->detail->keyBy('id');
            $itemDiterimaValid = [];
            $nilaiPenerimaanIni = 0;

            foreach ($inputTerima as $detailPoId => $qtyMasuk) {
                $qtyMasuk = (int) $qtyMasuk;
                if ($qtyMasuk <= 0) {
                    continue;
                }

                /** @var DetailPembelian|null $detailPo */
                $detailPo = $daftarDetailPo->get((int) $detailPoId);
                if (! $detailPo) {
                    throw new DomainException('Item detail PO tidak valid.');
                }

                $sudahDiterima = (int) $detailPo->detailPenerimaan->sum('qty_diterima');
                $sisa = $detailPo->qty_pesan - $sudahDiterima;

                if ($qtyMasuk > $sisa) {
                    throw new DomainException("Kuantitas diterima melebihi sisa pesanan (sisa: {$sisa}).");
                }

                $itemDiterimaValid[] = [
                    'detail_po' => $detailPo,
                    'qty_diterima' => $qtyMasuk,
                ];
                $nilaiPenerimaanIni += ($qtyMasuk * (float) $detailPo->harga_beli);
            }

            if (empty($itemDiterimaValid)) {
                throw new DomainException('Isi minimal satu kuantitas barang yang diterima.');
            }

            // Urutkan berdasarkan produk_id untuk menghindari deadlock
            usort($itemDiterimaValid, fn ($a, $b) => $a['detail_po']->produk_id <=> $b['detail_po']->produk_id);

            $penerimaan = PenerimaanBarang::create([
                'pembelian_id' => $po->id,
                'diterima_oleh' => $userId,
                'nomor' => NomorDokumen::buat('GRN', 'penerimaan_barang', 'nomor'),
                'tanggal' => now(),
            ]);

            foreach ($itemDiterimaValid as $baris) {
                /** @var DetailPembelian $detailPo */
                $detailPo = $baris['detail_po'];
                $qtyBeli = $baris['qty_diterima'];

                DetailPenerimaan::create([
                    'penerimaan_id' => $penerimaan->id,
                    'detail_pembelian_id' => $detailPo->id,
                    'qty_diterima' => $qtyBeli,
                ]);

                $produk = Produk::whereKey($detailPo->produk_id)->lockForUpdate()->firstOrFail();
                $isi = max(1, $produk->isi_per_satuan_beli);
                $qtyEceranMasuk = $qtyBeli * $isi;

                $hppBaru = $this->hppCalculator->hitung(
                    stokLama: $produk->stok,
                    hppLama: (float) $produk->hpp_rata_rata,
                    qtyMasukEceran: $qtyEceranMasuk,
                    hargaBeliPerSatuanBeli: (float) $detailPo->harga_beli,
                    isiPerSatuanBeli: $isi
                );

                $produk->hpp_rata_rata = $hppBaru;
                $produk->harga_beli = $detailPo->harga_beli;
                $produk->save();

                $this->inventoriService->ubahStok(
                    produkId: $produk->id,
                    qtyBertanda: $qtyEceranMasuk,
                    jenis: 'penerimaan',
                    userId: $userId,
                    sumberTipe: 'penerimaan_barang',
                    sumberId: $penerimaan->id
                );
            }

            // Cek apakah seluruh item pada PO sudah diterima penuh
            $po->load('detail.detailPenerimaan');
            $semuaLengkap = $po->detail->every(function (DetailPembelian $dp) {
                return $dp->detailPenerimaan->sum('qty_diterima') >= $dp->qty_pesan;
            });

            $po->status = $semuaLengkap ? 'selesai' : 'diterima_sebagian';
            $po->save();

            // Jika jenis bayar tunai, langsung catat arus kas keluar
            if ($po->jenis_bayar === 'tunai' && $nilaiPenerimaanIni > 0) {
                Pembayaran::create([
                    'pembelian_id' => $po->id,
                    'user_id' => $userId,
                    'arah' => 'keluar',
                    'jumlah' => $nilaiPenerimaanIni,
                    'tanggal' => now(),
                    'metode' => 'tunai',
                ]);
            } elseif ($po->jenis_bayar === 'kredit' && $nilaiPenerimaanIni > 0) {
                $utang = Utang::where('pembelian_id', $po->id)->lockForUpdate()->first();
                if ($utang) {
                    $utang->jumlah = round((float) $utang->jumlah + $nilaiPenerimaanIni, 2);
                    $utang->sisa = round((float) $utang->sisa + $nilaiPenerimaanIni, 2);
                    if ($utang->status === 'lunas') {
                        $utang->status = 'sebagian';
                    }
                    $utang->save();
                } else {
                    Utang::create([
                        'supplier_id' => $po->supplier_id,
                        'pembelian_id' => $po->id,
                        'jumlah' => $nilaiPenerimaanIni,
                        'sisa' => $nilaiPenerimaanIni,
                        'jatuh_tempo' => $po->jatuh_tempo,
                        'status' => 'belum_lunas',
                    ]);
                }
            }

            $this->logService->catat('terima_barang_po', 'penerimaan_barang', $penerimaan->id, [
                'nomor_grn' => $penerimaan->nomor,
                'nomor_po' => $po->nomor_po,
                'status_po' => $po->status,
            ], $userId);

            return $penerimaan;
        });
    }
}
