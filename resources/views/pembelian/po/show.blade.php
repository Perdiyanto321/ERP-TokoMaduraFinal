<x-app-layout :judul="'Detail PO ' . $po->nomor_po">
    <div class="flex flex-wrap items-start justify-between gap-4 mb-5">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-[19px] font-semibold font-mono">{{ $po->nomor_po }}</h2>
                <x-status :jenis="$po->status" :label="ucfirst(str_replace('_', ' ', $po->status))" />
            </div>
            <p class="text-[13px] text-[var(--teks-redup)] mt-1">
                Supplier: <strong class="text-[var(--teks)]">{{ $po->supplier?->nama }}</strong> &bull;
                Tanggal: {{ $po->tanggal->format('d/m/Y') }} &bull;
                Metode: <span class="capitalize">{{ $po->jenis_bayar }}</span>
                @if ($po->jatuh_tempo) (Jatuh tempo: {{ $po->jatuh_tempo->format('d/m/Y') }}) @endif
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if ($po->status === 'draf' && auth()->user()->hasRole('admin'))
                <form method="POST" action="{{ route('po.status', $po) }}">
                    @csrf
                    <input type="hidden" name="status" value="disetujui">
                    <button type="submit" class="btn-primer">Setujui PO</button>
                </form>
            @endif

            @if ($po->status === 'disetujui')
                <form method="POST" action="{{ route('po.status', $po) }}">
                    @csrf
                    <input type="hidden" name="status" value="dikirim">
                    <button type="submit" class="btn-sekunder">Tandai Dikirim ke Supplier</button>
                </form>
            @endif

            @if (in_array($po->status, ['disetujui', 'dikirim', 'diterima_sebagian']))
                <a href="{{ route('po.terima.form', $po) }}" class="btn-primer">Terima Barang</a>
            @endif

            @if (in_array($po->status, ['draf', 'disetujui']) && auth()->user()->hasRole('admin'))
                <form method="POST" action="{{ route('po.status', $po) }}" onsubmit="return confirm('Batalkan Purchase Order ini?')">
                    @csrf
                    <input type="hidden" name="status" value="dibatalkan">
                    <button type="submit" class="btn-sekunder text-[var(--bahaya)]">Batalkan PO</button>
                </form>
            @endif
        </div>
    </div>

    <x-tabel class="mb-6">
        <x-slot:head>
            <tr>
                <th class="px-4 py-2.5">Produk</th>
                <th class="px-4 py-2.5 angka">Qty Dipesan</th>
                <th class="px-4 py-2.5 angka">Sudah Diterima</th>
                <th class="px-4 py-2.5 angka">Sisa</th>
                <th class="px-4 py-2.5 angka">Harga Beli</th>
                <th class="px-4 py-2.5 angka">Subtotal</th>
            </tr>
        </x-slot:head>
        @foreach ($po->detail as $d)
            @php
                $sudah = $d->detailPenerimaan->sum('qty_diterima');
                $sisa = $d->qty_pesan - $sudah;
            @endphp
            <tr class="h-[42px]">
                <td class="px-4 py-2 font-medium">{{ $d->produk?->nama }}</td>
                <td class="px-4 py-2 angka">{{ $d->qty_pesan }} {{ $d->produk?->satuan_beli }}</td>
                <td class="px-4 py-2 angka text-[var(--sukses)] font-medium">{{ $sudah }} {{ $d->produk?->satuan_beli }}</td>
                <td class="px-4 py-2 angka {{ $sisa > 0 ? 'text-[var(--peringatan)] font-semibold' : 'text-[var(--teks-redup)]' }}">{{ $sisa }}</td>
                <td class="px-4 py-2 angka">{{ \App\Support\Rupiah::format($d->harga_beli) }}</td>
                <td class="px-4 py-2 angka font-medium">{{ \App\Support\Rupiah::format($d->qty_pesan * $d->harga_beli) }}</td>
            </tr>
        @endforeach
        <tr class="bg-[#F8FAFB] font-semibold">
            <td colspan="5" class="px-4 py-2.5 text-right">Total Purchase Order</td>
            <td class="px-4 py-2.5 angka text-[16px] text-[var(--primer)]">{{ \App\Support\Rupiah::format($po->total) }}</td>
        </tr>
    </x-tabel>

    @if ($po->penerimaan->isNotEmpty())
        <h3 class="text-[15px] font-semibold mb-2">Riwayat Penerimaan Barang (GRN)</h3>
        <x-tabel>
            <x-slot:head>
                <tr>
                    <th class="px-4 py-2">Nomor GRN</th>
                    <th class="px-4 py-2">Waktu Terima</th>
                    <th class="px-4 py-2">Diterima Oleh</th>
                </tr>
            </x-slot:head>
            @foreach ($po->penerimaan as $grn)
                <tr class="h-[40px]">
                    <td class="px-4 py-2 font-mono text-[13px]">{{ $grn->nomor }}</td>
                    <td class="px-4 py-2 text-[13px]">{{ $grn->tanggal->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-2 text-[13px]">{{ $grn->penerima?->karyawan?->nama ?? $grn->penerima?->username }}</td>
                </tr>
            @endforeach
        </x-tabel>
    @endif
</x-app-layout>
