@props(['label', 'name', 'hint' => null])

<div {{ $attributes }}>
    <label for="{{ $name }}" class="block text-sm font-medium text-stone-700">{{ $label }}</label>

    <div class="mt-1">
        {{ $slot }}
    </div>

    @if ($hint)
        <p class="mt-1 text-xs text-stone-400">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
