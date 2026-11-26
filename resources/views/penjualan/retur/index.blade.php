<x-app-layout judul="Retur Penjualan (D-10)">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Kolom Kiri: Pilih Transaksi dalam Batas 10 Hari --}}
        <div class="rounded-[12px] border border-[#E6E8EC] bg-white p-5 h-fit">
            <h2 class="text-[16px] font-semibold border-b border-[#F0F1F4] pb-3 mb-3">Buat Retur Baru (Maks. H+10)</h2>
            <p class="text-[12.5px] text-[#6B7280] mb-4">Pilih struk transaksi 10 hari terakhir yang akan diproses pengembalian barangnya:</p>

            <div class="space-y-2 max-h-[380px] overflow-y-auto">
                @forelse ($transaksiEligibel as $trx)
                    <a href="{{ route('retur.tambah', $trx) }}"
                       class="block rounded-[8px] border border-[#E6E8EC] p-3 hover:border-[#1F2428] hover:bg-[#F9FAFB] transition">
                        <div class="flex justify-between text-[13px] font-semibold">
                            <span class="font-mono">{{ $trx->nomor_struk }}</span>
                            <span class="angka">{{ \App\Support\Rupiah::format($trx->total) }}</span>
                        </div>
                        <p class="text-[11.5px] text-[#6B7280] mt-0.5">
                            {{ $trx->tanggal->format('d/m/Y H:i') }} &bull; <span class="capitalize">{{ $trx->metode_bayar }}</span>
                        </p>
                    </a>
                @empty
                    <p class="text-[13px] text-[#9CA3AF]">Tidak ada transaksi selesai dalam 10 hari terakhir.</p>
                @endforelse
            </div>
        </div>

        {{-- Kolom Kanan: Riwayat Dokumen Retur --}}
        <div class="lg:col-span-2">
            <x-tabel>
                <x-slot:head>
                    <tr>
                        <th class="px-4 py-2.5">Nomor Retur</th>
                        <th class="px-4 py-2.5">Struk Asal</th>
                        <th class="px-4 py-2.5">Tanggal</th>
                        <th class="px-4 py-2.5">Penyelesaian</th>
                        <th class="px-4 py-2.5 angka">Nilai Retur</th>
                    </tr>
                </x-slot:head>
                @forelse ($daftarRetur as $r)
                    <tr class="h-[42px] hover:bg-[#F9FAFB]">
                        <td class="px-4 py-2 font-mono text-[13px] font-semibold">{{ $r->nomor }}</td>
                        <td class="px-4 py-2 font-mono text-[13px]">{{ $r->penjualan?->nomor_struk }}</td>
                        <td class="px-4 py-2 text-[13px] text-[#6B7280]">{{ $r->tanggal->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-2 text-[13px] capitalize">{{ str_replace('_', ' ', $r->penyelesaian) }}</td>
                        <td class="px-4 py-2 angka font-semibold">{{ \App\Support\Rupiah::format($r->total_retur) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-6 text-center text-[#9CA3AF]">Belum ada dokumen retur penjualan.</td>
                    </tr>
                @endforelse
            </x-tabel>
            <div class="mt-4">{{ $daftarRetur->links() }}</div>
        </div>
    </div>
</x-app-layout>
