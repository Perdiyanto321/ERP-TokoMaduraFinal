@props(['judul' => 'ERP Toko Madura', 'layarPenuh' => false])

<x-layouts.app :judul="$judul" :layar-penuh="$layarPenuh">
    {{ $slot }}
</x-layouts.app>
