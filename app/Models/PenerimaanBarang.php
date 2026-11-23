<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PenerimaanBarang extends Model
{
    protected $table = 'penerimaan_barang';

    protected $fillable = [
        'pembelian_id',
        'diterima_oleh',
        'nomor',
        'tanggal',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'datetime',
        ];
    }

    public function pembelian(): BelongsTo
    {
        return $this->belongsTo(Pembelian::class, 'pembelian_id');
    }

    public function penerima(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diterima_oleh');
    }

    public function detail(): HasMany
    {
        return $this->hasMany(DetailPenerimaan::class, 'penerimaan_id');
    }
}
