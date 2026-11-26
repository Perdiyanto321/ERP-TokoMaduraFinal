<x-app-layout judul="Rekap Arus Kas">
    <form method="GET" action="{{ route('kas.index') }}" class="mb-5 flex flex-wrap items-end gap-3 rounded-[12px] border border-[#E6E8EC] bg-white p-3.5">
        <div>
            <label class="block text-xs font-medium text-[#6B7280] mb-1">Dari Tanggal</label>
            <input type="date" name="dari" value="{{ $dari }}" class="input-kontrol">
        </div>
        <div>
            <label class="block text-xs font-medium text-[#6B7280] mb-1">Sampai Tanggal</label>
            <input type="date" name="sampai" value="{{ $sampai }}" class="input-kontrol">
        </div>
        <button type="submit" class="btn-primer">Tampilkan</button>
    </form>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
        <div class="rounded-[12px] border border-[#E6E8EC] bg-white p-4">
            <p class="text-xs font-medium text-[#6B7280]">Total Kas Masuk</p>
            <p class="mt-1 text-[20px] font-bold text-[#16A34A] angka">{{ \App\Support\Rupiah::format($totalMasuk) }}</p>
        </div>
        <div class="rounded-[12px] border border-[#E6E8EC] bg-white p-4">
            <p class="text-xs font-medium text-[#6B7280]">Total Kas Keluar</p>
            <p class="mt-1 text-[20px] font-bold text-[#B03A2E] angka">{{ \App\Support\Rupiah::format($totalKeluar) }}</p>
        </div>
        <div class="rounded-[12px] bg-[#1F2428] p-4 text-white">
            <p class="text-xs font-medium text-[#9CA3AF]">Selisih Arus Kas Periode</p>
            <p class="mt-1 text-[20px] font-bold angka">{{ \App\Support\Rupiah::format($saldoBersih) }}</p>
        </div>
    </div>

    <x-tabel>
        <x-slot:head>
            <tr>
                <th class="px-4 py-2.5">Waktu</th>
                <th class="px-4 py-2.5">Arah</th>
                <th class="px-4 py-2.5">Referensi</th>
                <th class="px-4 py-2.5">Metode</th>
                <th class="px-4 py-2.5 angka">Nominal</th>
                <th class="px-4 py-2.5">Petugas</th>
            </tr>
        </x-slot:head>
        @forelse ($mutasiKas as $kas)
            <tr class="h-[42px] hover:bg-[#F9FAFB]">
                <td class="px-4 py-2 text-[13px] text-[#6B7280]">{{ $kas->tanggal->format('d/m/Y H:i') }}</td>
                <td class="px-4 py-2">
                    <x-status :jenis="$kas->arah === 'masuk' ? 'sukses' : 'bahaya'" :label="$kas->arah === 'masuk' ? 'Kas Masuk' : 'Kas Keluar'" />
                </td>
                <td class="px-4 py-2 font-mono text-[12.5px]">
                    {{ $kas->penjualan?->nomor_struk ?? $kas->pembelian?->nomor_po ?? 'Operasional / Umum' }}
                </td>
                <td class="px-4 py-2 text-[13px] capitalize">{{ $kas->metode }}</td>
                <td class="px-4 py-2 angka font-semibold {{ $kas->arah === 'masuk' ? 'text-[#16A34A]' : 'text-[#B03A2E]' }}">
                    {{ $kas->arah === 'masuk' ? '+' : '-' }}{{ \App\Support\Rupiah::format($kas->jumlah) }}
                </td>
                <td class="px-4 py-2 text-[13px]">{{ $kas->user?->karyawan?->nama ?? $kas->user?->username }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="p-6 text-center text-[#9CA3AF]">Belum ada mutasi kas pada rentang tanggal ini.</td>
            </tr>
        @endforelse
    </x-tabel>

    <div class="mt-4">{{ $mutasiKas->links() }}</div>
</x-app-layout>
