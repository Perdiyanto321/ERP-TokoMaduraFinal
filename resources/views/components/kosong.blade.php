@props(['judul' => 'Belum ada data', 'deskripsi' => 'Data yang Anda cari belum tersedia pada sistem.'])

<div class="rounded-[6px] border border-[var(--garis)] bg-[var(--permukaan)] px-6 py-10 text-center">
    <p class="text-[15px] font-semibold text-[var(--teks)]">{{ $judul }}</p>
    <p class="mt-1 text-[13px] text-[var(--teks-redup)]">{{ $deskripsi }}</p>
    @if (trim($slot) !== '')
        <div class="mt-4">
            {{ $slot }}
        </div>
    @endif
</div>
