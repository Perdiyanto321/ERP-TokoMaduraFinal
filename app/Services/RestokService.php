<?php

namespace App\Services;

use App\Models\DetailPembelian;
use App\Models\Produk;
use Illuminate\Support\Collection;

class RestokService
{
    public function daftarSaranRestok(): Collection
    {
        $produkMenipis = Produk::with('kategori')
            ->dibawahMinimum()
            ->orderByRaw('(stok - stok_minimum) ASC')
            ->get();

        // Hitung kuantitas (dalam satuan eceran) yang sedang dipesan pada PO aktif
        $sedangDipesan = DetailPembelian::with(['pembelian', 'detailPenerimaan', 'produk'])
            ->whereHas('pembelian', fn ($q) => $q->whereIn('status', ['draf', 'disetujui', 'dikirim', 'diterima_sebagian']))
            ->get()
            ->groupBy('produk_id')
            ->map(function ($items) {
                return $items->sum(function ($dp) {
                    $sudahTerima = $dp->detailPenerimaan->sum('qty_diterima');
                    $sisaBeli = max(0, $dp->qty_pesan - $sudahTerima);
                    $isi = max(1, $dp->produk?->isi_per_satuan_beli ?? 1);

                    return $sisaBeli * $isi;
                });
            });

        return $produkMenipis->map(function (Produk $p) use ($sedangDipesan) {
            $dalamPo = (int) ($sedangDipesan[$p->id] ?? 0);
            $kebutuhanEceran = max(0, $p->stok_target - $p->stok - $dalamPo);
            $isi = max(1, $p->isi_per_satuan_beli);
            $saranSatuanBeli = $kebutuhanEceran > 0 ? (int) ceil($kebutuhanEceran / $isi) : 0;

            return (object) [
                'produk' => $p,
                'dalam_po_eceran' => $dalamPo,
                'kebutuhan_eceran' => $kebutuhanEceran,
                'saran_satuan_beli' => $saranSatuanBeli,
                'estimasi_biaya' => $saranSatuanBeli * (float) $p->harga_beli,
            ];
        })->filter(fn ($baris) => $baris->saran_satuan_beli > 0)->values();
    }
}
