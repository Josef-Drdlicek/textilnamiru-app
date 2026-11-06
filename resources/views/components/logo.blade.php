{{-- LOGO: jediné místo pro logo webu. Při výměně loga nahradit soubor public/images/logo.* (stejný název a přípona). --}}
@props([
    'width' => 36,
    'height' => 36,
])

<img
    src="{{ asset('images/logo.jpg') }}"
    alt="Textil na míru"
    width="{{ $width }}"
    height="{{ $height }}"
    {{ $attributes->merge(['class' => 'shrink-0 object-contain']) }}
>
