<div {{ $attributes->merge(['class' => 'overflow-x-auto rounded-[6px] border border-[var(--garis)] bg-[var(--permukaan)]']) }}>
    <table class="w-full border-collapse text-left text-[14px]">
        @isset($head)
            <thead class="sticky top-0 z-10 border-b border-[var(--garis)] bg-[#F8FAFB] text-[12px] font-semibold uppercase tracking-wide text-[var(--teks-redup)]">
                {{ $head }}
            </thead>
        @endisset
        <tbody class="divide-y divide-[var(--garis)]">
            {{ $slot }}
        </tbody>
    </table>
</div>
