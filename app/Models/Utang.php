<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Utang extends Model
{
    protected $table = 'utang';

    protected $fillable = [
        'supplier_id',
        'pembelian_id',
        'jumlah',
        'sisa',
        'jatuh_tempo',
        'status',
    ];

    protected $casts = [
        'jumlah' => 'decimal:2',
        'sisa' => 'decimal:2',
        'jatuh_tempo' => 'date',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function pembelian(): BelongsTo
    {
        return $this->belongsTo(Pembelian::class, 'pembelian_id');
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'utang_id');
    }
}
