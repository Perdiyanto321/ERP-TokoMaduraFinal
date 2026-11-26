<x-app-layout judul="Buat Purchase Order Baru">
    @php
        $katalogJson = $daftarProduk->mapWithKeys(fn ($p) => [
            $p->id => [
                'nama' => $p->nama,
                'satuan_beli' => $p->satuan_beli,
                'isi' => $p->isi_per_satuan_beli,
                'satuan' => $p->satuan,
                'harga_beli' => (float) $p->harga_beli,
            ]
        ]);
    @endphp

    <div
        x-data="{
            katalog: {{ Js::from($katalogJson) }},
            jenisBayar: '{{ old('jenis_bayar', 'kredit') }}',
            items: {{ Js::from(old('items', $itemAwal)) }},
            pilihProduk(index) {
                const id = this.items[index].produk_id;
                if (id && this.katalog[id]) {
                    this.items[index].harga_beli = this.katalog[id].harga_beli;
                }
            },
            tambahBaris() {
                this.items.push({ produk_id: '', qty_pesan: 1, harga_beli: 0 });
            },
            hapusBaris(index) {
                if (this.items.length > 1) {
                    this.items.splice(index, 1);
                }
            },
            get grandTotal() {
                return this.items.reduce((sum, item) => sum + ((Number(item.qty_pesan) || 0) * (Number(item.harga_beli) || 0)), 0);
            },
            rp(angka) {
                return 'Rp' + Number(angka || 0).toLocaleString('id-ID');
            }
        }"
        class="rounded-[6px] border border-[var(--garis)] bg-[var(--permukaan)] p-6"
    >
        <div class="border-b border-[var(--garis)] pb-3 mb-5">
            <h2 class="text-[17px] font-semibold">Formulir Purchase Order (PO)</h2>
            <p class="text-[13px] text-[var(--teks-redup)]">Kuantitas dan harga diinput berdasarkan satuan kulakan (karton/sak/slop).</p>
        </div>

        <form method="POST" action="{{ route('po.simpan') }}" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <x-field label="Supplier" name="supplier_id" :required="true">
                    <select id="supplier_id" name="supplier_id" class="input-kontrol" required>
                        <option value="">-- Pilih Supplier --</option>
                        @foreach ($daftarSupplier as $sup)
                            <option value="{{ $sup->id }}" @selected(old('supplier_id') == $sup->id)>{{ $sup->nama }}</option>
                        @endforeach
                    </select>
                </x-field>

                <x-field label="Tanggal PO" name="tanggal" :required="true">
                    <input id="tanggal" name="tanggal" type="date" value="{{ old('tanggal', now()->toDateString()) }}" class="input-kontrol" required>
                </x-field>

                <x-field label="Metode Pembayaran" name="jenis_bayar" :required="true">
                    <select id="jenis_bayar" name="jenis_bayar" x-model="jenisBayar" class="input-kontrol" required>
                        <option value="kredit">Kredit (Tempo)</option>
                        <option value="tunai">Tunai Saat Terima</option>
                    </select>
                </x-field>

                <div x-show="jenisBayar === 'kredit'">
                    <x-field label="Tanggal Jatuh Tempo" name="jatuh_tempo" :required="true">
                        <input id="jatuh_tempo" name="jatuh_tempo" type="date" value="{{ old('jatuh_tempo', now()->addDays(14)->toDateString()) }}" class="input-kontrol">
                    </x-field>
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-[14px] font-semibold">Daftar Barang Dipesan</h3>
                    <button type="button" @click="tambahBaris()" class="btn-sekunder py-1 px-3 text-xs">+ Tambah Baris Produk</button>
                </div>

                <div class="overflow-x-auto rounded-[6px] border border-[var(--garis)]">
                    <table class="w-full text-left text-[14px]">
                        <thead class="border-b border-[var(--garis)] bg-[#F8FAFB] text-[12px] font-semibold uppercase text-[var(--teks-redup)]">
                            <tr>
                                <th class="px-3 py-2">Produk</th>
                                <th class="px-3 py-2">Satuan Beli</th>
                                <th class="px-3 py-2 w-28 angka">Qty Beli</th>
                                <th class="px-3 py-2 w-40 angka">Harga Beli / Satuan</th>
                                <th class="px-3 py-2 w-40 angka">Subtotal</th>
                                <th class="px-3 py-2 w-16"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--garis)]">
                            <template x-for="(item, index) in items" :key="index">
                                <tr>
                                    <td class="px-3 py-2">
                                        <select :name="`items[${index}][produk_id]`" x-model="item.produk_id" @change="pilihProduk(index)" class="input-kontrol" required>
                                            <option value="">-- Pilih Produk --</option>
                                            @foreach ($daftarProduk as $p)
                                                <option value="{{ $p->id }}">{{ $p->kode }} — {{ $p->nama }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-3 py-2 text-[13px] text-[var(--teks-redup)]">
                                        <span x-text="item.produk_id && katalog[item.produk_id] ? `${katalog[item.produk_id].satuan_beli} (isi ${katalog[item.produk_id].isi} ${katalog[item.produk_id].satuan})` : '-'"></span>
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number" min="1" :name="`items[${index}][qty_pesan]`" x-model.number="item.qty_pesan" class="input-kontrol angka" required>
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number" min="0" step="1" :name="`items[${index}][harga_beli]`" x-model.number="item.harga_beli" class="input-kontrol angka" required>
                                    </td>
                                    <td class="px-3 py-2 angka font-medium" x-text="rp((item.qty_pesan || 0) * (item.harga_beli || 0))"></td>
                                    <td class="px-3 py-2 text-center">
                                        <button type="button" @click="hapusBaris(index)" x-show="items.length > 1" class="text-xs text-[var(--bahaya)] hover:underline">Hapus</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot class="border-t border-[var(--garis)] bg-[#F8FAFB]">
                            <tr>
                                <td colspan="4" class="px-3 py-2.5 text-right font-semibold">Total Estimasi PO</td>
                                <td class="px-3 py-2.5 angka text-[16px] font-semibold text-[var(--primer)]" x-text="rp(grandTotal)"></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-[var(--garis)]">
                <a href="{{ route('po.index') }}" class="btn-sekunder">Batal</a>
                <button type="submit" class="btn-primer">Simpan Draf PO</button>
            </div>
        </form>
    </div>
</x-app-layout>
