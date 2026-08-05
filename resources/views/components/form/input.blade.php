@props(['name', 'unit' => null])

<div class="relative">
    <input
        id="{{ $name }}"
        name="{{ $name }}"
        {{ $attributes->merge(['class' => 'block w-full rounded-lg border border-stone-300 px-3 py-2 shadow-sm focus:border-teal-500 focus:ring-teal-500'.($unit ? ' pr-12' : '')]) }}
    >
    @if ($unit)
        <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-sm text-stone-400">{{ $unit }}</span>
    @endif
</div>
