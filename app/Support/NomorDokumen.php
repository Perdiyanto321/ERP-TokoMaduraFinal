<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NomorDokumen
{
    /**
     * Menghasilkan nomor dokumen berformat PREFIX-YYYYMMDD-XXXX
     * Menggunakan lock pada tabel target agar aman dari konkurensi.
     */
    public static function buat(string $prefix, string $tabel, string $kolom = 'nomor'): string
    {
        $tanggal = now()->format('Ymd');
        $awalan = "{$prefix}-{$tanggal}-";

        $nomorTerakhir = DB::table($tabel)
            ->where($kolom, 'like', $awalan . '%')
            ->lockForUpdate()
            ->orderByDesc($kolom)
            ->value($kolom);

        if ($nomorTerakhir) {
            $urutanTerakhir = (int) Str::afterLast($nomorTerakhir, '-');
            $urutanBaru = str_pad((string) ($urutanTerakhir + 1), 4, '0', STR_PAD_LEFT);
        } else {
            $urutanBaru = '0001';
        }

        return $awalan . $urutanBaru;
    }
}
