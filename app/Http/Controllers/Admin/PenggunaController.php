<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PerbaruiPenggunaRequest;
use App\Http\Requests\Admin\SimpanPenggunaRequest;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User;
use App\Services\LogAktivitasService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PenggunaController extends Controller
{
    public function __construct(
        private readonly LogAktivitasService $logService
    ) {}

    public function index(): View
    {
        $daftarPengguna = User::with(['role', 'karyawan'])
            ->orderBy('id')
            ->paginate(25);

        $roles = Role::orderBy('id')->get();

        return view('admin.pengguna.index', compact('daftarPengguna', 'roles'));
    }

    public function simpan(SimpanPenggunaRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $karyawan = Karyawan::create([
                'nama' => $data['nama'],
                'jabatan' => $data['jabatan'],
                'status' => $data['status'],
            ]);

            $user = User::create([
                'role_id' => $data['role_id'],
                'karyawan_id' => $karyawan->id,
                'username' => $data['username'],
                'password' => $data['password'],
                'status' => $data['status'],
            ]);

            $this->logService->catat(
                aksi: 'buat_pengguna',
                entitas: 'users',
                entitasId: $user->id,
                detail: ['username' => $user->username, 'role_id' => $user->role_id]
            );
        });

        return redirect()->route('pengguna.index')->with('sukses', 'Akun pengguna dan data karyawan berhasil ditambahkan.');
    }

    public function edit(User $pengguna): View
    {
        $pengguna->load(['role', 'karyawan']);
        $roles = Role::orderBy('id')->get();

        return view('admin.pengguna.edit', compact('pengguna', 'roles'));
    }

    public function perbarui(PerbaruiPenggunaRequest $request, User $pengguna): RedirectResponse
    {
        $data = $request->validated();

        if ($pengguna->id === Auth::id() && $data['status'] === 'nonaktif') {
            return back()->with('galat', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        DB::transaction(function () use ($data, $pengguna) {
            if ($pengguna->karyawan) {
                $pengguna->karyawan->update([
                    'nama' => $data['nama'],
                    'jabatan' => $data['jabatan'],
                    'status' => $data['status'],
                ]);
            }

            $payloadUser = [
                'role_id' => $data['role_id'],
                'username' => $data['username'],
                'status' => $data['status'],
            ];

            if (! empty($data['password'])) {
                $payloadUser['password'] = $data['password'];
            }

            $pengguna->update($payloadUser);

            $this->logService->catat(
                aksi: 'ubah_pengguna',
                entitas: 'users',
                entitasId: $pengguna->id,
                detail: ['username' => $pengguna->username, 'status' => $pengguna->status]
            );
        });

        return redirect()->route('pengguna.index')->with('sukses', 'Data pengguna berhasil diperbarui.');
    }
}
