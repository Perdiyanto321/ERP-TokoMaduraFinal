<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailRetur extends Model
{
    protected $table = 'detail_retur';

    protected $fillable = [
        'retur_id',
        'detail_penjualan_id',
        'qty_retur',
        'kondisi',
        'subtotal',
    ];

    protected $casts = [
        'qty_retur' => 'integer',
        'subtotal' => 'decimal:2',
    ];

    public function retur(): BelongsTo
    {
        return $this->belongsTo(ReturPenjualan::class, 'retur_id');
    }

    public function detailPenjualan(): BelongsTo
    {
        return $this->belongsTo(DetailPenjualan::class, 'detail_penjualan_id');
    }
}
