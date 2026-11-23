<?php

namespace App\Services;

use App\Models\LogAktivitas;
use Illuminate\Support\Facades\Auth;

class LogAktivitasService
{
    public function catat(
        string $aksi,
        string $entitas,
        ?int $entitasId = null,
        array $detail = [],
        ?int $userId = null
    ): LogAktivitas {
        return LogAktivitas::create([
            'user_id' => $userId ?? Auth::id(),
            'aksi' => $aksi,
            'entitas' => $entitas,
            'entitas_id' => $entitasId,
            'detail' => empty($detail) ? null : $detail,
            'waktu' => now(),
        ]);
    }
}
