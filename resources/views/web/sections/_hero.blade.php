{{-- Texty i fotky všech tří snímků na jednom místě — upravujte tady. Tlačítka jsou mimo toto pole. --}}
@php
    $slides = [
        [
            'name' => 'hero',
            'alt' => 'Obývací pokoj s vrstvenými záclonami a závěsy na míru',
            'label' => 'Fotografie interiéru se závěsy',
            'eager' => true,
            'object' => 'object-[center_20%]',
            'eyebrow' => 'Rodinná dílna · od roku 1992',
            'title' => 'Interiérový textil šitý na míru',
            'text' => 'Zaměříme, ušijeme, pověsíme. Rodinná dílna s více než 30letou tradicí vytváří záclony, závěsy a stínicí techniku pro domácnosti, hotely i firmy.',
        ],
        [
            'name' => 'hero-2',
            'alt' => 'Vypínaný stropní podhled v interiéru',
            'label' => 'Vypínaný stropní podhled v interiéru',
            'eager' => false,
            'object' => 'object-[center_top]',
            'eyebrow' => 'Nová technologie stropů',
            'title' => 'Vypínaný stropní podhled',
            'text' => 'Zaměříme, vyrobíme, namontujeme',
        ],
        [
            'name' => 'hero-3',
            'alt' => 'Stínicí technika v interiéru',
            'label' => 'Stínicí technika v interiéru',
            'eager' => false,
            'object' => 'object-[center]',
            'eyebrow' => 'Stínění pro každou situaci',
            'title' => 'Stínicí technika',
            'text' => 'Zaměříme, ušijeme, pověsíme. Rodinná dílna s více než 30letou tradicí vytváří záclony, závěsy a stínicí techniku pro domácnosti, hotely i firmy.',
        ],
    ];
@endphp

<section data-hero-root aria-labelledby="hero-nadpis" class="relative bg-surface-alt">
    {{-- Slider: posuv jako v galerii telefonu, automaticky po 6 s. --}}
    <div
        data-hero-slider
        class="relative aspect-[4/3] w-full touch-pan-y sm:aspect-[16/9] lg:absolute lg:inset-0 lg:aspect-auto lg:h-full"
        aria-roledescription="carousel"
        aria-label="Úvodní fotografie"
    >
        <div class="absolute inset-0 overflow-hidden">
            @foreach ($slides as $slide)
                <div
                    data-hero-slide
                    class="absolute inset-0 {{ $loop->first ? '' : 'pointer-events-none' }}"
                    aria-hidden="{{ $loop->first ? 'false' : 'true' }}"
                >
                    <x-web.img
                        :name="$slide['name']"
                        :alt="$slide['alt']"
                        :label="$slide['label']"
                        width="2400"
                        height="1350"
                        :eager="$slide['eager']"
                        class="{{ $slide['object'] }}"
                    />
                </div>
            @endforeach
        </div>

        <button
            type="button"
            data-hero-prev
            class="absolute bottom-6 left-4 z-10 flex h-11 w-11 items-center justify-center rounded-full bg-white/85 text-ink shadow-md transition hover:bg-white focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:outline-none sm:top-1/2 sm:bottom-auto sm:-translate-y-1/2 lg:top-auto lg:bottom-8 lg:translate-y-0"
            aria-label="Předchozí obrázek"
        >
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                <path d="M15 6 9 12l6 6"/>
            </svg>
        </button>
        <button
            type="button"
            data-hero-next
            class="absolute right-4 bottom-6 z-10 flex h-11 w-11 items-center justify-center rounded-full bg-white/85 text-ink shadow-md transition hover:bg-white focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:outline-none sm:top-1/2 sm:bottom-auto sm:-translate-y-1/2 lg:top-auto lg:bottom-8 lg:translate-y-0"
            aria-label="Další obrázek"
        >
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                <path d="m9 6 6 6-6 6"/>
            </svg>
        </button>

        <div class="absolute bottom-4 left-0 right-0 z-10 flex justify-center gap-2 lg:bottom-10" role="tablist" aria-label="Výběr snímku">
            @foreach ($slides as $slide)
                <button
                    type="button"
                    data-hero-dot
                    class="h-2.5 w-2.5 rounded-full transition {{ $loop->first ? 'bg-ink scale-125' : 'bg-white/70 hover:bg-white' }}"
                    aria-label="Obrázek {{ $loop->iteration }}"
                    @if ($loop->first) aria-current="true" @endif
                ></button>
            @endforeach
        </div>
    </div>

    <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:py-24">
        <div class="bg-white p-6 shadow-md sm:p-10 lg:max-w-xl">
            {{-- Texty ve mřížce: výška podle nejdelšího, tlačítka pod tím zůstanou. Posuv jen text, ne tlačítka. --}}
            <div data-hero-copy-track class="relative w-full overflow-hidden">
            <div class="grid">
                @foreach ($slides as $slide)
                    <div
                        data-hero-copy
                        class="col-start-1 row-start-1 w-full {{ $loop->first ? '' : 'pointer-events-none' }}"
                        aria-hidden="{{ $loop->first ? 'false' : 'true' }}"
                    >
                        <p class="eyebrow">{{ $slide['eyebrow'] }}</p>
                        @if ($loop->first)
                            <h1 id="hero-nadpis" class="text-display mt-3">
                                Interiérový textil šitý na&nbsp;míru
                            </h1>
                        @else
                            <p class="text-display mt-3">{{ $slide['title'] }}</p>
                        @endif
                        <p class="mt-4 text-lg leading-relaxed text-muted">{{ $slide['text'] }}</p>
                    </div>
                @endforeach
            </div>
            </div>

            <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('web.kalkulacka.sivani') }}" class="btn-primary">Spočítat orientační cenu</a>
                <a href="{{ route('web.inquiry.create') }}" class="btn-secondary">Nezávazná poptávka</a>
            </div>
        </div>
    </div>
</section>
