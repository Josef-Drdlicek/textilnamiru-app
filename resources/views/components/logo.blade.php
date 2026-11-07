{{-- Emblém loga: PNG s průhledným pozadím ve dvou hustotách (public/images/logo-72.png, logo-144.png).
     Název firmy nese okolní odkaz, obrázek je dekorativní. --}}
@props(['size' => 36])

<img
    src="{{ asset('images/logo-72.png') }}"
    srcset="{{ asset('images/logo-72.png') }} 1x, {{ asset('images/logo-144.png') }} 2x"
    alt=""
    width="{{ $size }}"
    height="{{ $size }}"
    decoding="async"
    {{ $attributes->merge(['class' => 'shrink-0']) }}
>
