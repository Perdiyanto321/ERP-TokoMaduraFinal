<x-app-layout judul="Purchase Order (PO)">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h2 class="text-[18px] font-semibold">Daftar Purchase Order</h2>
            <p class="text-[13px] text-[var(--teks-redup)]">Kelola pemesanan barang ke supplier hingga penerimaan di gudang.</p>
        </div>
        <a href="{{ route('po.tambah') }}" class="btn-primer">+ Buat PO Baru</a>
    </div>

    <form method="GET" action="{{ route('po.index') }}" class="mb-4 flex flex-wrap items-end gap-3 rounded-[6px] border border-[var(--garis)] bg-[var(--permukaan)] p-3.5">
        <div>
            <label class="block text-xs font-medium text-[var(--teks-redup)] mb-1">Status PO</label>
            <select name="status" class="input-kontrol w-48">
                <option value="">Semua Status</option>
                @foreach (['draf', 'disetujui', 'dikirim', 'diterima_sebagian', 'selesai', 'dibatalkan'] as $st)
                    <option value="{{ $st }}" @selected(request('status') === $st)>{{ ucfirst(str_replace('_', ' ', $st)) }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-primer">Filter</button>
        <a href="{{ route('po.index') }}" class="btn-sekunder">Reset</a>
    </form>

    <x-tabel>
        <x-slot:head>
            <tr>
                <th class="px-4 py-2.5">Nomor PO</th>
                <th class="px-4 py-2.5">Tanggal</th>
                <th class="px-4 py-2.5">Supplier</th>
                <th class="px-4 py-2.5">Pembayaran</th>
                <th class="px-4 py-2.5 angka">Total Nilai</th>
                <th class="px-4 py-2.5">Status</th>
                <th class="px-4 py-2.5 text-right">Aksi</th>
            </tr>
        </x-slot:head>
        @forelse ($daftarPo as $po)
            <tr class="h-[42px] hover:bg-[var(--primer-muda)]/50">
                <td class="px-4 py-2 font-mono text-[13px] font-medium">{{ $po->nomor_po }}</td>
                <td class="px-4 py-2 text-[13px]">{{ $po->tanggal->format('d/m/Y') }}</td>
                <td class="px-4 py-2 font-medium">{{ $po->supplier?->nama }}</td>
                <td class="px-4 py-2 text-[13px] capitalize">
                    {{ $po->jenis_bayar }}
                    @if ($po->jatuh_tempo)
                        <span class="block text-[11px] text-[var(--teks-redup)]">JT: {{ $po->jatuh_tempo->format('d/m/Y') }}</span>
                    @endif
                </td>
                <td class="px-4 py-2 angka font-semibold">{{ \App\Support\Rupiah::format($po->total) }}</td>
                <td class="px-4 py-2"><x-status :jenis="$po->status" :label="ucfirst(str_replace('_', ' ', $po->status))" /></td>
                <td class="px-4 py-2 text-right">
                    <a href="{{ route('po.detail', $po) }}" class="text-[13px] font-medium text-[var(--primer)] hover:underline">Detail</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="p-4 text-center text-[var(--teks-redup)]">Belum ada data Purchase Order.</td>
            </tr>
        @endforelse
    </x-tabel>

    <div class="mt-4">{{ $daftarPo->links() }}</div>
</x-app-layout>
