<x-app-layout judul="Laporan Laba Rugi & Penjualan">
    <form method="GET" action="{{ route('laporan.penjualan') }}" class="mb-5 flex flex-wrap items-end justify-between gap-3 rounded-[12px] border border-[#E6E8EC] bg-white p-3.5">
        <div class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-[#6B7280] mb-1">Dari Tanggal</label>
                <input type="date" name="dari" value="{{ $dari }}" class="input-kontrol">
            </div>
            <div>
                <label class="block text-xs font-medium text-[#6B7280] mb-1">Sampai Tanggal</label>
                <input type="date" name="sampai" value="{{ $sampai }}" class="input-kontrol">
            </div>
            <button type="submit" class="btn-primer">Hitung Laporan</button>
        </div>
        <button type="button" onclick="window.print()" class="btn-sekunder">Cetak Laporan</button>
    </form>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-5 rounded-[12px] border border-[#E6E8EC] bg-white p-5 space-y-3 h-fit">
            <h2 class="text-[16px] font-bold border-b border-[#F0F1F4] pb-3">Ikhtisar Laba Rugi Periode</h2>

            <div class="space-y-2.5 text-[13.5px]">
                <div class="flex justify-between">
                    <span class="text-[#6B7280]">Penjualan Kotor</span>
                    <span class="font-medium angka">{{ \App\Support\Rupiah::format($omzetKotor) }}</span>
                </div>
                <div class="flex justify-between text-[#B03A2E]">
                    <span>Dikurangi: Retur Penjualan</span>
                    <span class="angka">-{{ \App\Support\Rupiah::format($totalRetur) }}</span>
                </div>
                <div class="flex justify-between font-semibold border-t border-[#F0F1F4] pt-2">
                    <span>Pendapatan Bersih</span>
                    <span class="angka">{{ \App\Support\Rupiah::format($omzetBersih) }}</span>
                </div>
                <div class="flex justify-between text-[#6B7280]">
                    <span>Harga Pokok Penjualan (HPP)</span>
                    <span class="angka">-{{ \App\Support\Rupiah::format($hppTerjual) }}</span>
                </div>
                <div class="flex justify-between font-semibold border-t border-[#F0F1F4] pt-2">
                    <span>Laba Kotor</span>
                    <span class="angka">{{ \App\Support\Rupiah::format($labaKotor) }}</span>
                </div>
                <div class="flex justify-between text-[#6B7280]">
                    <span>Biaya Operasional</span>
                    <span class="angka">-{{ \App\Support\Rupiah::format($biayaOperasional) }}</span>
                </div>
                <div class="flex justify-between rounded-[8px] bg-[#1F2428] p-3 text-white font-bold text-[15px] mt-2">
                    <span>Laba Bersih</span>
                    <span class="angka">{{ \App\Support\Rupiah::format($labaBersih) }}</span>
                </div>
            </div>

            <div class="pt-3 border-t border-[#F0F1F4] flex justify-between text-[12.5px]">
                <span class="text-[#6B7280]">Nilai Aset Stok Gudang Saat Ini</span>
                <span class="font-bold text-[#181B1E] angka">{{ \App\Support\Rupiah::format($valuasiStok) }}</span>
            </div>
        </div>

        <div class="lg:col-span-7 space-y-3">
            <h2 class="text-[16px] font-bold">10 Produk Terlaris Periode Ini</h2>
            <x-tabel>
                <x-slot:head>
                    <tr>
                        <th class="px-4 py-2.5">Produk</th>
                        <th class="px-4 py-2.5 angka">Terjual</th>
                        <th class="px-4 py-2.5 angka">Omzet</th>
                        <th class="px-4 py-2.5 angka">Margin Kotor</th>
                    </tr>
                </x-slot:head>
                @forelse ($produkTerlaris as $item)
                    @php $margin = (float) $item->total_omzet - (float) $item->total_hpp; @endphp
                    <tr class="h-[42px] hover:bg-[#F9FAFB]">
                        <td class="px-4 py-2 font-medium">{{ $item->produk?->nama }}</td>
                        <td class="px-4 py-2 angka font-semibold">{{ $item->total_qty }} {{ $item->produk?->satuan }}</td>
                        <td class="px-4 py-2 angka">{{ \App\Support\Rupiah::format($item->total_omzet) }}</td>
                        <td class="px-4 py-2 angka font-semibold text-[#16A34A]">{{ \App\Support\Rupiah::format($margin) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-6 text-center text-[#9CA3AF]">Belum ada data penjualan pada periode ini.</td>
                    </tr>
                @endforelse
            </x-tabel>
        </div>
    </div>
</x-app-layout>
