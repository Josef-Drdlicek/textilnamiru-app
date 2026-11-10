@props([
    'value',            // hodnota radia
    'model',            // Livewire vlastnost, např. "type"
    'selected' => false,
    'label',
])

{{-- Dlaždice výběru ve veřejné kalkulačce; obsah nad popiskem (ikona/fotka) jde do slotu. --}}
<label
    {{ $attributes->class([
        'flex cursor-pointer flex-col items-center gap-2 border-2 text-center transition hover:border-ink/40 has-focus-visible:ring-2 has-focus-visible:ring-brand-600 has-focus-visible:ring-offset-2',
        'border-brand-600 bg-brand-50' => $selected,
        'border-line bg-white' => ! $selected,
    ]) }}
>
    <input
        type="radio"
        name="estimator-{{ $model }}"
        value="{{ $value }}"
        wire:model.live="{{ $model }}"
        class="sr-only"
    >
    {{ $slot }}
    <span class="text-sm font-semibold">{{ $label }}</span>
</label>
