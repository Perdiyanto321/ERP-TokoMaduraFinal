<?php

namespace App\Support;

class Rupiah
{
    public static function format(float|int|string|null $nominal, bool $kurungNegatif = false): string
    {
        $angka = (float) ($nominal ?? 0);
        $formatted = 'Rp' . number_format(abs($angka), 0, ',', '.');

        if ($angka < 0) {
            return $kurungNegatif ? "({$formatted})" : "-{$formatted}";
        }

        return $formatted;
    }
}
