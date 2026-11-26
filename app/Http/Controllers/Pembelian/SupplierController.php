<?php

namespace App\Http\Controllers\Pembelian;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Services\LogAktivitasService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function __construct(
        private readonly LogAktivitasService $logService
    ) {}

    public function index(): View
    {
        $daftarSupplier = Supplier::withCount('pembelian')->orderBy('nama')->paginate(25);

        return view('pembelian.supplier.index', compact('daftarSupplier'));
    }

    public function simpan(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:120'],
            'kontak' => ['nullable', 'string', 'max:60'],
            'alamat' => ['nullable', 'string', 'max:500'],
        ]);

        $supplier = Supplier::create($data);
        $this->logService->catat('buat_supplier', 'supplier', $supplier->id, ['nama' => $supplier->nama]);

        return redirect()->route('supplier.index')->with('sukses', 'Supplier baru berhasil ditambahkan.');
    }

    public function perbarui(Request $request, Supplier $supplier): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:120'],
            'kontak' => ['nullable', 'string', 'max:60'],
            'alamat' => ['nullable', 'string', 'max:500'],
        ]);

        $supplier->update($data);
        $this->logService->catat('ubah_supplier', 'supplier', $supplier->id, ['nama' => $supplier->nama]);

        return redirect()->route('supplier.index')->with('sukses', 'Data supplier berhasil diperbarui.');
    }
}
