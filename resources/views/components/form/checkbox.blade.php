@props(['name', 'label', 'hint' => null])

<label for="{{ $name }}" class="flex cursor-pointer items-start gap-3 rounded-lg border border-stone-200 px-3 py-2.5 transition hover:bg-stone-50 has-checked:border-teal-600 has-checked:bg-teal-50/50">
    <input
        id="{{ $name }}"
        type="checkbox"
        {{ $attributes->merge(['class' => 'mt-0.5 h-4 w-4 rounded border-stone-300 text-teal-700 focus:ring-teal-500']) }}
    >
    <span>
        <span class="block text-sm font-medium text-stone-700">{{ $label }}</span>
        @if ($hint)
            <span class="block text-xs text-stone-400">{{ $hint }}</span>
        @endif
    </span>
</label>
