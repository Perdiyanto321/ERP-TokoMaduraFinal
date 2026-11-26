<x-app-layout :judul="'Detail Transaksi ' . $penjualan->nomor_struk">
    <style>
        @media print {
            aside, header, .no-print { display: none !important; }
            body { background: #FFFFFF !important; }
            #area-struk { border: none !important; box-shadow: none !important; width: 100% !important; max-width: 80mm !important; margin: 0 auto !important; }
        }
    </style>

    <div class="no-print flex flex-wrap items-center justify-between gap-3 mb-5">
        <a href="{{ route('penjualan.index') }}" class="btn-sekunder">&larr; Kembali ke Riwayat</a>
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" onclick="window.print()" class="btn-primer">Cetak Struk</button>
            @if ($bisaDiretur)
                <a href="{{ route('retur.tambah', $penjualan) }}" class="btn-sekunder">Proses Retur (D-10)</a>
            @endif
            @if ($penjualan->status === 'selesai' && auth()->user()->hasRole('admin'))
                <button type="button" x-data @click="$dispatch('buka-modal', 'modal-batal-trx')" class="btn-bahaya">Batalkan Transaksi</button>
            @endif
        </div>
    </div>

    {{-- Kartu Struk Thermal Siap Cetak --}}
    <div id="area-struk" class="max-w-md mx-auto rounded-[12px] border border-[#E6E8EC] bg-white p-6 font-mono text-[13px]">
        <div class="text-center border-b border-dashed border-[#D1D5DB] pb-3 mb-3">
            <p class="text-[16px] font-bold uppercase">{{ config('toko.nama') }}</p>
            <p class="text-[11px] text-[#6B7280]">Struk Bukti Pembayaran</p>
        </div>

        <div class="space-y-1 text-[12px] border-b border-dashed border-[#D1D5DB] pb-3 mb-3">
            <div class="flex justify-between"><span>No. Struk:</span><span class="font-bold">{{ $penjualan->nomor_struk }}</span></div>
            <div class="flex justify-between"><span>Tanggal:</span><span>{{ $penjualan->tanggal->format('d/m/Y H:i') }}</span></div>
            <div class="flex justify-between"><span>Kasir:</span><span>{{ $penjualan->kasir?->karyawan?->nama ?? $penjualan->kasir?->username }}</span></div>
            <div class="flex justify-between"><span>Metode:</span><span class="uppercase font-semibold">{{ $penjualan->metode_bayar }}</span></div>
            @if ($penjualan->pelanggan)
                <div class="flex justify-between"><span>Pelanggan:</span><span>{{ $penjualan->pelanggan->nama }}</span></div>
            @endif
            <div class="flex justify-between"><span>Status:</span><span class="uppercase font-bold">{{ $penjualan->status }}</span></div>
        </div>

        <div class="space-y-2 border-b border-dashed border-[#D1D5DB] pb-3 mb-3">
            @foreach ($penjualan->detail as $d)
                <div>
                    <p class="font-semibold">{{ $d->produk?->nama }}</p>
                    <div class="flex justify-between text-[12px] text-[#4B5563]">
                        <span>{{ $d->qty }} {{ $d->produk?->satuan }} x {{ \App\Support\Rupiah::format($d->harga_jual) }}</span>
                        <span class="font-semibold text-[#181B1E]">{{ \App\Support\Rupiah::format($d->subtotal) }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="space-y-1 text-[12.5px]">
            <div class="flex justify-between"><span>Subtotal</span><span>{{ \App\Support\Rupiah::format($penjualan->subtotal) }}</span></div>
            @if ($penjualan->diskon > 0)
                <div class="flex justify-between"><span>Diskon</span><span>-{{ \App\Support\Rupiah::format($penjualan->diskon) }}</span></div>
            @endif
            <div class="flex justify-between text-[15px] font-bold pt-1 border-t border-[#E6E8EC]">
                <span>TOTAL</span>
                <span>{{ \App\Support\Rupiah::format($penjualan->total) }}</span>
            </div>
            @if ($penjualan->metode_bayar === 'tunai')
                <div class="flex justify-between text-[12px]"><span>Tunai</span><span>{{ \App\Support\Rupiah::format($penjualan->bayar) }}</span></div>
                <div class="flex justify-between text-[12px]"><span>Kembalian</span><span>{{ \App\Support\Rupiah::format($penjualan->kembalian) }}</span></div>
            @endif
        </div>

        <p class="mt-5 text-center text-[11px] text-[#6B7280]">Terima kasih telah berbelanja.</p>
    </div>

    <x-modal nama="modal-batal-trx" judul="Batalkan Transaksi Penjualan">
        <form method="POST" action="{{ route('penjualan.batalkan', $penjualan) }}" class="space-y-3.5">
            @csrf
            <p class="text-[13px] text-[#6B7280]">Stok seluruh barang pada struk <strong>{{ $penjualan->nomor_struk }}</strong> akan dikembalikan ke gudang.</p>
            <x-field label="Alasan Pembatalan" name="alasan" :required="true">
                <textarea id="alasan" name="alasan" rows="2" class="input-kontrol" required></textarea>
            </x-field>
            <div class="flex justify-end gap-2 pt-3 border-t border-[#E6E8EC]">
                <button type="button" @click="$dispatch('tutup-modal', 'modal-batal-trx')" class="btn-sekunder">Tutup</button>
                <button type="submit" class="btn-bahaya">Konfirmasi Batal</button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
