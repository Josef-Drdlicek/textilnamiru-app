{{-- Krátký náhled na samostatnou stránku. Texty jsou ty samé, co u plné sekce. --}}
@php
    $eyebrow = $eyebrow ?? null;
    $text = $text ?? null;
    $cta = $cta ?? 'Zobrazit více';
@endphp
<section class="{{ $class }}">
    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:py-20">
        @if ($eyebrow)
            <p class="eyebrow">{{ $eyebrow }}</p>
        @endif
        <h2 class="text-section-title {{ $eyebrow ? 'mt-2' : '' }}">{{ $title }}</h2>
        @if ($text)
            <p class="mt-3 max-w-2xl text-lg text-muted">{{ $text }}</p>
        @endif
        <div class="mt-8">
            <a href="{{ $href }}" class="btn-primary">{{ $cta }}</a>
        </div>
    </div>
</section>
