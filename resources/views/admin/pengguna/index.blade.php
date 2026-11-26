<x-app-layout judul="Pengguna & Karyawan">
    <div class="flex items-center justify-between mb-5">
        <div>
            <h2 class="text-[18px] font-semibold text-[var(--teks)]">Daftar Pengguna & Karyawan</h2>
            <p class="text-[13px] text-[var(--teks-redup)]">Kelola akun akses sistem berdasarkan peran operasional toko.</p>
        </div>
        <button type="button" x-data @click="$dispatch('buka-modal', 'modal-tambah-pengguna')" class="btn-primer">
            + Tambah Pengguna
        </button>
    </div>

    <x-tabel>
        <x-slot:head>
            <tr>
                <th class="px-4 py-2.5">Nama Karyawan</th>
                <th class="px-4 py-2.5">Jabatan</th>
                <th class="px-4 py-2.5">Username</th>
                <th class="px-4 py-2.5">Peran Akses</th>
                <th class="px-4 py-2.5">Status</th>
                <th class="px-4 py-2.5 text-right">Aksi</th>
            </tr>
        </x-slot:head>

        @forelse ($daftarPengguna as $u)
            <tr class="h-[40px] hover:bg-[var(--primer-muda)]/50">
                <td class="px-4 py-2 font-medium">{{ $u->karyawan?->nama ?? '-' }}</td>
                <td class="px-4 py-2 text-[var(--teks-redup)]">{{ $u->karyawan?->jabatan ?? '-' }}</td>
                <td class="px-4 py-2 font-mono text-[13px]">{{ $u->username }}</td>
                <td class="px-4 py-2 capitalize">{{ $u->role?->nama }}</td>
                <td class="px-4 py-2">
                    <x-status :jenis="$u->status" :label="ucfirst($u->status)" />
                </td>
                <td class="px-4 py-2 text-right">
                    <a href="{{ route('pengguna.edit', $u) }}" class="text-[13px] font-medium text-[var(--primer)] hover:underline">Ubah</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="p-4 text-center text-[var(--teks-redup)]">Belum ada data pengguna.</td>
            </tr>
        @endforelse
    </x-tabel>

    <div class="mt-4">
        {{ $daftarPengguna->links() }}
    </div>

    <x-modal nama="modal-tambah-pengguna" judul="Tambah Akun Pengguna Baru" lebar="max-w-lg">
        <form method="POST" action="{{ route('pengguna.simpan') }}" class="space-y-3.5">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <x-field label="Nama Lengkap" name="nama" :required="true">
                    <input id="nama" name="nama" type="text" value="{{ old('nama') }}" class="input-kontrol" required>
                </x-field>
                <x-field label="Jabatan" name="jabatan" :required="true">
                    <input id="jabatan" name="jabatan" type="text" value="{{ old('jabatan') }}" class="input-kontrol" required>
                </x-field>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <x-field label="Username" name="username" :required="true">
                    <input id="username" name="username" type="text" value="{{ old('username') }}" class="input-kontrol" required>
                </x-field>
                <x-field label="Peran Akses" name="role_id" :required="true">
                    <select id="role_id" name="role_id" class="input-kontrol" required>
                        @foreach ($roles as $r)
                            <option value="{{ $r->id }}" @selected(old('role_id') == $r->id)>{{ ucfirst($r->nama) }}</option>
                        @endforeach
                    </select>
                </x-field>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <x-field label="Kata Sandi (min. 8 karakter)" name="password" :required="true">
                    <input id="password" name="password" type="password" class="input-kontrol" required>
                </x-field>
                <x-field label="Konfirmasi Kata Sandi" name="password_confirmation" :required="true">
                    <input id="password_confirmation" name="password_confirmation" type="password" class="input-kontrol" required>
                </x-field>
            </div>

            <input type="hidden" name="status" value="aktif">

            <div class="flex justify-end gap-2 pt-3 border-t border-[var(--garis)]">
                <button type="button" @click="$dispatch('tutup-modal', 'modal-tambah-pengguna')" class="btn-sekunder">Batal</button>
                <button type="submit" class="btn-primer">Simpan Pengguna</button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
