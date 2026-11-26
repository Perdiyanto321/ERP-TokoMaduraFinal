<x-app-layout judul="Ubah Data Pengguna">
    <div class="max-w-xl rounded-[6px] border border-[var(--garis)] bg-[var(--permukaan)] p-5">
        <div class="border-b border-[var(--garis)] pb-3 mb-4">
            <h2 class="text-[16px] font-semibold">Ubah Pengguna: {{ $pengguna->username }}</h2>
        </div>

        <form method="POST" action="{{ route('pengguna.perbarui', $pengguna) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <x-field label="Nama Lengkap" name="nama" :required="true">
                    <input id="nama" name="nama" type="text" value="{{ old('nama', $pengguna->karyawan?->nama) }}" class="input-kontrol" required>
                </x-field>
                <x-field label="Jabatan" name="jabatan" :required="true">
                    <input id="jabatan" name="jabatan" type="text" value="{{ old('jabatan', $pengguna->karyawan?->jabatan) }}" class="input-kontrol" required>
                </x-field>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <x-field label="Username" name="username" :required="true">
                    <input id="username" name="username" type="text" value="{{ old('username', $pengguna->username) }}" class="input-kontrol" required>
                </x-field>
                <x-field label="Peran Akses" name="role_id" :required="true">
                    <select id="role_id" name="role_id" class="input-kontrol" required>
                        @foreach ($roles as $r)
                            <option value="{{ $r->id }}" @selected(old('role_id', $pengguna->role_id) == $r->id)>{{ ucfirst($r->nama) }}</option>
                        @endforeach
                    </select>
                </x-field>
                <x-field label="Status Akun" name="status" :required="true">
                    <select id="status" name="status" class="input-kontrol" required>
                        <option value="aktif" @selected(old('status', $pengguna->status) === 'aktif')>Aktif</option>
                        <option value="nonaktif" @selected(old('status', $pengguna->status) === 'nonaktif')>Nonaktif</option>
                    </select>
                </x-field>
            </div>

            <div class="grid grid-cols-2 gap-4 pt-2 border-t border-[var(--garis)]">
                <x-field label="Kata Sandi Baru (Opsional)" name="password" hint="Kosongkan jika tidak ingin mengganti kata sandi">
                    <input id="password" name="password" type="password" class="input-kontrol">
                </x-field>
                <x-field label="Konfirmasi Kata Sandi Baru" name="password_confirmation">
                    <input id="password_confirmation" name="password_confirmation" type="password" class="input-kontrol">
                </x-field>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-[var(--garis)]">
                <a href="{{ route('pengguna.index') }}" class="btn-sekunder">Batal</a>
                <button type="submit" class="btn-primer">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</x-app-layout>
