{{-- Rozcestník: záclony a závěsy mají každé vlastní stránku. Texty jsou výchozí návrh, majitel si je doladí.
     Fotky: nahrajte public/images/web/zaclony.webp|jpg a zavesy.webp|jpg, do té doby se ukáže placeholder. --}}
@php
    $tiles = [
        [
            'route' => 'web.zaclony',
            'title' => 'Záclony',
            'text' => 'Lehké záclony, které pustí světlo a ochrání soukromí.',
            'cta' => 'Více o záclonách',
            'img' => 'zaclony',
            'alt' => 'Záclony šité na míru na okně',
        ],
        [
            'route' => 'web.zavesy',
            'title' => 'Závěsy',
            'text' => 'Závěsy, které zatemní, zateplí a dotvoří interiér.',
            'cta' => 'Více o závěsech',
            'img' => 'zavesy',
            'alt' => 'Závěsy šité na míru v obývacím pokoji',
        ],
    ];
@endphp

<x-layouts.web
    title="Záclony a závěsy"
    description="Záclony a závěsy šité na míru ve vlastní dílně. Zaměříme, ušijeme a pověsíme."
>
    <section class="bg-paper">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:py-20">
            <p class="eyebrow">Zakázkové šití</p>
            <h1 class="text-section-title mt-2">Záclony a závěsy</h1>
            <p class="mt-3 max-w-2xl text-lg text-muted">
                Záclony a závěsy šijeme na míru ve vlastní dílně. Vyberte, co vás zajímá.
            </p>

            {{-- Dvě menší dlaždice vždy vedle sebe, i na telefonu. --}}
            <div class="mt-10 grid max-w-3xl grid-cols-2 gap-3 sm:gap-6">
                @foreach ($tiles as $tile)
                    <a
                        href="{{ route($tile['route']) }}"
                        class="group flex flex-col border-2 border-line bg-white transition hover:-translate-y-0.5 hover:border-ink/40 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2"
                    >
                        <div class="aspect-[4/3] overflow-hidden">
                            <x-web.img :name="$tile['img']" :alt="$tile['alt']" sizes="(min-width: 768px) 384px, 50vw" width="768" height="576" />
                        </div>
                        <div class="flex flex-1 flex-col border-t border-line px-3 py-4 sm:px-5 sm:py-5">
                            <h2 class="text-lg font-extrabold sm:text-xl">{{ $tile['title'] }}</h2>
                            <p class="mt-1.5 flex-1 text-sm text-muted sm:text-base">{{ $tile['text'] }}</p>
                            <p class="mt-3 text-sm font-semibold text-brand-600 transition group-hover:text-brand-700">
                                {{ $tile['cta'] }} <span aria-hidden="true">→</span>
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-10 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('web.inquiry.create') }}" class="btn-primary">Nezávazná poptávka</a>
                <a href="{{ route('web.kalkulacka.sivani') }}" class="btn-secondary">Spočítat cenu ušití</a>
            </div>
        </div>
    </section>
</x-layouts.web>
