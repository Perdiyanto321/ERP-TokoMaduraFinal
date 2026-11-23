<?php

namespace App\Services;

use App\Models\PergerakanStok;
use App\Models\Produk;
use DomainException;
use Illuminate\Support\Facades\DB;

class InventoriService
{
    /**
     * Satu-satunya pintu keluar-masuk untuk mengubah kolom produk.stok.
     * Wajib dipanggil di dalam atau memulai DB::transaction().
     */
    public function ubahStok(
        int $produkId,
        int $qtyBertanda,
        string $jenis,
        int $userId,
        ?string $sumberTipe = null,
        ?int $sumberId = null
    ): PergerakanStok {
        if ($qtyBertanda === 0) {
            throw new DomainException('Kuantitas perubahan stok tidak boleh bernilai nol.');
        }

        return DB::transaction(function () use ($produkId, $qtyBertanda, $jenis, $userId, $sumberTipe, $sumberId) {
            $produk = Produk::whereKey($produkId)->lockForUpdate()->firstOrFail();

            $stokBaru = $produk->stok + $qtyBertanda;

            if ($stokBaru < 0) {
                throw new DomainException(
                    "Stok produk '{$produk->nama}' tidak mencukupi (tersedia: {$produk->stok} {$produk->satuan}, dibutuhkan: " . abs($qtyBertanda) . " {$produk->satuan})."
                );
            }

            $produk->stok = $stokBaru;
            $produk->save();

            return PergerakanStok::create([
                'produk_id' => $produk->id,
                'user_id' => $userId,
                'jenis' => $jenis,
                'qty' => $qtyBertanda,
                'stok_sesudah' => $stokBaru,
                'sumber_tipe' => $sumberTipe,
                'sumber_id' => $sumberId,
                'created_at' => now(),
            ]);
        });
    }
}
