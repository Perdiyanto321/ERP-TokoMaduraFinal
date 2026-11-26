<x-app-layout :judul="$modeEdit ? 'Ubah Produk' : 'Tambah Produk Baru'">
    <div
        x-data="{
            isi: {{ (int) old('isi_per_satuan_beli', $produk->isi_per_satuan_beli ?? 1) }},
            hargaBeli: {{ (float) old('harga_beli', $produk->harga_beli ?? 0) }},
            hargaJual: {{ (float) old('harga_jual', $produk->harga_jual ?? 0) }},
            get hppEstimasi() {
                const pembagi = this.isi > 0 ? this.isi : 1;
                return Math.round(this.hargaBeli / pembagi);
            },
            get marginEstimasi() {
                return this.hargaJual - this.hppEstimasi;
            },
            rp(angka) {
                return 'Rp' + Number(angka || 0).toLocaleString('id-ID');
            }
        }"
        class="max-w-3xl rounded-[6px] border border-[var(--garis)] bg-[var(--permukaan)] p-6"
    >
        <div class="border-b border-[var(--garis)] pb-3 mb-5">
            <h2 class="text-[17px] font-semibold">{{ $modeEdit ? "Ubah Produk: {$produk->nama}" : 'Tambah Produk Baru' }}</h2>
        </div>

        <form method="POST" action="{{ $modeEdit ? route('produk.perbarui', $produk) : route('produk.simpan') }}" class="space-y-4">
            @csrf
            @if ($modeEdit)
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-field label="Kode Produk / Barcode" name="kode" :required="true">
                    <input id="kode" name="kode" type="text" value="{{ old('kode', $produk->kode) }}" class="input-kontrol font-mono uppercase" required>
                </x-field>
                <div class="md:col-span-2">
                    <x-field label="Nama Produk" name="nama" :required="true">
                        <input id="nama" name="nama" type="text" value="{{ old('nama', $produk->nama) }}" class="input-kontrol" required>
                    </x-field>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-field label="Kategori" name="kategori_id" :required="true">
                    <select id="kategori_id" name="kategori_id" class="input-kontrol" required>
                        @foreach ($daftarKategori as $k)
                            <option value="{{ $k->id }}" @selected(old('kategori_id', $produk->kategori_id) == $k->id)>{{ $k->nama }}</option>
                        @endforeach
                    </select>
                </x-field>
                <x-field label="Status Produk" name="status" :required="true">
                    <select id="status" name="status" class="input-kontrol" required>
                        <option value="aktif" @selected(old('status', $produk->status) === 'aktif')>Aktif (Tampil di Kasir)</option>
                        <option value="nonaktif" @selected(old('status', $produk->status) === 'nonaktif')>Nonaktif</option>
                    </select>
                </x-field>
            </div>

            <div class="rounded-[6px] border border-[var(--garis)] bg-[#F8FAFB] p-4 space-y-4">
                <p class="text-[13px] font-semibold text-[var(--primer)]">Konversi Satuan Kulakan & Kalkulator Harga</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-field label="Satuan Jual (Eceran)" name="satuan" :required="true" hint="Contoh: bungkus, botol, kg">
                        <input id="satuan" name="satuan" type="text" value="{{ old('satuan', $produk->satuan) }}" class="input-kontrol" required>
                    </x-field>
                    <x-field label="Satuan Beli (Kulakan)" name="satuan_beli" :required="true" hint="Contoh: karton, sak, slop">
                        <input id="satuan_beli" name="satuan_beli" type="text" value="{{ old('satuan_beli', $produk->satuan_beli) }}" class="input-kontrol" required>
                    </x-field>
                    <x-field label="Isi per Satuan Beli" name="isi_per_satuan_beli" :required="true" hint="Berapa satuan eceran per 1 satuan beli">
                        <input id="isi_per_satuan_beli" name="isi_per_satuan_beli" type="number" min="1" x-model.number="isi" class="input-kontrol" required>
                    </x-field>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-field label="Harga Beli per Satuan Beli (Rp)" name="harga_beli" :required="true">
                        <input id="harga_beli" name="harga_beli" type="number" step="1" min="0" x-model.number="hargaBeli" class="input-kontrol" required>
                    </x-field>
                    <x-field label="Harga Jual Eceran per Satuan (Rp)" name="harga_jual" :required="true">
                        <input id="harga_jual" name="harga_jual" type="number" step="1" min="0" x-model.number="hargaJual" class="input-kontrol" required>
                    </x-field>
                </div>

                <div class="flex flex-wrap gap-6 border-t border-[var(--garis)] pt-3 text-[13px]">
                    <div>
                        <span class="text-[var(--teks-redup)]">Estimasi HPP per Satuan Eceran:</span>
                        <span class="font-semibold text-[var(--teks)]" x-text="rp(hppEstimasi)"></span>
                    </div>
                    <div>
                        <span class="text-[var(--teks-redup)]">Estimasi Laba Kotor per Satuan:</span>
                        <span class="font-semibold" :class="marginEstimasi >= 0 ? 'text-[var(--sukses)]' : 'text-[var(--bahaya)]'" x-text="rp(marginEstimasi)"></span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @unless ($modeEdit)
                    <x-field label="Stok Awal (Satuan Eceran)" name="stok_awal" hint="Tercatat otomatis di kartu stok">
                        <input id="stok_awal" name="stok_awal" type="number" min="0" value="{{ old('stok_awal', 0) }}" class="input-kontrol">
                    </x-field>
                @else
                    <div class="space-y-1">
                        <span class="block text-[13px] font-medium text-[var(--teks)]">Stok Saat Ini</span>
                        <p class="py-2 text-[14px] font-semibold">{{ $produk->stok }} {{ $produk->satuan }} <span class="text-xs font-normal text-[var(--teks-redup)]">(ubah lewat Kartu Stok)</span></p>
                    </div>
                @endunless

                <x-field label="Batas Stok Minimum" name="stok_minimum" :required="true">
                    <input id="stok_minimum" name="stok_minimum" type="number" min="0" value="{{ old('stok_minimum', $produk->stok_minimum) }}" class="input-kontrol" required>
                </x-field>

                <x-field label="Stok Target (Saran Restok)" name="stok_target" :required="true">
                    <input id="stok_target" name="stok_target" type="number" min="0" value="{{ old('stok_target', $produk->stok_target) }}" class="input-kontrol" required>
                </x-field>
            </div>

            <div class="flex justify-end gap-2 pt-4 border-t border-[var(--garis)]">
                <a href="{{ route('produk.index') }}" class="btn-sekunder">Batal</a>
                <button type="submit" class="btn-primer">{{ $modeEdit ? 'Simpan Perubahan' : 'Simpan Produk' }}</button>
            </div>
        </form>
    </div>
</x-app-layout>
