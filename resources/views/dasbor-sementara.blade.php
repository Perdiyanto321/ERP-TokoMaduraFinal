<x-app-layout judul="Dasbor Operasional">
    <div class="mb-5">
        <h2 class="text-[18px] font-semibold">Ringkasan Sistem (Pratinjau Tahap 1–3)</h2>
        <p class="text-[13px] text-[var(--teks-redup)]">Modul Administrasi, Inventori, dan Pengadaan (PO) sudah aktif dan siap diuji.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="rounded-[6px] border border-[var(--garis)] bg-[var(--permukaan)] p-4">
            <p class="text-xs font-medium uppercase text-[var(--teks-redup)]">Total Produk Aktif</p>
            <p class="mt-1 text-[24px] font-semibold text-[var(--teks)]">{{ $totalProduk }} item</p>
        </div>
        <div class="rounded-[6px] border border-[var(--garis)] bg-[var(--permukaan)] p-4">
            <p class="text-xs font-medium uppercase text-[var(--teks-redup)]">Stok Menipis (&le; Minimum)</p>
            <p class="mt-1 text-[24px] font-semibold text-[var(--peringatan)]">{{ $produkMenipis }} item</p>
        </div>
        <div class="rounded-[6px] border border-[var(--garis)] bg-[var(--permukaan)] p-4">
            <p class="text-xs font-medium uppercase text-[var(--teks-redup)]">Purchase Order Aktif</p>
            <p class="mt-1 text-[24px] font-semibold text-[var(--primer)]">{{ $poAktif }} dokumen</p>
        </div>
    </div>
</x-app-layout>
