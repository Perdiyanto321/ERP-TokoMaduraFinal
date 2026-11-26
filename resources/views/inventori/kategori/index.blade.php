<x-app-layout judul="Kategori Produk">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="rounded-[6px] border border-[var(--garis)] bg-[var(--permukaan)] p-5 h-fit">
            <h2 class="text-[16px] font-semibold border-b border-[var(--garis)] pb-3 mb-4">Tambah Kategori</h2>
            <form method="POST" action="{{ route('kategori.simpan') }}" class="space-y-3">
                @csrf
                <x-field label="Nama Kategori" name="nama" :required="true">
                    <input id="nama" name="nama" type="text" value="{{ old('nama') }}" class="input-kontrol" placeholder="Misal: Bumbu Dapur" required>
                </x-field>
                <button type="submit" class="btn-primer w-full">Simpan Kategori</button>
            </form>
        </div>

        <div class="md:col-span-2">
            <x-tabel>
                <x-slot:head>
                    <tr>
                        <th class="px-4 py-2.5">Nama Kategori</th>
                        <th class="px-4 py-2.5 angka">Jumlah Produk</th>
                        <th class="px-4 py-2.5 text-right">Ubah Nama</th>
                    </tr>
                </x-slot:head>

                @forelse ($daftarKategori as $kat)
                    <tr class="h-[44px] hover:bg-[var(--primer-muda)]/50">
                        <td class="px-4 py-2 font-medium">{{ $kat->nama }}</td>
                        <td class="px-4 py-2 angka">{{ $kat->produk_count }} item</td>
                        <td class="px-4 py-2 text-right">
                            <form method="POST" action="{{ route('kategori.perbarui', $kat) }}" class="inline-flex items-center gap-1.5">
                                @csrf
                                @method('PUT')
                                <input type="text" name="nama" value="{{ $kat->nama }}" class="input-kontrol py-1 px-2 text-xs w-44" required>
                                <button type="submit" class="btn-sekunder py-1 px-2.5 text-xs">Simpan</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="p-4 text-center text-[var(--teks-redup)]">Belum ada kategori.</td>
                    </tr>
                @endforelse
            </x-tabel>
        </div>
    </div>
</x-app-layout>
