@props(['name'])

<select
    id="{{ $name }}"
    name="{{ $name }}"
    {{ $attributes->merge(['class' => 'block w-full rounded-lg border border-stone-300 px-3 py-2 shadow-sm focus:border-teal-500 focus:ring-teal-500']) }}
>
    {{ $slot }}
</select>
