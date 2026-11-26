<?php

namespace App\Http\Middleware;

use App\Services\LogAktivitasService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CatatAksesDitolak
{
    public function __construct(
        private readonly LogAktivitasService $logService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->getStatusCode() === 403 && $request->user()) {
            $this->logService->catat(
                aksi: 'akses_ditolak',
                entitas: 'rute',
                entitasId: null,
                detail: [
                    'method' => $request->method(),
                    'url' => $request->fullUrl(),
                    'ip' => $request->ip(),
                ],
                userId: $request->user()->id
            );
        }

        return $response;
    }
}
