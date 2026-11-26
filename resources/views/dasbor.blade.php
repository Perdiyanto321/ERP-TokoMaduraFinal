<x-app-layout judul="Dashboard">
    {{-- Baris Kontrol Filter Periode (Day / Week / Month / Year + Rentang Tanggal) --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <p class="text-[13px] text-[#6B7280]">Ringkasan performa penjualan, stok barang, dan pengadaan toko.</p>

        <div class="flex flex-wrap items-center gap-2">
            @foreach (['hari' => 'Hari', 'minggu' => 'Minggu', 'bulan' => 'Bulan', 'tahun' => 'Tahun'] as $key => $label)
                <a href="{{ route('dasbor', ['periode' => $key]) }}"
                   class="rounded-full px-4 py-1.5 text-[12.5px] font-medium transition {{ $periode === $key ? 'bg-[#6B7280] text-white shadow-xs' : 'bg-white text-[#4B5563] border border-[#E6E8EC] hover:bg-[#F9FAFB]' }}">
                    {{ $label }}
                </a>
            @endforeach

            <div class="hidden sm:inline-flex items-center gap-2 rounded-full bg-white border border-[#E6E8EC] px-4 py-1.5 text-[12.5px] font-medium text-[#374151]">
                <svg class="w-3.5 h-3.5 text-[#6B7280]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>{{ $mulaiIni->translatedFormat('d M Y') }} - {{ $selesaiIni->translatedFormat('d M Y') }}</span>
            </div>
        </div>
    </div>

    {{-- 4 Kartu KPI Utama (Kartu Pertama Beraksen Gelap #1F2428 Sesuai Referensi) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
        {{-- KPI 1: Total Omzet (Kartu Gelap Hero) --}}
        <div class="rounded-[16px] bg-[#1F2428] p-5 text-white relative overflow-hidden">
            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/5 pointer-events-none"></div>
            <p class="text-[13px] font-medium text-[#D1D5DB]">Total Pendapatan</p>
            <p class="mt-2.5 text-[24px] font-bold tracking-tight angka">{{ \App\Support\Rupiah::format($omzetIni) }}</p>
            <div class="mt-2.5 flex items-center gap-1.5 text-[12px]">
                <span class="font-semibold {{ $persenOmzet >= 0 ? 'text-[#4ADE80]' : 'text-[#F87171]' }}">
                    {{ $persenOmzet >= 0 ? '↗' : '↘' }} {{ abs($persenOmzet) }}%
                </span>
                <span class="text-[#9CA3AF]">{{ $labelPeriode }}</span>
            </div>
        </div>

        {{-- KPI 2: Total Transaksi Selesai --}}
        <div class="rounded-[16px] bg-white border border-[#E6E8EC] p-5">
            <p class="text-[13px] font-medium text-[#6B7280]">Transaksi Penjualan</p>
            <p class="mt-2.5 text-[24px] font-bold tracking-tight text-[#181B1E] angka">{{ number_format($trxIni, 0, ',', '.') }}</p>
            <div class="mt-2.5 flex items-center gap-1.5 text-[12px]">
                <span class="font-semibold {{ $persenTrx >= 0 ? 'text-[#16A34A]' : 'text-[#DC2626]' }}">
                    {{ $persenTrx >= 0 ? '↗' : '↘' }} {{ abs($persenTrx) }}%
                </span>
                <span class="text-[#9CA3AF]">{{ $labelPeriode }}</span>
            </div>
        </div>

        {{-- KPI 3: Nilai Pembelian / Kulakan --}}
        <div class="rounded-[16px] bg-white border border-[#E6E8EC] p-5">
            <p class="text-[13px] font-medium text-[#6B7280]">Belanja Kulakan (PO)</p>
            <p class="mt-2.5 text-[24px] font-bold tracking-tight text-[#181B1E] angka">{{ \App\Support\Rupiah::format($totalBelanjaPo) }}</p>
            <div class="mt-2.5 flex items-center gap-1.5 text-[12px]">
                <a href="{{ route('po.index') }}" class="font-medium text-[#2F7D52] hover:underline">Lihat daftar PO &rarr;</a>
            </div>
        </div>

        {{-- KPI 4: Piutang Kasbon Aktif --}}
        <div class="rounded-[16px] bg-white border border-[#E6E8EC] p-5">
            <p class="text-[13px] font-medium text-[#6B7280]">Piutang Kasbon Aktif</p>
            <p class="mt-2.5 text-[24px] font-bold tracking-tight text-[#181B1E] angka">{{ \App\Support\Rupiah::format($totalPiutang) }}</p>
            <div class="mt-2.5 flex items-center gap-1.5 text-[12px]">
                <span class="font-semibold text-[#B56A1C]">{{ $jumlahMenipis }} produk</span>
                <span class="text-[#9CA3AF]">perlu restok segera</span>
            </div>
        </div>
    </div>

    {{-- Baris Tengah: Grafik Batang Omzet Bulanan (Kiri) + Kalender Pekanan & Ring Ketersediaan Stok (Kanan) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 mb-5">
        {{-- Grafik Batang Rounded (8 Kolom) --}}
        <div class="lg:col-span-8 rounded-[16px] bg-white border border-[#E6E8EC] p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-[16px] font-semibold text-[#181B1E]">Grafik Pendapatan 6 Bulan Terakhir</h2>
                    <p class="text-[12px] text-[#6B7280]">Perbandingan omzet penjualan bersih per bulan</p>
                </div>
                <a href="{{ route('laporan.penjualan') }}"
                   class="h-8 w-8 rounded-full bg-[#F3F4F6] hover:bg-[#E5E7EB] flex items-center justify-center text-[#181B1E] transition"
                   title="Buka Laporan Lengkap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 17L17 7M7 7h10v10" />
                    </svg>
                </a>
            </div>

            {{-- Kanvas Grafik Batang --}}
            <div class="flex items-end gap-4 pt-4 pb-1 h-[215px]">
                {{-- Sumbu Y (Skala Nominal) --}}
                <div class="hidden sm:flex flex-col justify-between h-[175px] text-[11px] font-medium text-[#9CA3AF] pr-2 text-right select-none">
                    <span>{{ $nilaiMaks > 0 ? number_format($nilaiMaks / 1000, 0) . 'K' : '100K' }}</span>
                    <span>{{ $nilaiMaks > 0 ? number_format(($nilaiMaks * 0.75) / 1000, 0) . 'K' : '75K' }}</span>
                    <span>{{ $nilaiMaks > 0 ? number_format(($nilaiMaks * 0.5) / 1000, 0) . 'K' : '50K' }}</span>
                    <span>{{ $nilaiMaks > 0 ? number_format(($nilaiMaks * 0.25) / 1000, 0) . 'K' : '25K' }}</span>
                    <span>0</span>
                </div>

                {{-- Batang Grafik --}}
                <div class="flex-1 grid grid-cols-6 gap-3 sm:gap-5 items-end h-[195px]">
                    @foreach ($grafikBulanan as $bar)
                        <div class="flex flex-col items-center justify-end h-full group">
                            <div class="relative w-full max-w-[44px] h-[165px] flex items-end justify-center">
                                {{-- Tooltip Nilai saat Hover --}}
                                <div class="opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity absolute -top-8 z-10 whitespace-nowrap rounded-[6px] bg-[#1F2428] px-2 py-1 text-[11px] font-medium text-white shadow">
                                    {{ \App\Support\Rupiah::format($bar['nilai']) }}
                                </div>

                                <div
                                    style="height: {{ $bar['persen_tinggi'] }}%;"
                                    class="w-full rounded-t-[10px] rounded-b-[10px] transition-all duration-300 {{ $bar['aktif'] ? 'bg-[#6B7280]' : 'bg-[#1F2428] group-hover:bg-[#374151]' }}"
                                ></div>
                            </div>
                            <span class="mt-2.5 text-[12px] font-medium {{ $bar['aktif'] ? 'text-[#181B1E] font-bold' : 'text-[#6B7280]' }}">
                                {{ $bar['label'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Kolom Kanan (4 Kolom): Kalender Pekanan + Donut Kesehatan Stok --}}
        <div class="lg:col-span-4 flex flex-col gap-4">
            {{-- Kartu Kalender Pekanan --}}
            <div class="rounded-[16px] bg-white border border-[#E6E8EC] p-5">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs text-[#9CA3AF]">&lsaquo;</span>
                    <p class="text-[14px] font-semibold text-[#181B1E]">{{ now()->translatedFormat('F Y') }}</p>
                    <span class="text-xs text-[#9CA3AF]">&rsaquo;</span>
                </div>

                <div class="grid grid-cols-5 gap-1.5 text-center">
                    @foreach ($stripKalender as $hari)
                        <div class="rounded-[12px] py-2.5 px-1 transition {{ $hari['hari_ini'] ? 'bg-[#6B7280] text-white shadow-xs' : 'text-[#4B5563] hover:bg-[#F3F4F6]' }}">
                            <p class="text-[11px] {{ $hari['hari_ini'] ? 'text-white/85' : 'text-[#9CA3AF]' }}">{{ $hari['hari'] }}</p>
                            <p class="mt-1.5 text-[14px] font-bold">{{ $hari['tanggal'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Kartu Kesiapan Stok Produk (Donut Progress Ring) --}}
            <div class="rounded-[16px] bg-white border border-[#E6E8EC] p-5 flex items-center justify-between flex-1">
                <div>
                    <p class="text-[14px] font-semibold text-[#181B1E]">Ketersediaan Stok</p>
                    <div class="mt-1.5 flex items-center gap-1.5 text-[12px]">
                        <span class="font-semibold text-[#16A34A]">↗ {{ $totalProdukAktif - $jumlahMenipis }}/{{ $totalProdukAktif }}</span>
                        <span class="text-[#9CA3AF]">produk aman</span>
                    </div>
                    <a href="{{ route('restok.index') }}" class="mt-2 inline-block text-[12px] font-medium text-[#181B1E] underline">
                        Cek saran restok
                    </a>
                </div>

                {{-- SVG Donut Ring --}}
                @php
                    $keliling = 2 * pi() * 26;
                    $offset = $keliling - ($persenStokAman / 100) * $keliling;
                @endphp
                <div class="relative h-16 w-16 shrink-0 flex items-center justify-center">
                    <svg class="h-16 w-16 -rotate-90" viewBox="0 0 64 64">
                        <circle cx="32" cy="32" r="26" stroke="#E5E7EB" stroke-width="6" fill="none" />
                        <circle cx="32" cy="32" r="26" stroke="#6B7280" stroke-width="6" fill="none"
                                stroke-linecap="round"
                                stroke-dasharray="{{ $keliling }}"
                                stroke-dashoffset="{{ $offset }}" />
                    </svg>
                    <span class="absolute text-[13px] font-bold text-[#181B1E]">{{ $persenStokAman }}%</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Bagian Bawah: Tabel Rekap Aktivitas Operasional Terbaru --}}
    <div class="rounded-[16px] bg-white border border-[#E6E8EC] p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-[16px] font-semibold text-[#181B1E]">Rekap Transaksi & Pengadaan Terbaru</h2>
                <p class="text-[12px] text-[#6B7280]">Aktivitas penjualan kasir dan pemesanan barang supplier terkini.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('po.index') }}" class="btn-sekunder text-xs py-1.5 px-3">Lihat Semua PO</a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13.5px]">
                <thead class="border-b border-[#E6E8EC] text-[12px] font-semibold text-[#6B7280]">
                    <tr>
                        <th class="pb-3 pr-4">Nomor Dokumen</th>
                        <th class="pb-3 px-4">Keterangan / Pihak</th>
                        <th class="pb-3 px-4">Tanggal</th>
                        <th class="pb-3 px-4 angka">Nominal</th>
                        <th class="pb-3 pl-4 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F0F1F4]">
                    @forelse ($poTerbaru as $po)
                        <tr class="hover:bg-[#F9FAFB] transition">
                            <td class="py-3 pr-4 font-mono text-[13px] font-semibold text-[#181B1E]">
                                <a href="{{ route('po.detail', $po) }}" class="hover:underline">{{ $po->nomor_po }}</a>
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-medium text-[#181B1E]">PO Supplier: {{ $po->supplier?->nama }}</span>
                                <span class="block text-[11px] text-[#9CA3AF] capitalize">Pembayaran {{ $po->jenis_bayar }}</span>
                            </td>
                            <td class="py-3 px-4 text-[#6B7280]">{{ $po->tanggal->format('d M Y') }}</td>
                            <td class="py-3 px-4 angka font-semibold text-[#181B1E]">{{ \App\Support\Rupiah::format($po->total) }}</td>
                            <td class="py-3 pl-4 text-right">
                                <span class="inline-block rounded-full bg-[#6B7280] px-3 py-1 text-[11.5px] font-medium text-white capitalize">
                                    {{ str_replace('_', ' ', $po->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                    @endforelse

                    @forelse ($penjualanTerbaru as $pj)
                        <tr class="hover:bg-[#F9FAFB] transition">
                            <td class="py-3 pr-4 font-mono text-[13px] font-semibold text-[#181B1E]">{{ $pj->nomor_struk }}</td>
                            <td class="py-3 px-4">
                                <span class="font-medium text-[#181B1E]">Penjualan Kasir ({{ $pj->detail->count() }} item)</span>
                                <span class="block text-[11px] text-[#9CA3AF]">Kasir: {{ $pj->kasir?->karyawan?->nama ?? $pj->kasir?->username }}</span>
                            </td>
                            <td class="py-3 px-4 text-[#6B7280]">{{ $pj->tanggal->format('d M Y H:i') }}</td>
                            <td class="py-3 px-4 angka font-semibold text-[#181B1E]">{{ \App\Support\Rupiah::format($pj->total) }}</td>
                            <td class="py-3 pl-4 text-right">
                                <span class="inline-block rounded-full bg-[#1F2428] px-3 py-1 text-[11.5px] font-medium text-white capitalize">
                                    {{ $pj->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                    @endforelse

                    @if ($poTerbaru->isEmpty() && $penjualanTerbaru->isEmpty())
                        <tr>
                            <td colspan="5" class="py-8 text-center text-[#9CA3AF]">
                                Belum ada transaksi penjualan maupun Purchase Order yang tercatat.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
