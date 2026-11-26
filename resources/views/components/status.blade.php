@props(['jenis' => 'netral', 'label' => ''])

@php
    $warna = match ($jenis) {
        'sukses', 'aktif', 'selesai', 'disetujui' => 'var(--sukses)',
        'peringatan', 'menunggu', 'diterima_sebagian', 'dikirim', 'kredit' => 'var(--peringatan)',
        'bahaya', 'nonaktif', 'batal', 'dibatalkan', 'ditolak', 'rusak' => 'var(--bahaya)',
        default => 'var(--teks-redup)',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 text-[13px] font-medium text-[var(--teks)]']) }}>
    <span class="h-2 w-2 rounded-full shrink-0" style="background-color: {{ $warna }};"></span>
    <span>{{ $label ?: $slot }}</span>
</span>
