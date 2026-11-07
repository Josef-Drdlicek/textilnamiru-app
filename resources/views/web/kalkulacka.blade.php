{{-- Rozcestník kalkulaček podle oboru. Obrázky sdílí s hero sliderem. --}}
@php
    $calculators = [
        [
            'route' => 'web.kalkulacka.sivani',
            'title' => 'Zakázkové šití',
            'text' => 'Záclony, závěsy, polštáře, sedáky a římské rolety. Cena za ušití podle rozměrů.',
            'cta' => 'Spočítat ušití',
            'img' => 'hero',
            'alt' => 'Interiérový textil šitý na míru',
        ],
        [
            'route' => 'web.kalkulacka.stropy',
            'title' => 'Vypínané stropy',
            'text' => 'Orientační cena stropu podle rozměrů místnosti, včetně LED podsvícení a dopravy.',
            'cta' => 'Spočítat strop',
            'img' => 'hero-2',
            'alt' => 'Vypínaný stropní podhled',
        ],
        [
            'route' => 'web.inquiry.create',
            'params' => ['produkt' => 'shades'],
            'title' => 'Stínicí technika',
            'text' => 'Žaluzie, rolety a japonské stěny naceníme po zaměření. Pošlete nám rozměry oken.',
            'cta' => 'Poptat stínění',
            'badge' => 'Na poptávku',
            'img' => 'hero-3',
            'alt' => 'Stínicí technika v interiéru',
        ],
    ];
@endphp

<x-layouts.web
    title="Kalkulačka"
    description="Orientační kalkulačka zakázkového šití a vypínaných stropů. Stínicí techniku naceníme po zaměření."
>
    <section class="bg-paper">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:py-20">
            <p class="eyebrow">Orientační kalkulačka</p>
            <h1 class="text-section-title mt-2">Co chcete nacenit?</h1>
            <p class="mt-3 max-w-2xl text-lg text-muted">
                Vyberte obor, zadejte rozměry a hned vidíte orientační cenu. Přesnou nabídku připravíme zdarma po zaměření.
            </p>

            <div class="mt-10 grid grid-cols-1 gap-6 md:grid-cols-3">
                @foreach ($calculators as $calculator)
                    <a
                        href="{{ route($calculator['route'], $calculator['params'] ?? []) }}"
                        class="group flex flex-col border-2 border-line bg-white transition hover:-translate-y-0.5 hover:border-ink/40 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2"
                    >
                        <div class="aspect-[4/3] overflow-hidden">
                            <x-web.img :name="$calculator['img']" :alt="$calculator['alt']" :widths="[960, 1600]" sizes="(min-width: 768px) 33vw, 100vw" width="800" height="600" />
                        </div>
                        <div class="flex flex-1 flex-col border-t border-line px-5 py-6">
                            <h2 class="text-xl font-extrabold">{{ $calculator['title'] }}</h2>
                            @isset ($calculator['badge'])
                                <p class="mt-1.5"><span class="inline-block rounded-full bg-brand-100 px-3 py-1 text-sm font-semibold text-brand-700">{{ $calculator['badge'] }}</span></p>
                            @endisset
                            <p class="mt-2 flex-1 text-muted">{{ $calculator['text'] }}</p>
                            <p class="mt-4 font-semibold text-brand-600 transition group-hover:text-brand-700">
                                {{ $calculator['cta'] }} <span aria-hidden="true">→</span>
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
</x-layouts.web>
