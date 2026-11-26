<x-app-layout judul="Piutang Kasbon Pelanggan">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- Daftar Tagihan Kasbon Aktif (8 Kolom) --}}
        <div class="lg:col-span-8 space-y-4">
            <div>
                <h2 class="text-[17px] font-semibold">Tagihan Kasbon Belum Lunas</h2>
                <p class="text-[13px] text-[#6B7280]">Catat pembayaran cicilan atau pelunasan piutang pelanggan.</p>
            </div>

            <x-tabel>
                <x-slot:head>
                    <tr>
                        <th class="px-4 py-2.5">Pelanggan</th>
                        <th class="px-4 py-2.5">Struk</th>
                        <th class="px-4 py-2.5 angka">Total Awal</th>
                        <th class="px-4 py-2.5 angka">Sisa Tagihan</th>
                        <th class="px-4 py-2.5 text-right">Terima Bayar</th>
                    </tr>
                </x-slot:head>
                @forelse ($piutangAktif as $pt)
                    <tr class="h-[46px]">
                        <td class="px-4 py-2 font-medium">
                            {{ $pt->pelanggan?->nama }}
                            <span class="block text-[11px] text-[#6B7280]">JT: {{ $pt->jatuh_tempo?->format('d/m/Y') ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-2 font-mono text-[12.5px]">{{ $pt->penjualan?->nomor_struk }}</td>
                        <td class="px-4 py-2 angka text-[#6B7280]">{{ \App\Support\Rupiah::format($pt->jumlah) }}</td>
                        <td class="px-4 py-2 angka font-bold text-[#B56A1C]">{{ \App\Support\Rupiah::format($pt->sisa) }}</td>
                        <td class="px-4 py-2 text-right">
                            <form method="POST" action="{{ route('kasbon.bayar', $pt) }}" class="inline-flex items-center gap-1.5">
                                @csrf
                                <input type="number" name="jumlah" max="{{ (int) $pt->sisa }}" value="{{ (int) $pt->sisa }}" class="input-kontrol py-1 px-2 text-xs w-28 angka" required>
                                <select name="metode" class="input-kontrol py-1 px-2 text-xs w-24">
                                    <option value="tunai">Tunai</option>
                                    <option value="transfer">Transfer</option>
                                </select>
                                <button type="submit" class="btn-primer py-1 px-2.5 text-xs">Bayar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-6 text-center text-[#9CA3AF]">Tidak ada piutang kasbon yang belum lunas.</td>
                    </tr>
                @endforelse
            </x-tabel>
        </div>

        {{-- Kolom Kanan (4 Kolom): Tambah Pelanggan & Daftar Limit Kasbon --}}
        <div class="lg:col-span-4 space-y-4">
            <div class="rounded-[12px] border border-[#E6E8EC] bg-white p-5">
                <h3 class="text-[15px] font-semibold border-b border-[#F0F1F4] pb-2.5 mb-3">Tambah Pelanggan Kasbon</h3>
                <form method="POST" action="{{ route('kasbon.pelanggan.simpan') }}" class="space-y-3">
                    @csrf
                    <x-field label="Nama Pelanggan / Warung" name="nama" :required="true">
                        <input id="nama" name="nama" type="text" class="input-kontrol" required>
                    </x-field>
                    <x-field label="No. HP / WhatsApp" name="no_hp">
                        <input id="no_hp" name="no_hp" type="text" class="input-kontrol">
                    </x-field>
                    <x-field label="Batas Limit Kasbon (Rp)" name="batas_kasbon" :required="true">
                        <input id="batas_kasbon" name="batas_kasbon" type="number" min="0" value="500000" class="input-kontrol angka" required>
                    </x-field>
                    <button type="submit" class="btn-primer w-full">Simpan Pelanggan</button>
                </form>
            </div>

            <div class="rounded-[12px] border border-[#E6E8EC] bg-white p-5">
                <h3 class="text-[15px] font-semibold border-b border-[#F0F1F4] pb-2.5 mb-3">Limit Kredit Pelanggan</h3>
                <div class="divide-y divide-[#F0F1F4] text-[13px]">
                    @foreach ($daftarPelanggan as $plg)
                        <div class="py-2 flex justify-between">
                            <div>
                                <p class="font-medium">{{ $plg->nama }}</p>
                                <p class="text-[11px] text-[#6B7280]">Terpakai: {{ \App\Support\Rupiah::format($plg->total_kasbon_aktif) }}</p>
                            </div>
                            <span class="font-semibold angka">{{ \App\Support\Rupiah::format($plg->batas_kasbon) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
