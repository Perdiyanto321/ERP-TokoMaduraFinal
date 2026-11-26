<x-app-layout :judul="'Terima Barang — ' . $po->nomor_po">
    <div class="max-w-3xl rounded-[6px] border border-[var(--garis)] bg-[var(--permukaan)] p-6">
        <div class="border-b border-[var(--garis)] pb-3 mb-4">
            <h2 class="text-[17px] font-semibold">Penerimaan Barang: {{ $po->nomor_po }}</h2>
            <p class="text-[13px] text-[var(--teks-redup)]">Supplier: {{ $po->supplier?->nama }}. Masukkan jumlah satuan kulakan yang diterima saat ini.</p>
        </div>

        <form method="POST" action="{{ route('po.terima.proses', $po) }}" class="space-y-4">
            @csrf

            <x-tabel>
                <x-slot:head>
                    <tr>
                        <th class="px-4 py-2">Produk</th>
                        <th class="px-4 py-2 angka">Dipesan</th>
                        <th class="px-4 py-2 angka">Sisa Belum Terima</th>
                        <th class="px-4 py-2 w-40 angka">Diterima Sekarang</th>
                    </tr>
                </x-slot:head>
                @foreach ($po->detail as $d)
                    @php
                        $sudah = $d->detailPenerimaan->sum('qty_diterima');
                        $sisa = $d->qty_pesan - $sudah;
                    @endphp
                    <tr>
                        <td class="px-4 py-2.5">
                            <p class="font-medium">{{ $d->produk?->nama }}</p>
                            <p class="text-xs text-[var(--teks-redup)]">1 {{ $d->produk?->satuan_beli }} = {{ $d->produk?->isi_per_satuan_beli }} {{ $d->produk?->satuan }}</p>
                        </td>
                        <td class="px-4 py-2.5 angka">{{ $d->qty_pesan }} {{ $d->produk?->satuan_beli }}</td>
                        <td class="px-4 py-2.5 angka font-semibold">{{ $sisa }} {{ $d->produk?->satuan_beli }}</td>
                        <td class="px-4 py-2.5">
                            @if ($sisa > 0)
                                <input type="number" min="0" max="{{ $sisa }}" name="terima[{{ $d->id }}]" value="{{ old("terima.{$d->id}", $sisa) }}" class="input-kontrol angka">
                            @else
                                <span class="block text-right text-xs text-[var(--sukses)] font-medium">Lengkap</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-tabel>

            <div class="flex justify-end gap-2 pt-3 border-t border-[var(--garis)]">
                <a href="{{ route('po.detail', $po) }}" class="btn-sekunder">Batal</a>
                <button type="submit" class="btn-primer">Simpan Penerimaan & Perbarui Stok</button>
            </div>
        </form>
    </div>
</x-app-layout>
