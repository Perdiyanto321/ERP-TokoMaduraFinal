<?php

namespace App\Http\Controllers\Pembelian;

use App\Http\Controllers\Controller;
use App\Services\RestokService;
use Illuminate\View\View;

class RestokController extends Controller
{
    public function __construct(
        private readonly RestokService $restokService
    ) {}

    public function index(): View
    {
        $saranRestok = $this->restokService->daftarSaranRestok();

        return view('pembelian.restok.index', compact('saranRestok'));
    }
}
