<x-app-layout judul="Kasir (POS)" :layar-penuh="true">
    <div
        x-data="{
            produkList: {{ Js::from($daftarProduk) }},
            pelangganList: {{ Js::from($daftarPelanggan) }},
            kataCari: '',
            kategoriAktif: '',
            keranjang: [],
            metodeBayar: 'tunai',
            pelangganId: '',
            diskon: 0,
            jumlahBayar: 0,
            sedangProses: false,
            pesanError: '',

            get produkTersaring() {
                const q = this.kataCari.trim().toLowerCase();
                return this.produkList.filter(p => {
                    const cocokKat = !this.kategoriAktif || String(p.kategori_id) === String(this.kategoriAktif);
                    const cocokTeks = !q || p.nama.toLowerCase().includes(q) || p.kode.toLowerCase().includes(q);
                    return cocokKat && cocokTeks;
                });
            },

            tambahDariBarcode() {
                const q = this.kataCari.trim().toLowerCase();
                if (!q) return;
                const persis = this.produkList.find(p => p.kode.toLowerCase() === q);
                if (persis) {
                    this.tambahKeKeranjang(persis);
                    this.kataCari = '';
                } else if (this.produkTersaring.length === 1) {
                    this.tambahKeKeranjang(this.produkTersaring[0]);
                    this.kataCari = '';
                }
            },

            tambahKeKeranjang(produk) {
                this.pesanError = '';
                if (produk.stok <= 0) {
                    this.pesanError = `Stok ${produk.nama} sedang kosong.`;
                    return;
                }
                const ada = this.keranjang.find(i => i.produk_id === produk.id);
                if (ada) {
                    if (ada.qty + 1 > produk.stok) {
                        this.pesanError = `Stok ${produk.nama} hanya tersedia ${produk.stok} ${produk.satuan}.`;
                        return;
                    }
                    ada.qty++;
                } else {
                    this.keranjang.push({
                        produk_id: produk.id,
                        kode: produk.kode,
                        nama: produk.nama,
                        satuan: produk.satuan,
                        harga_jual: Number(produk.harga_jual),
                        stok_maks: Number(produk.stok),
                        qty: 1
                    });
                }
                if (this.metodeBayar !== 'tunai') {
                    this.jumlahBayar = this.grandTotal;
                }
            },

            ubahQty(index, delta) {
                const item = this.keranjang[index];
                const baru = item.qty + delta;
                if (baru <= 0) {
                    this.keranjang.splice(index, 1);
                } else if (baru <= item.stok_maks) {
                    item.qty = baru;
                }
            },

            get subtotal() {
                return this.keranjang.reduce((s, i) => s + (i.harga_jual * i.qty), 0);
            },
            get grandTotal() {
                return Math.max(0, this.subtotal - (Number(this.diskon) || 0));
            },
            get kembalian() {
                if (this.metodeBayar !== 'tunai') return 0;
                return Math.max(0, (Number(this.jumlahBayar) || 0) - this.grandTotal);
            },
            get pelangganTerpilih() {
                return this.pelangganList.find(p => String(p.id) === String(this.pelangganId)) || null;
            },
            rp(n) {
                return 'Rp' + Number(n || 0).toLocaleString('id-ID');
            },

            async simpanTransaksi() {
                this.pesanError = '';
                if (this.keranjang.length === 0) {
                    this.pesanError = 'Keranjang belanja masih kosong.';
                    return;
                }
                if (this.metodeBayar === 'tunai' && Number(this.jumlahBayar) < this.grandTotal) {
                    this.pesanError = 'Uang tunai yang diterima kurang dari total tagihan.';
                    return;
                }
                if (this.metodeBayar === 'kasbon' && !this.pelangganId) {
                    this.pesanError = 'Pilih pelanggan terlebih dahulu untuk transaksi kasbon.';
                    return;
                }

                this.sedangProses = true;
                try {
                    const res = await fetch('{{ route('kasir.simpan') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                        },
                        body: JSON.stringify({
                            metode_bayar: this.metodeBayar,
                            pelanggan_id: this.pelangganId || null,
                            diskon: Number(this.diskon) || 0,
                            jumlah_bayar: this.metodeBayar === 'tunai' ? Number(this.jumlahBayar) : this.grandTotal,
                            items: this.keranjang.map(i => ({ produk_id: i.produk_id, qty: i.qty }))
                        })
                    });
                    const hasil = await res.json();
                    if (!res.ok || !hasil.sukses) {
                        this.pesanError = hasil.pesan || 'Gagal memproses transaksi.';
                        this.sedangProses = false;
                        return;
                    }
                    window.location.href = hasil.redirect_struk;
                } catch (e) {
                    this.pesanError = 'Terjadi gangguan koneksi ke server.';
                    this.sedangProses = false;
                }
            }
        }"
        class="grid grid-cols-1 lg:grid-cols-12 gap-4 h-[calc(100vh-84px)]"
    >
        {{-- Panel Kiri (7 Kolom): Pencarian Barcode & Katalog Produk --}}
        <div class="lg:col-span-7 flex flex-col rounded-[14px] bg-white border border-[#E6E8EC] p-4 overflow-hidden">
            <div class="flex flex-wrap gap-2.5 pb-3 border-b border-[#F0F1F4]">
                <input
                    type="text"
                    x-model="kataCari"
                    @keydown.enter.prevent="tambahDariBarcode()"
                    autofocus
                    placeholder="Scan barcode atau ketik nama produk lalu tekan Enter..."
                    class="input-kontrol flex-1"
                >
                <select x-model="kategoriAktif" class="input-kontrol w-44">
                    <option value="">Semua Kategori</option>
                    @foreach ($daftarKategori as $kat)
                        <option value="{{ $kat->id }}">{{ $kat->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex-1 overflow-y-auto pt-3 grid grid-cols-2 sm:grid-cols-3 gap-2.5 content-start">
                <template x-for="p in produkTersaring" :key="p.id">
                    <button
                        type="button"
                        @click="tambahKeKeranjang(p)"
                        :disabled="p.stok <= 0"
                        class="text-left rounded-[10px] border border-[#E6E8EC] p-3 transition flex flex-col justify-between cursor-pointer"
                        :class="p.stok > 0 ? 'bg-white hover:border-[#1F2428] hover:bg-[#F9FAFB]' : 'bg-[#F3F4F6] opacity-55 cursor-not-allowed'"
                    >
                        <div>
                            <div class="flex items-center justify-between text-[11px] font-mono text-[#6B7280]">
                                <span x-text="p.kode"></span>
                                <span :class="p.stok <= 5 ? 'text-[#B56A1C] font-semibold' : ''" x-text="`Stok: ${p.stok}`"></span>
                            </div>
                            <p class="mt-1 text-[13px] font-semibold text-[#181B1E] line-clamp-2" x-text="p.nama"></p>
                        </div>
                        <p class="mt-2.5 text-[14px] font-bold text-[#181B1E] angka" x-text="rp(p.harga_jual)"></p>
                    </button>
                </template>
            </div>
        </div>

        {{-- Panel Kanan (5 Kolom): Keranjang Belanja & Pembayaran --}}
        <div class="lg:col-span-5 flex flex-col rounded-[14px] bg-white border border-[#E6E8EC] p-4 overflow-hidden">
            <div class="flex items-center justify-between pb-3 border-b border-[#F0F1F4]">
                <h2 class="text-[15px] font-bold text-[#181B1E]">Keranjang Transaksi</h2>
                <button type="button" @click="keranjang = []; diskon = 0; jumlahBayar = 0" x-show="keranjang.length > 0"
                        class="text-xs font-medium text-[#B03A2E] hover:underline cursor-pointer">
                    Kosongkan
                </button>
            </div>

            {{-- Daftar Item Keranjang --}}
            <div class="flex-1 overflow-y-auto divide-y divide-[#F0F1F4]">
                <template x-if="keranjang.length === 0">
                    <div class="h-full flex items-center justify-center text-[13px] text-[#9CA3AF]">
                        Belum ada produk dipilih. Klik produk di sebelah kiri.
                    </div>
                </template>

                <template x-for="(item, idx) in keranjang" :key="item.produk_id">
                    <div class="py-2.5 flex items-center justify-between gap-2">
                        <div class="min-w-0 flex-1">
                            <p class="text-[13px] font-semibold text-[#181B1E] truncate" x-text="item.nama"></p>
                            <p class="text-[11.5px] text-[#6B7280] angka" x-text="`${rp(item.harga_jual)} / ${item.satuan}`"></p>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <button type="button" @click="ubahQty(idx, -1)" class="h-7 w-7 rounded-[6px] border border-[#E6E8EC] font-bold hover:bg-[#F3F4F6] cursor-pointer">-</button>
                            <span class="w-8 text-center text-[13px] font-semibold angka" x-text="item.qty"></span>
                            <button type="button" @click="ubahQty(idx, 1)" class="h-7 w-7 rounded-[6px] border border-[#E6E8EC] font-bold hover:bg-[#F3F4F6] cursor-pointer">+</button>
                        </div>
                        <div class="w-24 text-right text-[13px] font-bold angka" x-text="rp(item.harga_jual * item.qty)"></div>
                    </div>
                </template>
            </div>

            {{-- Panel Ringkasan & Pembayaran --}}
            <div class="pt-3 border-t border-[#E6E8EC] space-y-3 bg-[#FAFAFB] -mx-4 -mb-4 p-4">
                <template x-if="pesanError">
                    <div class="rounded-[8px] bg-red-50 border border-red-200 px-3 py-2 text-xs font-medium text-[#B03A2E]" x-text="pesanError"></div>
                </template>

                <div class="grid grid-cols-3 gap-1.5">
                    @foreach (['tunai' => 'Tunai', 'transfer' => 'Transfer/QRIS', 'kasbon' => 'Kasbon'] as $mKey => $mLabel)
                        <button
                            type="button"
                            @click="metodeBayar = '{{ $mKey }}'; if ('{{ $mKey }}' !== 'tunai') jumlahBayar = grandTotal;"
                            :class="metodeBayar === '{{ $mKey }}' ? 'bg-[#1F2428] text-white border-[#1F2428]' : 'bg-white text-[#374151] border-[#E6E8EC]'"
                            class="rounded-[8px] border py-1.5 text-[12.5px] font-semibold transition cursor-pointer"
                        >{{ $mLabel }}</button>
                    @endforeach
                </div>

                <div x-show="metodeBayar === 'kasbon'" x-cloak class="space-y-1.5">
                    <select x-model="pelangganId" class="input-kontrol text-[13px]">
                        <option value="">-- Pilih Pelanggan Kasbon --</option>
                        <template x-for="plg in pelangganList" :key="plg.id">
                            <option :value="plg.id" x-text="`${plg.nama} (Sisa limit: ${rp(plg.sisa_limit)})`"></option>
                        </template>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-2 text-[12.5px]">
                    <div>
                        <label class="text-[#6B7280]">Diskon (Rp)</label>
                        <input type="number" min="0" x-model.number="diskon" class="input-kontrol py-1 angka">
                    </div>
                    <div x-show="metodeBayar === 'tunai'">
                        <label class="text-[#6B7280]">Uang Diterima (Rp)</label>
                        <input type="number" min="0" x-model.number="jumlahBayar" class="input-kontrol py-1 angka font-semibold">
                    </div>
                </div>

                <div class="flex items-center justify-between pt-1 border-t border-[#E6E8EC]">
                    <div>
                        <p class="text-xs text-[#6B7280]">Total Tagihan</p>
                        <p class="text-[20px] font-extrabold text-[#181B1E] angka" x-text="rp(grandTotal)"></p>
                    </div>
                    <div x-show="metodeBayar === 'tunai'" class="text-right">
                        <p class="text-xs text-[#6B7280]">Kembalian</p>
                        <p class="text-[16px] font-bold text-[#16A34A] angka" x-text="rp(kembalian)"></p>
                    </div>
                </div>

                <button
                    type="button"
                    @click="simpanTransaksi()"
                    :disabled="sedangProses || keranjang.length === 0"
                    class="btn-primer w-full py-2.5 text-[14px] font-bold"
                >
                    <span x-show="!sedangProses">Selesaikan & Cetak Struk</span>
                    <span x-show="sedangProses" x-cloak>Menyimpan Transaksi...</span>
                </button>
            </div>
        </div>
    </div>
</x-app-layout>
