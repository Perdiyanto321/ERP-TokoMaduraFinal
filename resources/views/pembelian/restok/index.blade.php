<x-app-layout judul="Saran Restok Barang">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h2 class="text-[18px] font-semibold">Saran Restok Otomatis</h2>
            <p class="text-[13px] text-[var(--teks-redup)]">Produk dengan stok saat ini di bawah atau sama dengan batas minimum (memperhitungkan PO aktif).</p>
        </div>
        @if ($saranRestok->isNotEmpty())
            <a href="{{ route('po.tambah', ['dari_restok' => 1]) }}" class="btn-primer">+ Buat PO dari Saran Restok</a>
        @endif
    </div>

    @if ($saranRestok->isEmpty())
        <x-kosong judul="Semua stok dalam kondisi aman" deskripsi="Tidak ada produk yang berada di bawah batas stok minimum saat ini." />
    @else
        <x-tabel>
            <x-slot:head>
                <tr>
                    <th class="px-4 py-2.5">Produk</th>
                    <th class="px-4 py-2.5 angka">Stok Saat Ini</th>
                    <th class="px-4 py-2.5 angka">Min / Target</th>
                    <th class="px-4 py-2.5 angka">Dalam PO</th>
                    <th class="px-4 py-2.5 angka">Saran Order</th>
                    <th class="px-4 py-2.5 angka">Estimasi Biaya</th>
                </tr>
            </x-slot:head>
            @foreach ($saranRestok as $baris)
                @php $p = $baris->produk; @endphp
                <tr class="h-[42px] hover:bg-[var(--primer-muda)]/50">
                    <td class="px-4 py-2">
                        <span class="font-mono text-xs text-[var(--teks-redup)]">{{ $p->kode }}</span>
                        <p class="font-medium">{{ $p->nama }}</p>
                    </td>
                    <td class="px-4 py-2 angka font-semibold text-[var(--peringatan)]">{{ $p->stok }} {{ $p->satuan }}</td>
                    <td class="px-4 py-2 angka text-[13px] text-[var(--teks-redup)]">{{ $p->stok_minimum }} / {{ $p->stok_target }}</td>
                    <td class="px-4 py-2 angka text-[13px]">{{ $baris->dalam_po_eceran }} {{ $p->satuan }}</td>
                    <td class="px-4 py-2 angka font-semibold text-[var(--primer)]">
                        {{ $baris->saran_satuan_beli }} {{ $p->satuan_beli }}
                        <span class="block text-[11px] font-normal text-[var(--teks-redup)]">({{ $baris->saran_satuan_beli * $p->isi_per_satuan_beli }} {{ $p->satuan }})</span>
                    </td>
                    <td class="px-4 py-2 angka font-medium">{{ \App\Support\Rupiah::format($baris->estimasi_biaya) }}</td>
                </tr>
            @endforeach
        </x-tabel>
    @endif
</x-app-layout>
