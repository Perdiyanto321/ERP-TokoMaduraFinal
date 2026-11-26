<x-app-layout judul="Daftar Produk & Harga">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h2 class="text-[18px] font-semibold">Katalog Produk Toko</h2>
            <p class="text-[13px] text-[var(--teks-redup)]">Kelola harga jual eceran, konversi satuan kulakan, dan batas stok minimum.</p>
        </div>
        <a href="{{ route('produk.tambah') }}" class="btn-primer">+ Tambah Produk</a>
    </div>

    <form method="GET" action="{{ route('produk.index') }}" class="mb-4 flex flex-wrap items-end gap-3 rounded-[6px] border border-[var(--garis)] bg-[var(--permukaan)] p-3.5">
        <div>
            <label class="block text-xs font-medium text-[var(--teks-redup)] mb-1">Cari Kode / Nama</label>
            <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Ketik kode atau nama..." class="input-kontrol w-56">
        </div>
        <div>
            <label class="block text-xs font-medium text-[var(--teks-redup)] mb-1">Kategori</label>
            <select name="kategori_id" class="input-kontrol w-48">
                <option value="">Semua Kategori</option>
                @foreach ($daftarKategori as $k)
                    <option value="{{ $k->id }}" @selected(request('kategori_id') == $k->id)>{{ $k->nama }}</option>
                @endforeach
            </select>
        </div>
        <label class="inline-flex items-center gap-2 pb-2 text-[13px] cursor-pointer">
            <input type="checkbox" name="menipis" value="1" @checked(request('menipis'))>
            <span>Hanya stok menipis (&le; min)</span>
        </label>
        <button type="submit" class="btn-primer">Terapkan</button>
        <a href="{{ route('produk.index') }}" class="btn-sekunder">Reset</a>
    </form>

    <x-tabel>
        <x-slot:head>
            <tr>
                <th class="px-4 py-2.5">Kode</th>
                <th class="px-4 py-2.5">Nama Produk</th>
                <th class="px-4 py-2.5">Kategori</th>
                <th class="px-4 py-2.5">Konversi Beli</th>
                <th class="px-4 py-2.5 angka">HPP Rata-rata</th>
                <th class="px-4 py-2.5 angka">Harga Jual</th>
                <th class="px-4 py-2.5 angka">Stok</th>
                <th class="px-4 py-2.5">Status</th>
                <th class="px-4 py-2.5 text-right">Aksi</th>
            </tr>
        </x-slot:head>

        @forelse ($daftarProduk as $p)
            @php $menipis = $p->stok <= $p->stok_minimum; @endphp
            <tr class="h-[42px] hover:bg-[var(--primer-muda)]/50">
                <td class="px-4 py-2 font-mono text-[13px]">{{ $p->kode }}</td>
                <td class="px-4 py-2 font-medium">{{ $p->nama }}</td>
                <td class="px-4 py-2 text-[13px] text-[var(--teks-redup)]">{{ $p->kategori?->nama }}</td>
                <td class="px-4 py-2 text-[13px]">1 {{ $p->satuan_beli }} = {{ $p->isi_per_satuan_beli }} {{ $p->satuan }}</td>
                <td class="px-4 py-2 angka text-[var(--teks-redup)]">{{ \App\Support\Rupiah::format($p->hpp_rata_rata) }}</td>
                <td class="px-4 py-2 angka font-medium">{{ \App\Support\Rupiah::format($p->harga_jual) }}</td>
                <td class="px-4 py-2 angka font-semibold {{ $menipis ? 'text-[var(--peringatan)]' : '' }}">
                    {{ $p->stok }} {{ $p->satuan }}
                    <span class="block text-[11px] font-normal text-[var(--teks-redup)]">Min: {{ $p->stok_minimum }}</span>
                </td>
                <td class="px-4 py-2">
                    <x-status :jenis="$p->status" :label="ucfirst($p->status)" />
                </td>
                <td class="px-4 py-2 text-right whitespace-nowrap space-x-2">
                    <a href="{{ route('stok.index', ['produk_id' => $p->id]) }}" class="text-xs text-[var(--teks-redup)] hover:text-[var(--teks)] hover:underline">Kartu Stok</a>
                    <a href="{{ route('produk.edit', $p) }}" class="text-[13px] font-medium text-[var(--primer)] hover:underline">Ubah</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="p-4 text-center text-[var(--teks-redup)]">Tidak ada produk yang cocok dengan filter.</td>
            </tr>
        @endforelse
    </x-tabel>

    <div class="mt-4">{{ $daftarProduk->links() }}</div>
</x-app-layout>
