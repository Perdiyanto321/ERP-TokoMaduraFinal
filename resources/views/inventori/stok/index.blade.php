<x-app-layout judul="Kartu Stok & Penyesuaian">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h2 class="text-[18px] font-semibold">Kartu Stok & Penyesuaian Fisik</h2>
            <p class="text-[13px] text-[var(--teks-redup)]">Riwayat keluar-masuk barang dan pengajuan selisih stok opname.</p>
        </div>
        <button type="button" x-data @click="$dispatch('buka-modal', 'modal-penyesuaian-stok')" class="btn-primer">
            + Ajukan Penyesuaian Stok
        </button>
    </div>

    {{-- Daftar Pengajuan Penyesuaian Stok --}}
    @if ($daftarPenyesuaian->isNotEmpty())
        <div class="mb-6">
            <h3 class="text-[14px] font-semibold mb-2">Pengajuan Penyesuaian Stok (Terbaru)</h3>
            <x-tabel>
                <x-slot:head>
                    <tr>
                        <th class="px-4 py-2">Produk</th>
                        <th class="px-4 py-2 angka">Selisih</th>
                        <th class="px-4 py-2">Alasan</th>
                        <th class="px-4 py-2">Diajukan Oleh</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2 text-right">Aksi Admin</th>
                    </tr>
                </x-slot:head>
                @foreach ($daftarPenyesuaian as $adj)
                    <tr class="h-[40px]">
                        <td class="px-4 py-2 font-medium">{{ $adj->produk?->nama }}</td>
                        <td class="px-4 py-2 angka font-semibold {{ $adj->qty_selisih > 0 ? 'text-[var(--sukses)]' : 'text-[var(--bahaya)]' }}">
                            {{ $adj->qty_selisih > 0 ? "+{$adj->qty_selisih}" : $adj->qty_selisih }} {{ $adj->produk?->satuan }}
                        </td>
                        <td class="px-4 py-2 text-[13px]">{{ $adj->alasan }}</td>
                        <td class="px-4 py-2 text-[13px]">{{ $adj->pengaju?->karyawan?->nama ?? $adj->pengaju?->username }}</td>
                        <td class="px-4 py-2"><x-status :jenis="$adj->status" :label="ucfirst($adj->status)" /></td>
                        <td class="px-4 py-2 text-right whitespace-nowrap">
                            @if ($adj->status === 'menunggu' && auth()->user()->hasRole('admin'))
                                <form method="POST" action="{{ route('stok.penyesuaian.proses', $adj) }}" class="inline-flex gap-1.5">
                                    @csrf
                                    <button type="submit" name="keputusan" value="disetujui" class="btn-primer py-1 px-2.5 text-xs">Setujui</button>
                                    <button type="submit" name="keputusan" value="ditolak" class="btn-sekunder py-1 px-2.5 text-xs text-[var(--bahaya)]">Tolak</button>
                                </form>
                            @else
                                <span class="text-xs text-[var(--teks-redup)]">-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-tabel>
        </div>
    @endif

    {{-- Filter Kartu Stok --}}
    <form method="GET" action="{{ route('stok.index') }}" class="mb-4 flex flex-wrap items-end gap-3 rounded-[6px] border border-[var(--garis)] bg-[var(--permukaan)] p-3.5">
        <div>
            <label class="block text-xs font-medium text-[var(--teks-redup)] mb-1">Produk</label>
            <select name="produk_id" class="input-kontrol w-64">
                <option value="">Semua Produk</option>
                @foreach ($daftarProduk as $p)
                    <option value="{{ $p->id }}" @selected(request('produk_id') == $p->id)>{{ $p->kode }} — {{ $p->nama }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-[var(--teks-redup)] mb-1">Jenis Mutasi</label>
            <select name="jenis" class="input-kontrol w-44">
                <option value="">Semua Jenis</option>
                @foreach (['stok_awal', 'penerimaan', 'penjualan', 'retur', 'penyesuaian', 'pembatalan'] as $j)
                    <option value="{{ $j }}" @selected(request('jenis') === $j)>{{ ucfirst(str_replace('_', ' ', $j)) }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-primer">Filter</button>
        <a href="{{ route('stok.index') }}" class="btn-sekunder">Reset</a>
    </form>

    <x-tabel>
        <x-slot:head>
            <tr>
                <th class="px-4 py-2.5">Waktu</th>
                <th class="px-4 py-2.5">Produk</th>
                <th class="px-4 py-2.5">Jenis Mutasi</th>
                <th class="px-4 py-2.5 angka">Perubahan</th>
                <th class="px-4 py-2.5 angka">Saldo Akhir</th>
                <th class="px-4 py-2.5">Oleh</th>
            </tr>
        </x-slot:head>
        @forelse ($kartuStok as $ks)
            <tr class="h-[40px] hover:bg-[var(--primer-muda)]/50">
                <td class="px-4 py-2 text-[13px] text-[var(--teks-redup)] whitespace-nowrap">{{ $ks->created_at->format('d/m/Y H:i') }}</td>
                <td class="px-4 py-2 font-medium">{{ $ks->produk?->nama }}</td>
                <td class="px-4 py-2 text-[13px] capitalize">{{ str_replace('_', ' ', $ks->jenis) }}</td>
                <td class="px-4 py-2 angka font-semibold {{ $ks->qty > 0 ? 'text-[var(--sukses)]' : 'text-[var(--bahaya)]' }}">
                    {{ $ks->qty > 0 ? "+{$ks->qty}" : $ks->qty }}
                </td>
                <td class="px-4 py-2 angka font-medium">{{ $ks->stok_sesudah }} {{ $ks->produk?->satuan }}</td>
                <td class="px-4 py-2 text-[13px]">{{ $ks->user?->karyawan?->nama ?? $ks->user?->username }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="p-4 text-center text-[var(--teks-redup)]">Belum ada riwayat pergerakan stok.</td>
            </tr>
        @endforelse
    </x-tabel>

    <div class="mt-4">{{ $kartuStok->links() }}</div>

    <x-modal nama="modal-penyesuaian-stok" judul="Ajukan Penyesuaian Stok (Stok Opname)">
        <form method="POST" action="{{ route('stok.penyesuaian.ajukan') }}" class="space-y-3.5">
            @csrf
            <x-field label="Pilih Produk" name="produk_id" :required="true">
                <select id="produk_id" name="produk_id" class="input-kontrol" required>
                    @foreach ($daftarProduk as $p)
                        <option value="{{ $p->id }}">{{ $p->kode }} — {{ $p->nama }} (Stok sistem: {{ $p->stok }} {{ $p->satuan }})</option>
                    @endforeach
                </select>
            </x-field>

            <x-field label="Selisih Kuantitas (+ tambah / - kurang)" name="qty_selisih" :required="true" hint="Contoh: -2 jika hilang/rusak 2 unit, atau 5 jika lebih 5 unit">
                <input id="qty_selisih" name="qty_selisih" type="number" class="input-kontrol" required>
            </x-field>

            <x-field label="Alasan Penyesuaian" name="alasan" :required="true">
                <textarea id="alasan" name="alasan" rows="2" class="input-kontrol" placeholder="Misal: Hasil stok opname fisik rak sembako" required></textarea>
            </x-field>

            <div class="flex justify-end gap-2 pt-3 border-t border-[var(--garis)]">
                <button type="button" @click="$dispatch('tutup-modal', 'modal-penyesuaian-stok')" class="btn-sekunder">Batal</button>
                <button type="submit" class="btn-primer">Kirim Pengajuan</button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
