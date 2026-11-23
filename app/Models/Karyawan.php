<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Karyawan extends Model
{
    protected $table = 'karyawan';

    protected $fillable = ['nama', 'jabatan', 'status'];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'karyawan_id');
    }
}
