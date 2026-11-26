<x-app-layout judul="Biaya Operasional Toko">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-4 rounded-[12px] border border-[#E6E8EC] bg-white p-5 h-fit">
            <h2 class="text-[16px] font-semibold border-b border-[#F0F1F4] pb-3 mb-4">Catat Pengeluaran Baru</h2>
            <form method="POST" action="{{ route('biaya.simpan') }}" class="space-y-3.5">
                @csrf
                <x-field label="Kategori Biaya" name="kategori_biaya" :required="true">
                    <select id="kategori_biaya" name="kategori_biaya" class="input-kontrol" required>
                        <option value="listrik">Listrik & Air</option>
                        <option value="gaji">Gaji & Upah Karyawan</option>
                        <option value="sewa">Sewa Tempat</option>
                        <option value="kemasan">Kemasan & Plastik Kresek</option>
                        <option value="lainnya">Operasional Lainnya</option>
                    </select>
                </x-field>

                <x-field label="Nominal (Rp)" name="nominal" :required="true">
                    <input id="nominal" name="nominal" type="number" min="100" class="input-kontrol angka" required>
                </x-field>

                <x-field label="Tanggal" name="tanggal" :required="true">
                    <input id="tanggal" name="tanggal" type="date" value="{{ now()->toDateString() }}" class="input-kontrol" required>
                </x-field>

                <x-field label="Keterangan" name="keterangan" :required="true">
                    <textarea id="keterangan" name="keterangan" rows="2" class="input-kontrol" placeholder="Misal: Token listrik toko" required></textarea>
                </x-field>

                <button type="submit" class="btn-primer w-full">Simpan Pengeluaran</button>
            </form>
        </div>

        <div class="lg:col-span-8 space-y-4">
            <div class="flex items-center justify-between rounded-[12px] border border-[#E6E8EC] bg-white p-4">
                <span class="text-[13.5px] font-medium text-[#6B7280]">Total Biaya Operasional Bulan Ini</span>
                <span class="text-[18px] font-bold text-[#B03A2E] angka">{{ \App\Support\Rupiah::format($totalBulanIni) }}</span>
            </div>

            <x-tabel>
                <x-slot:head>
                    <tr>
                        <th class="px-4 py-2.5">Tanggal</th>
                        <th class="px-4 py-2.5">Kategori</th>
                        <th class="px-4 py-2.5">Keterangan</th>
                        <th class="px-4 py-2.5 angka">Nominal</th>
                        <th class="px-4 py-2.5">Dicatat Oleh</th>
                    </tr>
                </x-slot:head>
                @forelse ($daftarBiaya as $b)
                    <tr class="h-[42px] hover:bg-[#F9FAFB]">
                        <td class="px-4 py-2 text-[13px] text-[#6B7280]">{{ $b->tanggal->format('d/m/Y') }}</td>
                        <td class="px-4 py-2 text-[13px] font-medium capitalize">{{ $b->kategori_biaya }}</td>
                        <td class="px-4 py-2 text-[13px]">{{ $b->keterangan }}</td>
                        <td class="px-4 py-2 angka font-semibold">{{ \App\Support\Rupiah::format($b->nominal) }}</td>
                        <td class="px-4 py-2 text-[13px] text-[#6B7280]">{{ $b->user?->karyawan?->nama ?? $b->user?->username }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-6 text-center text-[#9CA3AF]">Belum ada data pengeluaran operasional.</td>
                    </tr>
                @endforelse
            </x-tabel>
            <div class="mt-4">{{ $daftarBiaya->links() }}</div>
        </div>
    </div>
</x-app-layout>
