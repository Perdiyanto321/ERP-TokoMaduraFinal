<x-app-layout :judul="'Proses Retur — ' . $penjualan->nomor_struk">
    <div class="max-w-3xl rounded-[12px] border border-[#E6E8EC] bg-white p-6">
        <div class="border-b border-[#F0F1F4] pb-3 mb-4">
            <h2 class="text-[17px] font-semibold">Formulir Retur Penjualan: {{ $penjualan->nomor_struk }}</h2>
            <p class="text-[13px] text-[#6B7280]">
                Tanggal Transaksi: {{ $penjualan->tanggal->format('d/m/Y H:i') }} &bull;
                Metode Asal: <strong class="uppercase">{{ $penjualan->metode_bayar }}</strong>
            </p>
        </div>

        <form method="POST" action="{{ route('retur.simpan', $penjualan) }}" class="space-y-4">
            @csrf

            <x-tabel>
                <x-slot:head>
                    <tr>
                        <th class="px-4 py-2">Produk</th>
                        <th class="px-4 py-2 angka">Qty Beli</th>
                        <th class="px-4 py-2 angka">Sisa Bisa Retur</th>
                        <th class="px-4 py-2 w-28 angka">Qty Retur</th>
                        <th class="px-4 py-2 w-40">Kondisi Barang</th>
                    </tr>
                </x-slot:head>
                @foreach ($penjualan->detail as $d)
                    @php
                        $sudah = (int) ($sudahDireturMap[$d->id] ?? 0);
                        $sisa = $d->qty - $sudah;
                    @endphp
                    <tr>
                        <td class="px-4 py-2.5 font-medium">{{ $d->produk?->nama }}</td>
                        <td class="px-4 py-2.5 angka">{{ $d->qty }} {{ $d->produk?->satuan }}</td>
                        <td class="px-4 py-2.5 angka font-semibold">{{ $sisa }}</td>
                        <td class="px-4 py-2.5">
                            @if ($sisa > 0)
                                <input type="number" min="0" max="{{ $sisa }}" name="retur[{{ $d->id }}][qty]" value="{{ old("retur.{$d->id}.qty", 0) }}" class="input-kontrol angka">
                            @else
                                <span class="text-xs text-[#9CA3AF]">Habis diretur</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5">
                            <select name="retur[{{ $d->id }}][kondisi]" class="input-kontrol text-xs">
                                <option value="layak_jual">Layak Jual (+Stok)</option>
                                <option value="rusak">Rusak (Tidak +Stok)</option>
                            </select>
                        </td>
                    </tr>
                @endforeach
            </x-tabel>

            <x-field label="Alasan Retur Pelanggan" name="alasan" :required="true">
                <textarea id="alasan" name="alasan" rows="2" class="input-kontrol" placeholder="Misal: Kemasan penyok atau salah beli ukuran" required>{{ old('alasan') }}</textarea>
            </x-field>

            <div class="flex justify-end gap-2 pt-3 border-t border-[#F0F1F4]">
                <a href="{{ route('retur.index') }}" class="btn-sekunder">Batal</a>
                <button type="submit" class="btn-primer">Simpan Dokumen Retur</button>
            </div>
        </form>
    </div>
</x-app-layout>
