{{-- Rozcestník tří oborů na úvodní stránce. Fotky sdílí s hero sliderem. --}}
@php
    $services = [
        [
            'title' => 'Zakázkové šití',
            'text' => 'Záclony, závěsy, polštáře, sedáky a římské rolety šité na míru ve vlastní dílně.',
            'href' => route('web.sortiment'),
            'cta' => 'Prohlédnout sortiment',
            'img' => 'hero',
            'alt' => 'Interiérový textil šitý na míru',
        ],
        [
            'title' => 'Vypínaný podhled',
            'text' => 'Hladký strop bez spár, s osvětlením zabudovaným už při návrhu. Zaměříme, vyrobíme, namontujeme.',
            'href' => route('web.vypinany-podhled'),
            'cta' => 'Více o podhledech',
            'img' => 'hero-2',
            'alt' => 'Vypínaný stropní podhled v interiéru',
        ],
        [
            'title' => 'Stínicí technika',
            'text' => 'Žaluzie, látkové rolety, japonské stěny a garnýže, které ladí s textilem.',
            'href' => route('web.stinici-technika'),
            'cta' => 'Více o stínění',
            'img' => 'hero-3',
            'alt' => 'Stínicí technika v interiéru',
        ],
    ];
@endphp

<section aria-labelledby="obory-nadpis" class="bg-paper">
    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:py-20">
        <p class="eyebrow">Co pro vás uděláme</p>
        <h2 id="obory-nadpis" class="text-section-title mt-2">Textil, stropy i stínění z jedné dílny</h2>

        <div class="mt-10 grid grid-cols-1 gap-6 md:grid-cols-3">
            @foreach ($services as $service)
                <article class="reveal group flex flex-col bg-white">
                    <a href="{{ $service['href'] }}" class="block overflow-hidden" tabindex="-1" aria-hidden="true">
                        <div class="aspect-[4/3] transition duration-300 group-hover:-translate-y-0.5 group-hover:shadow-md">
                            <x-web.img :name="$service['img']" :alt="$service['alt']" :widths="[960, 1600]" sizes="(min-width: 768px) 33vw, 100vw" width="800" height="600" />
                        </div>
                    </a>
                    <div class="flex flex-1 flex-col px-1 pb-6 pt-4">
                        <h3 class="text-xl font-extrabold">{{ $service['title'] }}</h3>
                        <p class="mt-2 flex-1 text-muted">{{ $service['text'] }}</p>
                        <p class="mt-3">
                            <a href="{{ $service['href'] }}" class="font-semibold text-brand-600 transition hover:text-brand-700">
                                {{ $service['cta'] }} <span aria-hidden="true">→</span>
                            </a>
                        </p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
