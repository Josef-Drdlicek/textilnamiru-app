@props([
    'product',
    'sizes' => '100vw',
    'width' => 1200,
    'height' => 900,
    'eager' => false,
])

{{-- Fotka produktu z ceníku textilu: vlastní nebo první barvy, jinak zástupný obrázek. --}}
@if ($url = $product->mainImageUrl())
    <img
        src="{{ $url }}"
        alt="{{ $product->name }}"
        width="{{ $width }}"
        height="{{ $height }}"
        @if ($eager) loading="eager" fetchpriority="high" @else loading="lazy" decoding="async" @endif
        {{ $attributes->merge(['class' => 'h-full w-full object-cover']) }}
    >
@else
    <x-web.img :name="$product->imageName()" :alt="$product->name" :sizes="$sizes" :width="$width" :height="$height" :eager="$eager" />
@endif
