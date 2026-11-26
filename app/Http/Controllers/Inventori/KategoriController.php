<?php

namespace App\Http\Controllers\Inventori;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Services\LogAktivitasService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KategoriController extends Controller
{
    public function __construct(
        private readonly LogAktivitasService $logService
    ) {}

    public function index(): View
    {
        $daftarKategori = Kategori::withCount('produk')->orderBy('nama')->get();

        return view('inventori.kategori.index', compact('daftarKategori'));
    }

    public function simpan(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:80', Rule::unique('kategori', 'nama')],
        ]);

        $kategori = Kategori::create($data);

        $this->logService->catat('buat_kategori', 'kategori', $kategori->id, ['nama' => $kategori->nama]);

        return redirect()->route('kategori.index')->with('sukses', 'Kategori baru berhasil ditambahkan.');
    }

    public function perbarui(Request $request, Kategori $kategori): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:80', Rule::unique('kategori', 'nama')->ignore($kategori->id)],
        ]);

        $kategori->update($data);

        $this->logService->catat('ubah_kategori', 'kategori', $kategori->id, ['nama' => $kategori->nama]);

        return redirect()->route('kategori.index')->with('sukses', 'Nama kategori berhasil diperbarui.');
    }
}
