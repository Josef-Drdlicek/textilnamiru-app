@props(['type'])

@php use App\Enums\ItemType; @endphp

<svg {{ $attributes }} viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($type)
        @case(ItemType::Curtain)
            {{-- Záclona: lehká látka s řasením --}}
            <line x1="6" y1="8" x2="42" y2="8" />
            <path d="M10 8v30c2-2 4-2 6 0V8" opacity="0.5" />
            <path d="M18 8v32c2-2 4-2 6 0V8" />
            <path d="M26 8v30c2-2 4-2 6 0V8" opacity="0.5" />
            <path d="M34 8v32c2-2 4-2 6 0V8" />
            @break

        @case(ItemType::Drape)
            {{-- Závěs: těžká látka stažená úvazem --}}
            <line x1="6" y1="8" x2="42" y2="8" />
            <path d="M14 8c0 10-2 14-4 18 4 6 4 10 4 14" />
            <path d="M24 8c0 8-4 12-8 18 6 6 6 10 6 14" />
            <path d="M34 8c0 10 2 14 4 18-4 6-4 10-4 14" />
            <path d="M12 26h12" opacity="0.5" />
            @break

        @case(ItemType::Pillow)
            {{-- Polštář --}}
            <path d="M8 12c10-4 22-4 32 0 -4 8-4 16 0 24-10 4-22 4-32 0 4-8 4-16 0-24z" />
            <circle cx="24" cy="24" r="1.5" fill="currentColor" stroke="none" />
            @break

        @case(ItemType::Cushion)
            {{-- Sedák: hranatý s výškou --}}
            <rect x="8" y="18" width="32" height="14" rx="4" />
            <path d="M8 25h32" opacity="0.5" />
            <path d="M14 32v4M34 32v4" />
            @break

        @case(ItemType::Blind)
            {{-- Římská roleta: skládané pruhy --}}
            <rect x="8" y="6" width="32" height="6" rx="1" />
            <path d="M10 18h28M10 24h28M12 30h24" />
            <path d="M14 36h20" />
            <circle cx="24" cy="41" r="1.5" fill="currentColor" stroke="none" />
            @break

        @case(ItemType::StretchCeiling)
            {{-- Vypínaný strop: napnutá plocha v perspektivě se světlem --}}
            <path d="M6 14h36l-6 10H12z" />
            <path d="M12 24v14M36 24v14" opacity="0.5" />
            <circle cx="24" cy="19" r="1.5" fill="currentColor" stroke="none" />
            @break
    @endswitch
</svg>
