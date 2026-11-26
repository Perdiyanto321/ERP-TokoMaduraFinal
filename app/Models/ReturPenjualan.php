<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReturPenjualan extends Model
{
    protected $table = 'retur_penjualan';

    protected $fillable = [
        'penjualan_id',
        'diproses_oleh',
        'nomor',
        'tanggal',
        'penyelesaian',
        'total_retur',
        'alasan',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'total_retur' => 'decimal:2',
    ];

    public function penjualan(): BelongsTo
    {
        return $this->belongsTo(Penjualan::class, 'penjualan_id');
    }

    public function pemroses(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }

    public function detail(): HasMany
    {
        return $this->hasMany(DetailRetur::class, 'retur_id');
    }
}
