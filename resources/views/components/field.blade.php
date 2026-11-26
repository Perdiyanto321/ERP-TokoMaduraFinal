@props(['label', 'name', 'required' => false, 'hint' => null])

<div {{ $attributes->merge(['class' => 'space-y-1']) }}>
    <label for="{{ $name }}" class="block text-[13px] font-medium text-[var(--teks)]">
        {{ $label }}
        @if ($required)
            <span class="text-[var(--bahaya)]">*</span>
        @endif
    </label>

    {{ $slot }}

    @if ($hint)
        <p class="text-xs text-[var(--teks-redup)]">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="text-xs font-medium text-[var(--bahaya)]">{{ $message }}</p>
    @enderror
</div>
