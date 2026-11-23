<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Penjualan extends Model
{
    protected $table = 'penjualan';

    protected $fillable = [
        'kasir_id',
        'pelanggan_id',
        'nomor',
        'tanggal',
        'total',
        'jenis_bayar',
        'jumlah_dibayar',
        'status',
        'idempotency_key',
        'alasan_batal',
        'dibatalkan_oleh',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'datetime',
            'total' => 'decimal:2',
            'jumlah_dibayar' => 'decimal:2',
        ];
    }

    public function kasir(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kasir_id');
    }

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    public function pembatal(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibatalkan_oleh');
    }

    public function detail(): HasMany
    {
        return $this->hasMany(DetailPenjualan::class, 'penjualan_id');
    }

    public function retur(): HasMany
    {
        return $this->hasMany(ReturPenjualan::class, 'penjualan_id');
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'penjualan_id');
    }

    public function getKembalianAttribute(): float
    {
        if ($this->jenis_bayar === 'kasbon') {
            return 0.0;
        }

        return max(0.0, (float) $this->jumlah_dibayar - (float) $this->total);
    }
}
