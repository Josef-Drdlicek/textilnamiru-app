@props([
    'name',          // základ názvu souboru v public/images/web/ (bez přípony a šířky)
    'alt',
    'width',
    'height',
    'eager' => false,
    'label' => null, // popisek placeholderu; default = alt
    'widths' => [],  // dostupné šířky responzivních variant, např. [960, 1600, 2400] → {name}-{w}.webp
    'sizes' => '100vw',
])

@php
    $file = fn (string $suffix) => file_exists(public_path("images/web/{$name}{$suffix}")) ? asset("images/web/{$name}{$suffix}") : null;

    // Responzivní varianty: {name}-{w}.webp (+ JPG fallback {name}-{w}.jpg u největší dostupné),
    // jinak jednoduchý pár {name}.webp / {name}.jpg.
    $variants = collect($widths)
        ->map(fn (int $w) => ['w' => $w, 'webp' => $file("-{$w}.webp"), 'jpg' => $file("-{$w}.jpg")])
        ->filter(fn (array $v) => $v['webp'] !== null)
        ->values();

    $srcset = $variants->map(fn (array $v) => "{$v['webp']} {$v['w']}w")->implode(', ');
    $webp = $variants->isNotEmpty() ? null : $file('.webp');
    $jpg = $variants->isNotEmpty()
        ? ($variants->firstWhere('jpg', '!=', null)['jpg'] ?? null)
        : $file('.jpg');
    $hasImage = $srcset !== '' || $webp || $jpg;
    $patternId = 'weave-'.$name;
@endphp

@if ($hasImage)
    <picture>
        @if ($srcset !== '')
            <source srcset="{{ $srcset }}" sizes="{{ $sizes }}" type="image/webp">
        @elseif ($webp && $jpg)
            <source srcset="{{ $webp }}" type="image/webp">
        @endif
        <img
            src="{{ $jpg ?? $webp ?? $variants->last()['webp'] }}"
            alt="{{ $alt }}"
            width="{{ $width }}"
            height="{{ $height }}"
            @if ($eager) loading="eager" fetchpriority="high" @else loading="lazy" decoding="async" @endif
            {{ $attributes->merge(['class' => 'h-full w-full object-cover']) }}
        >
    </picture>
@else
    {{-- Placeholder, dokud nejsou fotky (viz docs/IMAGE-PROMPTS.md) — stačí
         nakopírovat soubor images/web/{{ $name }}.webp a vykreslí se fotka. --}}
    <div
        {{ $attributes->merge(['class' => 'relative flex h-full w-full items-center justify-center overflow-hidden bg-surface-alt']) }}
        style="aspect-ratio: {{ $width }} / {{ $height }};"
        role="img"
        aria-label="{{ $alt }}"
    >
        <svg class="absolute inset-0 h-full w-full" aria-hidden="true">
            <defs>
                <pattern id="{{ $patternId }}" width="14" height="14" patternUnits="userSpaceOnUse">
                    <rect width="14" height="14" fill="#f2efe9"/>
                    <path d="M0 3.5h14M0 10.5h14" stroke="#e7e2da" stroke-width="2"/>
                    <path d="M3.5 0v14M10.5 0v14" stroke="#f6e3d7" stroke-width="2"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#{{ $patternId }})"/>
        </svg>
        <span class="relative rounded-full bg-white/80 px-4 py-1.5 text-sm font-medium text-muted">
            {{ $label ?? $alt }}
        </span>
    </div>
@endif
