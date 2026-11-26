<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Piutang extends Model
{
    protected $table = 'piutang';

    protected $fillable = [
        'pelanggan_id',
        'penjualan_id',
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

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    public function penjualan(): BelongsTo
    {
        return $this->belongsTo(Penjualan::class, 'penjualan_id');
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'piutang_id');
    }
}
