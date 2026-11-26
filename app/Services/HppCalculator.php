<?php

namespace App\Services;

class HppCalculator
{
    /**
     * Menghitung HPP rata-rata bergerak (Moving Average) dalam satuan eceran.
     */
    public function hitung(
        int $stokLama,
        float $hppLama,
        int $qtyMasukEceran,
        float $hargaBeliPerSatuanBeli,
        int $isiPerSatuanBeli
    ): float {
        $isi = max(1, $isiPerSatuanBeli);
        $hppMasukPerEceran = $hargaBeliPerSatuanBeli / $isi;
        $totalUnit = $stokLama + $qtyMasukEceran;

        if ($totalUnit <= 0) {
            return round($hppMasukPerEceran, 2);
        }

        if ($stokLama <= 0) {
            return round($hppMasukPerEceran, 2);
        }

        $nilaiLama = $stokLama * $hppLama;
        $nilaiMasuk = $qtyMasukEceran * $hppMasukPerEceran;

        return round(($nilaiLama + $nilaiMasuk) / $totalUnit, 2);
    }
}
