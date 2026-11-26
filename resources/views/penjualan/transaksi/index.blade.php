<x-app-layout judul="Riwayat Penjualan">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h2 class="text-[18px] font-semibold">Riwayat Transaksi Penjualan</h2>
            <p class="text-[13px] text-[#6B7280]">Daftar seluruh struk penjualan kasir beserta status pembayaran.</p>
        </div>
        <a href="{{ route('kasir.index') }}" class="btn-primer">+ Buka Kasir (POS)</a>
    </div>

    <form method="GET" action="{{ route('penjualan.index') }}" class="mb-4 flex flex-wrap items-end gap-3 rounded-[12px] border border-[#E6E8EC] bg-white p-3.5">
        <div>
            <label class="block text-xs font-medium text-[#6B7280] mb-1">Nomor Struk</label>
            <input type="text" name="cari" value="{{ request('cari') }}" placeholder="TRX-..." class="input-kontrol w-48">
        </div>
        <div>
            <label class="block text-xs font-medium text-[#6B7280] mb-1">Metode Bayar</label>
            <select name="metode" class="input-kontrol w-40">
                <option value="">Semua Metode</option>
                <option value="tunai" @selected(request('metode') === 'tunai')>Tunai</option>
                <option value="transfer" @selected(request('metode') === 'transfer')>Transfer/QRIS</option>
                <option value="kasbon" @selected(request('metode') === 'kasbon')>Kasbon</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-[#6B7280] mb-1">Tanggal</label>
            <input type="date" name="tanggal" value="{{ request('tanggal') }}" class="input-kontrol">
        </div>
        <button type="submit" class="btn-primer">Filter</button>
        <a href="{{ route('penjualan.index') }}" class="btn-sekunder">Reset</a>
    </form>

    <x-tabel>
        <x-slot:head>
            <tr>
                <th class="px-4 py-2.5">Nomor Struk</th>
                <th class="px-4 py-2.5">Waktu</th>
                <th class="px-4 py-2.5">Kasir</th>
                <th class="px-4 py-2.5">Metode</th>
                <th class="px-4 py-2.5 angka">Total</th>
                <th class="px-4 py-2.5">Status</th>
                <th class="px-4 py-2.5 text-right">Aksi</th>
            </tr>
        </x-slot:head>
        @forelse ($daftarPenjualan as $pj)
            <tr class="h-[42px] hover:bg-[#F9FAFB]">
                <td class="px-4 py-2 font-mono text-[13px] font-semibold">{{ $pj->nomor_struk }}</td>
                <td class="px-4 py-2 text-[13px] text-[#6B7280]">{{ $pj->tanggal->format('d/m/Y H:i') }}</td>
                <td class="px-4 py-2 text-[13px]">{{ $pj->kasir?->karyawan?->nama ?? $pj->kasir?->username }}</td>
                <td class="px-4 py-2 text-[13px] capitalize">
                    {{ $pj->metode_bayar }}
                    @if ($pj->pelanggan)
                        <span class="block text-[11px] text-[#6B7280]">{{ $pj->pelanggan->nama }}</span>
                    @endif
                </td>
                <td class="px-4 py-2 angka font-semibold">{{ \App\Support\Rupiah::format($pj->total) }}</td>
                <td class="px-4 py-2"><x-status :jenis="$pj->status" :label="ucfirst($pj->status)" /></td>
                <td class="px-4 py-2 text-right">
                    <a href="{{ route('penjualan.detail', $pj) }}" class="text-[13px] font-medium text-[#181B1E] underline">Struk & Detail</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="p-6 text-center text-[#9CA3AF]">Belum ada transaksi penjualan tercatat.</td>
            </tr>
        @endforelse
    </x-tabel>

    <div class="mt-4">{{ $daftarPenjualan->links() }}</div>
</x-app-layout>
