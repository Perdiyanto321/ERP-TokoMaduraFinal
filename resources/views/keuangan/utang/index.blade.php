<x-app-layout judul="Utang Supplier">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h2 class="text-[18px] font-semibold">Daftar Utang Pembelian Supplier</h2>
            <p class="text-[13px] text-[#6B7280]">Tagihan Purchase Order bertipe kredit yang telah diterima di gudang.</p>
        </div>
        <div class="rounded-[12px] bg-[#1F2428] px-4 py-2.5 text-white">
            <span class="text-xs text-[#9CA3AF] block">Total Sisa Utang Aktif</span>
            <span class="text-[18px] font-bold angka">{{ \App\Support\Rupiah::format($totalSisaUtang) }}</span>
        </div>
    </div>

    <x-tabel>
        <x-slot:head>
            <tr>
                <th class="px-4 py-2.5">Supplier</th>
                <th class="px-4 py-2.5">Nomor PO</th>
                <th class="px-4 py-2.5">Jatuh Tempo</th>
                <th class="px-4 py-2.5 angka">Total Tagihan</th>
                <th class="px-4 py-2.5 angka">Sisa Utang</th>
                <th class="px-4 py-2.5">Status</th>
                <th class="px-4 py-2.5 text-right">Pelunasan</th>
            </tr>
        </x-slot:head>
        @forelse ($daftarUtang as $u)
            <tr class="h-[46px] hover:bg-[#F9FAFB]">
                <td class="px-4 py-2 font-medium">{{ $u->supplier?->nama }}</td>
                <td class="px-4 py-2 font-mono text-[13px]">{{ $u->pembelian?->nomor_po }}</td>
                <td class="px-4 py-2 text-[13px] {{ $u->jatuh_tempo && $u->jatuh_tempo->isPast() && $u->sisa > 0 ? 'text-[#B03A2E] font-semibold' : 'text-[#6B7280]' }}">
                    {{ $u->jatuh_tempo?->format('d/m/Y') ?? '-' }}
                </td>
                <td class="px-4 py-2 angka text-[#6B7280]">{{ \App\Support\Rupiah::format($u->jumlah) }}</td>
                <td class="px-4 py-2 angka font-bold">{{ \App\Support\Rupiah::format($u->sisa) }}</td>
                <td class="px-4 py-2"><x-status :jenis="$u->status === 'lunas' ? 'sukses' : 'peringatan'" :label="ucfirst(str_replace('_', ' ', $u->status))" /></td>
                <td class="px-4 py-2 text-right">
                    @if (in_array($u->status, ['belum_lunas', 'sebagian']))
                        <form method="POST" action="{{ route('utang.bayar', $u) }}" class="inline-flex items-center gap-1.5">
                            @csrf
                            <input type="number" name="jumlah" max="{{ (int) $u->sisa }}" value="{{ (int) $u->sisa }}" class="input-kontrol py-1 px-2 text-xs w-28 angka" required>
                            <select name="metode" class="input-kontrol py-1 px-2 text-xs w-24">
                                <option value="transfer">Transfer</option>
                                <option value="tunai">Tunai</option>
                            </select>
                            <button type="submit" class="btn-primer py-1 px-2.5 text-xs">Bayar</button>
                        </form>
                    @else
                        <span class="text-xs text-[#9CA3AF]">Lunas</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="p-6 text-center text-[#9CA3AF]">Belum ada catatan utang ke supplier.</td>
            </tr>
        @endforelse
    </x-tabel>

    <div class="mt-4">{{ $daftarUtang->links() }}</div>
</x-app-layout>
