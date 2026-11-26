<x-app-layout judul="Data Supplier">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="rounded-[6px] border border-[var(--garis)] bg-[var(--permukaan)] p-5 h-fit">
            <h2 class="text-[16px] font-semibold border-b border-[var(--garis)] pb-3 mb-4">Tambah Supplier</h2>
            <form method="POST" action="{{ route('supplier.simpan') }}" class="space-y-3">
                @csrf
                <x-field label="Nama Supplier / Distributor" name="nama" :required="true">
                    <input id="nama" name="nama" type="text" value="{{ old('nama') }}" class="input-kontrol" required>
                </x-field>
                <x-field label="Nomor Kontak / WA" name="kontak">
                    <input id="kontak" name="kontak" type="text" value="{{ old('kontak') }}" class="input-kontrol" placeholder="0812-xxxx-xxxx">
                </x-field>
                <x-field label="Alamat" name="alamat">
                    <textarea id="alamat" name="alamat" rows="2" class="input-kontrol">{{ old('alamat') }}</textarea>
                </x-field>
                <button type="submit" class="btn-primer w-full">Simpan Supplier</button>
            </form>
        </div>

        <div class="md:col-span-2">
            <x-tabel>
                <x-slot:head>
                    <tr>
                        <th class="px-4 py-2.5">Nama Supplier</th>
                        <th class="px-4 py-2.5">Kontak</th>
                        <th class="px-4 py-2.5">Alamat</th>
                        <th class="px-4 py-2.5 angka">Total PO</th>
                    </tr>
                </x-slot:head>
                @forelse ($daftarSupplier as $sup)
                    <tr class="h-[42px] hover:bg-[var(--primer-muda)]/50">
                        <td class="px-4 py-2 font-medium">{{ $sup->nama }}</td>
                        <td class="px-4 py-2 text-[13px]">{{ $sup->kontak ?: '-' }}</td>
                        <td class="px-4 py-2 text-[13px] text-[var(--teks-redup)]">{{ $sup->alamat ?: '-' }}</td>
                        <td class="px-4 py-2 angka">{{ $sup->pembelian_count }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-4 text-center text-[var(--teks-redup)]">Belum ada data supplier.</td>
                    </tr>
                @endforelse
            </x-tabel>
            <div class="mt-4">{{ $daftarSupplier->links() }}</div>
        </div>
    </div>
</x-app-layout>
