{{-- Texty, fotky i tlačítka všech tří snímků na jednom místě — upravujte tady. --}}
@php
    $slides = [
        [
            'name' => 'hero',
            'alt' => 'Obývací pokoj s vrstvenými záclonami a závěsy na míru',
            'label' => 'Fotografie interiéru se závěsy',
            'object' => 'object-[center_20%]',
            'eyebrow' => 'Rodinná dílna · od roku 1992',
            'title' => 'Interiérový textil šitý na míru',
            'text' => 'Zaměříme, ušijeme, pověsíme. Rodinná dílna s více než 30letou tradicí vytváří záclony, závěsy a stínicí techniku pro domácnosti, hotely i firmy.',
            'primary' => ['label' => 'Spočítat orientační cenu', 'href' => route('web.kalkulacka.sivani')],
            'secondary' => ['label' => 'Nezávazná poptávka', 'href' => route('web.inquiry.create')],
        ],
        [
            'name' => 'hero-2',
            'alt' => 'Vypínaný stropní podhled v interiéru',
            'label' => 'Vypínaný stropní podhled v interiéru',
            'object' => 'object-center',
            'eyebrow' => 'Nová technologie stropů',
            'title' => 'Vypínaný stropní podhled',
            'text' => 'Zaměříme, vyrobíme, namontujeme. Hladký strop bez spár, s osvětlením zabudovaným už při návrhu.',
            'primary' => ['label' => 'Spočítat cenu stropu', 'href' => route('web.kalkulacka.stropy')],
            'secondary' => ['label' => 'Více o podhledech', 'href' => route('web.vypinany-podhled')],
        ],
        [
            'name' => 'hero-3',
            'alt' => 'Stínicí technika v interiéru',
            'label' => 'Stínicí technika v interiéru',
            'object' => 'object-center',
            'eyebrow' => 'Stínění pro každou situaci',
            'title' => 'Stínicí technika',
            'text' => 'Žaluzie, látkové rolety, japonské stěny i garnýže. Zaměříme, dodáme a namontujeme, aby stínění ladilo s textilem.',
            'primary' => ['label' => 'Nezávazná poptávka', 'href' => route('web.inquiry.create', ['produkt' => 'shades'])],
            'secondary' => ['label' => 'Více o stínění', 'href' => route('web.stinici-technika')],
        ],
    ];
@endphp

<section
    data-hero
    data-slider
    aria-labelledby="hero-nadpis"
    aria-roledescription="carousel"
    aria-label="Úvodní snímky"
    class="relative bg-surface-alt"
>
    {{-- Fotky: na mobilu nad textem, od lg jako podklad celé sekce. --}}
    <div class="relative aspect-[4/3] w-full touch-pan-y sm:aspect-[16/9] lg:absolute lg:inset-0 lg:aspect-auto lg:h-full">
        <div class="absolute inset-0 overflow-hidden">
            @foreach ($slides as $slide)
                <div
                    data-slider-slide
                    class="slider-item slider-slide {{ $loop->first ? 'is-active' : 'is-after' }}"
                    @unless ($loop->first) inert aria-hidden="true" @endunless
                >
                    <x-web.img
                        :name="$slide['name']"
                        :alt="$slide['alt']"
                        :label="$slide['label']"
                        :widths="[960, 1600, 2400]"
                        width="2400"
                        height="1350"
                        :eager="$loop->first"
                        class="{{ $slide['object'] }}"
                    />
                </div>
            @endforeach
        </div>

        {{-- Ovládání: šipky vedle sebe vpravo dole, tečky uprostřed, pauza vpravo nahoře. --}}
        <div class="absolute right-4 bottom-4 z-10 flex gap-2 lg:right-8 lg:bottom-8">
            <button type="button" data-slider-prev class="slider-arrow" aria-label="Předchozí snímek">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M15 6 9 12l6 6"/></svg>
            </button>
            <button type="button" data-slider-next class="slider-arrow" aria-label="Další snímek">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="m9 6 6 6-6 6"/></svg>
            </button>
        </div>

        <div class="absolute bottom-4 left-4 z-10 flex items-center gap-1 lg:left-auto lg:right-32 lg:bottom-8">
            @foreach ($slides as $slide)
                <button type="button" data-slider-dot class="slider-dot {{ $loop->first ? 'is-active' : '' }}" aria-label="Snímek {{ $loop->iteration }}: {{ $slide['title'] }}" @if ($loop->first) aria-current="true" @endif></button>
            @endforeach
            <button type="button" data-slider-pause class="slider-dot text-ink" aria-pressed="false" aria-label="Pozastavit automatické přepínání" hidden>
                <svg data-when="playing" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7 5h4v14H7zM13 5h4v14h-4z"/></svg>
                <svg data-when="paused" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" hidden><path d="M8 5v14l11-7z"/></svg>
            </button>
        </div>
    </div>

    <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:py-24">
        {{-- Hlavní nadpis stránky stojí mimo přepínané texty, ať zůstane čtečkám dostupný u každého snímku. --}}
        <h1 id="hero-nadpis" class="sr-only">{{ $slides[0]['title'] }}</h1>
        <div class="bg-white p-6 shadow-md sm:p-10 lg:max-w-xl">
            {{-- Texty ve mřížce přes sebe: výška podle nejdelšího snímku, posouvá se jen obsah. --}}
            <div class="relative w-full overflow-hidden">
                <div class="grid">
                    @foreach ($slides as $slide)
                        <div
                            data-hero-copy
                            class="slider-item col-start-1 row-start-1 w-full {{ $loop->first ? 'is-active' : 'is-after' }}"
                            @unless ($loop->first) inert aria-hidden="true" @endunless
                        >
                            <p class="eyebrow">{{ $slide['eyebrow'] }}</p>
                            <p class="text-display mt-3" aria-hidden="{{ $loop->first ? 'true' : 'false' }}">{{ $slide['title'] }}</p>
                            <p class="mt-4 text-lg leading-relaxed text-muted">{{ $slide['text'] }}</p>

                            <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                                <a href="{{ $slide['primary']['href'] }}" class="btn-primary">{{ $slide['primary']['label'] }}</a>
                                <a href="{{ $slide['secondary']['href'] }}" class="btn-secondary">{{ $slide['secondary']['label'] }}</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
