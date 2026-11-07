{{-- Produktová stránka vypínaného podhledu: vlevo galerie, vpravo text. Fotky: public/images/web/podhled-{n}.webp. --}}
@php
    $gallery = [
        ['name' => 'podhled-1', 'alt' => 'Podsvícený vypínaný strop se světelným obvodem v obývacím pokoji', 'width' => 760, 'height' => 575],
        ['name' => 'podhled-2', 'alt' => 'Lesklý vypínaný strop odrážející interiér', 'width' => 756, 'height' => 575],
        ['name' => 'podhled-3', 'alt' => 'Matný vypínaný strop s bodovými svítidly', 'width' => 502, 'height' => 420],
        ['name' => 'podhled-4', 'alt' => 'Vypínaný strop s liniovým LED osvětlením', 'width' => 501, 'height' => 420],
        ['name' => 'podhled-5', 'alt' => 'Saténový vypínaný strop s bodovými světly', 'width' => 502, 'height' => 420],
    ];

    $benefits = [
        ['Hladký a estetický povrch', 'Čistá plocha bez spár a viditelných šroubů. Interiér působí klidně a upraveně.'],
        ['Barvy a povrchy', 'Široký výběr odstínů a typů povrchu, které lze sladit s nábytkem, podlahou i stěnami.'],
        ['Integrace osvětlení', 'Bodová i liniová svítidla a LED podsvícení zapadnou do stropu už při návrhu.'],
        ['Rychlá a čistá montáž', 'Práce zpravidla probíhají s menší prašností než u klasických podhledů. Konkrétní postup závisí na prostoru.'],
        ['Snadná běžná údržba', 'Povrch se obvykle udržuje jemným otíráním. Doporučený postup vám předáme při montáži.'],
        ['Zakrytí nerovností', 'Původní strop zůstane skrytý, takže jeho stav nemusí být před instalací dokonalý.'],
        ['Individuální přizpůsobení', 'Každý strop vzniká na míru podle půdorysu, výšky místnosti i vašich představ.'],
    ];

    $variants = [
        ['Matné stropy', 'Klidný vzhled podobný nátěru. Hodí se do obývacích pokojů, ložnic i kanceláří, kde je důležitá decentní elegance.'],
        ['Lesklé stropy', 'Povrch s odrazivostí, který opticky otevírá prostor a zdůrazňuje světlo. Výrazné řešení pro reprezentativní interiéry.'],
        ['Saténové stropy', 'Něžný odlesk mezi matem a leskem. Příjemná volba, pokud chcete hloubku povrchu bez výrazných odrazů.'],
        ['Podsvícené stropy', 'Napínané stropy s LED technologií vytvářejí atmosféru a mohou sloužit i jako hlavní zdroj světla. Vhodné pro obývací prostory, ložnice i obchody.'],
    ];

    $rooms = [
        'Obývací pokoje – podsvícení a nepřímé světlo pro večerní atmosféru',
        'Ložnice – klidný povrch a jemné osvětlení',
        'Kuchyně – čisté linie a bodová svítidla nad pracovní plochou',
        'Koupelny – při vhodném technickém řešení, které předem probereme',
        'Kanceláře – rovnoměrné světlo a upravený vzhled',
        'Obchodní prostory – strop jako součást vizuální identity',
        'Hotely a komerční interiéry – reprezentativní stropy na míru',
    ];
@endphp

<x-layouts.web
    title="Napínané stropy – dokonalý vzhled vašeho interiéru"
    description="PVC napínané stropy vytvářejí hladký, čistý povrch bez spár a škrábanců po omítce. Navrhneme je přesně pro váš prostor, pečlivě zaměříme a odborně nainstalujeme – včetně osvětlení, které strop promění v designový prvek."
>
    <section class="bg-paper">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:py-20">
            {{-- Pod lg pod sebou (galerie nahoře), od lg dva sloupce s přilepenou galerií. --}}
            <div class="grid items-start gap-10 lg:grid-cols-2 lg:gap-16">
                <figure class="m-0 lg:sticky lg:top-24">
                    <div
                        data-gallery
                        data-slider
                        tabindex="0"
                        role="region"
                        aria-roledescription="carousel"
                        aria-label="Fotografie napínaných stropů"
                        class="relative aspect-[4/3] overflow-hidden border border-line bg-surface-alt focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2"
                    >
                        @foreach ($gallery as $photo)
                            <div
                                data-slider-slide
                                class="slider-item slider-slide {{ $loop->first ? 'is-active' : 'is-after' }}"
                                @unless ($loop->first) inert aria-hidden="true" @endunless
                            >
                                <x-web.img
                                    :name="$photo['name']"
                                    :alt="$photo['alt']"
                                    :width="$photo['width']"
                                    :height="$photo['height']"
                                    :eager="$loop->first"
                                />
                            </div>
                        @endforeach

                        <button type="button" data-slider-prev class="slider-arrow absolute top-1/2 left-3 z-10 -translate-y-1/2" aria-label="Předchozí obrázek">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M15 6 9 12l6 6"/></svg>
                        </button>
                        <button type="button" data-slider-next class="slider-arrow absolute top-1/2 right-3 z-10 -translate-y-1/2" aria-label="Další obrázek">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="m9 6 6 6-6 6"/></svg>
                        </button>

                        <div class="absolute right-0 bottom-3 left-0 z-10 flex items-center justify-center gap-1">
                            @foreach ($gallery as $photo)
                                <button type="button" data-slider-dot class="slider-dot {{ $loop->first ? 'is-active' : '' }}" aria-label="Obrázek {{ $loop->iteration }}" @if ($loop->first) aria-current="true" @endif></button>
                            @endforeach
                        </div>
                    </div>
                    <figcaption class="mt-2 flex items-center justify-between text-sm text-muted">
                        <span>Ukázky realizací vypínaných stropů</span>
                        <span data-slider-counter aria-live="polite" class="tabular-nums">1 / {{ count($gallery) }}</span>
                    </figcaption>
                </figure>

                <div>
                    <p class="eyebrow">Nová technologie stropů</p>
                    <h1 class="text-section-title mt-2">Napínané stropy – dokonalý vzhled vašeho interiéru</h1>
                    <p class="mt-3 text-lg text-muted">
                        PVC napínané stropy vytvářejí hladký, čistý povrch bez spár a škrábanců po omítce. Navrhneme je přesně pro váš prostor, pečlivě zaměříme a odborně nainstalujeme – včetně osvětlení, které strop promění v designový prvek.
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('web.kalkulacka.stropy') }}" class="btn-primary">Spočítat orientační cenu</a>
                        <a href="{{ route('web.inquiry.create', ['produkt' => 'stretch_ceilings']) }}" class="btn-secondary">Nezávazná poptávka</a>
                    </div>

                    <h2 class="mt-12 text-2xl font-extrabold">Co jsou napínané stropy?</h2>
                    <div class="mt-4 space-y-4 leading-relaxed text-ink/85">
                        <p>Napínaný strop, odborně také vypínaný stropní podhled, je tenká PVC membrána upevněná do obvodových profilů po stranách místnosti. Po nahřátí se folie napne a vytvoří rovnou, hladkou plochu pod původním stropem.</p>
                        <p>Díky tomu se dá zakrýt nerovná omítka, praskliny nebo stopy po starších úpravách. A protože jde o moderní stropní podhled bez nutnosti sádrokartonu a tmelení, je montáž obvykle rychlejší a čistší než u klasických řešení.</p>
                    </div>
                    <ul class="mt-4 list-disc space-y-2 pl-5 leading-relaxed text-ink/85">
                        <li>Folie je napnutá v profilech po obvodu místnosti</li>
                        <li>Pod stropem vzniká prostor pro vedení kabelů a svítidel</li>
                        <li>Povrch může být matný, lesklý nebo saténový</li>
                        <li>Strop lze doplnit bodovým či liniovým osvětlením</li>
                    </ul>

                    <h2 class="mt-12 text-2xl font-extrabold">Hlavní výhody napínaných stropů</h2>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        @foreach ($benefits as [$title, $text])
                            <div class="border border-line bg-white p-5 {{ $loop->last && $loop->iteration % 2 === 1 ? 'sm:col-span-2' : '' }}">
                                <h3 class="text-lg font-extrabold">{{ $title }}</h3>
                                <p class="mt-2 leading-relaxed text-muted">{{ $text }}</p>
                            </div>
                        @endforeach
                    </div>

                    <h2 class="mt-12 text-2xl font-extrabold">Varianty provedení</h2>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        @foreach ($variants as [$title, $text])
                            <div class="border border-line bg-white p-5">
                                <h3 class="text-lg font-extrabold">{{ $title }}</h3>
                                <p class="mt-2 leading-relaxed text-muted">{{ $text }}</p>
                            </div>
                        @endforeach
                    </div>
                    <p class="mt-4 text-sm text-muted">Dostupnost konkrétních odstínů a technologií vám potvrdíme při konzultaci.</p>

                    <h2 class="mt-12 text-2xl font-extrabold">Kde lze napínané stropy využít?</h2>
                    <p class="mt-4 leading-relaxed text-ink/85">Napínaný strop se hodí všude tam, kde chcete čistý vzhled a možnost promyšleného osvětlení. Vhodnost vždy posuzujeme podle konkrétního prostoru.</p>
                    <ul class="mt-4 list-disc space-y-2 pl-5 leading-relaxed text-ink/85">
                        @foreach ($rooms as $room)
                            <li>{{ $room }}</li>
                        @endforeach
                    </ul>

                    <div class="mt-12 border-t border-line pt-8">
                        <p class="text-lg font-extrabold">Chcete vědět, kolik by stál strop u vás?</p>
                        <div class="mt-4 flex flex-col gap-3 sm:flex-row">
                            <a href="{{ route('web.kalkulacka.stropy') }}" class="btn-primary">Spočítat orientační cenu</a>
                            <a href="{{ route('web.inquiry.create', ['produkt' => 'stretch_ceilings']) }}" class="btn-secondary">Nezávazná poptávka</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.web>
