@if (session('sukses') || session('galat'))
    <div
        x-data="{ tampil: true }"
        x-init="setTimeout(() => tampil = false, 4500)"
        x-show="tampil"
        x-transition
        role="status"
        aria-live="polite"
        class="fixed bottom-4 right-4 z-50 flex items-center gap-3 rounded-[6px] border px-4 py-3 text-sm font-medium bg-[var(--permukaan)]"
        style="border-color: {{ session('galat') ? 'var(--bahaya)' : 'var(--sukses)' }}; box-shadow: 0 4px 12px rgba(29,43,51,0.12);"
    >
        <span class="h-2.5 w-2.5 rounded-full shrink-0" style="background-color: {{ session('galat') ? 'var(--bahaya)' : 'var(--sukses)' }};"></span>
        <span>{{ session('galat') ?? session('sukses') }}</span>
        <button type="button" @click="tampil = false" class="ml-2 text-xs text-[var(--teks-redup)] hover:text-[var(--teks)]">Tutup</button>
    </div>
@endif
