@props(['nama', 'judul', 'lebar' => 'max-w-md'])

<div
    x-data="{ buka: false }"
    x-on:buka-modal.window="if ($event.detail === '{{ $nama }}') buka = true"
    x-on:tutup-modal.window="if ($event.detail === '{{ $nama }}') buka = false"
    x-on:keydown.escape.window="buka = false"
    x-show="buka"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
>
    <div
        @click.outside="buka = false"
        class="w-full {{ $lebar }} rounded-[6px] border border-[var(--garis)] bg-[var(--permukaan)] p-5"
        style="box-shadow: 0 10px 25px rgba(29,43,51,0.16);"
    >
        <div class="flex items-center justify-between border-b border-[var(--garis)] pb-3">
            <h3 class="text-[16px] font-semibold text-[var(--teks)]">{{ $judul }}</h3>
            <button type="button" @click="buka = false" class="text-sm text-[var(--teks-redup)] hover:text-[var(--teks)]">&times;</button>
        </div>
        <div class="mt-4">
            {{ $slot }}
        </div>
    </div>
</div>
