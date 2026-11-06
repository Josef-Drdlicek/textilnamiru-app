{{-- Produktová stránka napínaných stropů: vlevo místo pro fotku, vpravo text. --}}
<x-layouts.web
    title="Napínané stropy – dokonalý vzhled vašeho interiéru"
    description="PVC napínané stropy vytvářejí hladký, čistý povrch bez spár a škrábanců po omítce. Navrhneme je přesně pro váš prostor, pečlivě zaměříme a odborně nainstalujeme – včetně osvětlení, které strop promění v designový prvek."
>
    <section class="bg-paper">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:py-20">
            {{-- Pod lg pod sebou (obrázek nahoře), od lg dva sloupce. --}}
            <div class="grid items-start gap-10 lg:grid-cols-2 lg:gap-16">
                <div class="lg:sticky lg:top-24">
                    {{-- GALERIE: pro přidání obrázku vložit soubor do public/images/vypinany-podhled/ a doplnit do seznamu. --}}
                    @php
                        $galerie = ['01.png', '02.png', '03.png', '04.png', '05.png'];
                        $galeriePocet = count($galerie);
                    @endphp
                    {{-- Poměr podle fotek (cca 4:3), ať object-fit: cover neořeže popisky. --}}
                    <style>
                        .vypinany-foto { aspect-ratio: 756 / 575; }
                        [data-vypinany-slide] {
                            opacity: 0;
                            pointer-events: none;
                            transition: opacity 0.4s ease;
                        }
                        [data-vypinany-slide].is-active {
                            opacity: 1;
                            pointer-events: auto;
                        }
                        @media (prefers-reduced-motion: reduce) {
                            [data-vypinany-slide] { transition: none; }
                        }
                    </style>
                    <div
                        class="vypinany-foto relative overflow-hidden border border-line bg-surface-alt outline-none"
                        data-vypinany-galerie
                        tabindex="0"
                        role="region"
                        aria-roledescription="carousel"
                        aria-label="Fotografie napínaných stropů"
                    >
                        @foreach ($galerie as $soubor)
                            <figure
                                data-vypinany-slide
                                class="absolute inset-0 m-0 {{ $loop->first ? 'is-active' : '' }}"
                                aria-hidden="{{ $loop->first ? 'false' : 'true' }}"
                            >
                                <img
                                    src="{{ asset('images/vypinany-podhled/'.$soubor) }}"
                                    alt="Napínaný strop v interiéru – fotka {{ $loop->iteration }} z {{ $galeriePocet }}"
                                    width="760"
                                    height="575"
                                    @if ($loop->first)
                                        loading="eager"
                                    @else
                                        loading="lazy"
                                    @endif
                                    decoding="async"
                                    class="h-full w-full object-cover"
                                    draggable="false"
                                >
                            </figure>
                        @endforeach

                        <button
                            type="button"
                            data-vypinany-prev
                            class="absolute top-1/2 left-3 z-10 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/85 text-ink shadow-md transition hover:bg-white focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:outline-none"
                            aria-label="Předchozí obrázek"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                                <path d="M15 6 9 12l6 6"/>
                            </svg>
                        </button>
                        <button
                            type="button"
                            data-vypinany-next
                            class="absolute top-1/2 right-3 z-10 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/85 text-ink shadow-md transition hover:bg-white focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:outline-none"
                            aria-label="Další obrázek"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                                <path d="m9 6 6 6-6 6"/>
                            </svg>
                        </button>

                        <div class="absolute bottom-4 left-0 right-0 z-10 flex flex-col items-center gap-2">
                            <p class="rounded-full bg-white/85 px-2.5 py-0.5 text-xs font-medium text-ink" data-vypinany-pocitadlo aria-live="polite">1 / {{ $galeriePocet }}</p>
                            <div class="flex justify-center gap-2" role="tablist" aria-label="Výběr snímku">
                                @foreach ($galerie as $soubor)
                                    <button
                                        type="button"
                                        data-vypinany-dot
                                        class="h-2.5 w-2.5 rounded-full transition {{ $loop->first ? 'bg-ink scale-125' : 'bg-white/70 hover:bg-white' }}"
                                        aria-label="Obrázek {{ $loop->iteration }}"
                                        @if ($loop->first) aria-current="true" @endif
                                    ></button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    {{-- Galerie jen na této stránce, oddělená od úvodního slideru. --}}
                    <script>
                        (() => {
                            const root = document.querySelector('[data-vypinany-galerie]');
                            if (!root) {
                                return;
                            }

                            const slides = [...root.querySelectorAll('[data-vypinany-slide]')];
                            const dots = [...root.querySelectorAll('[data-vypinany-dot]')];
                            const prevBtn = root.querySelector('[data-vypinany-prev]');
                            const nextBtn = root.querySelector('[data-vypinany-next]');
                            const counter = root.querySelector('[data-vypinany-pocitadlo]');
                            const count = slides.length;
                            let index = 0;
                            let touchStartX = 0;
                            let touchStartY = 0;
                            let touching = false;

                            const wrap = (i) => ((i % count) + count) % count;

                            const show = (next) => {
                                index = wrap(next);
                                slides.forEach((slide, i) => {
                                    const on = i === index;
                                    slide.classList.toggle('is-active', on);
                                    slide.setAttribute('aria-hidden', on ? 'false' : 'true');
                                });
                                dots.forEach((dot, i) => {
                                    const on = i === index;
                                    dot.classList.toggle('bg-ink', on);
                                    dot.classList.toggle('scale-125', on);
                                    dot.classList.toggle('bg-white/70', !on);
                                    if (on) {
                                        dot.setAttribute('aria-current', 'true');
                                    } else {
                                        dot.removeAttribute('aria-current');
                                    }
                                });
                                if (counter) {
                                    counter.textContent = (index + 1) + ' / ' + count;
                                }
                            };

                            prevBtn?.addEventListener('click', () => show(index - 1));
                            nextBtn?.addEventListener('click', () => show(index + 1));
                            dots.forEach((dot, i) => dot.addEventListener('click', () => show(i)));

                            root.addEventListener('keydown', (event) => {
                                if (event.key === 'ArrowLeft') {
                                    event.preventDefault();
                                    show(index - 1);
                                }
                                if (event.key === 'ArrowRight') {
                                    event.preventDefault();
                                    show(index + 1);
                                }
                            });

                            root.addEventListener('touchstart', (event) => {
                                if (event.touches.length !== 1 || event.target.closest('button')) {
                                    return;
                                }
                                touching = true;
                                touchStartX = event.touches[0].clientX;
                                touchStartY = event.touches[0].clientY;
                            }, { passive: true });

                            root.addEventListener('touchend', (event) => {
                                if (!touching) {
                                    return;
                                }
                                touching = false;
                                const touch = event.changedTouches[0];
                                if (!touch) {
                                    return;
                                }
                                const dx = touch.clientX - touchStartX;
                                const dy = touch.clientY - touchStartY;
                                if (Math.abs(dx) < 40 || Math.abs(dx) < Math.abs(dy)) {
                                    return;
                                }
                                show(dx < 0 ? index + 1 : index - 1);
                            }, { passive: true });
                        })();
                    </script>
                </div>

                <div>
                    <h1 class="text-section-title">Napínané stropy – dokonalý vzhled vašeho interiéru</h1>
                    <p class="mt-3 text-lg text-muted">
                        PVC napínané stropy vytvářejí hladký, čistý povrch bez spár a škrábanců po omítce. Navrhneme je přesně pro váš prostor, pečlivě zaměříme a odborně nainstalujeme – včetně osvětlení, které strop promění v designový prvek.
                    </p>

                    <h2 class="text-section-title mt-12">Co jsou napínané stropy?</h2>
                    <div class="mt-6 space-y-4 leading-relaxed text-ink/85">
                        <p>Napínaný strop, odborně také vypínaný stropní podhled, je tenká PVC membrána upevněná do obvodových profilů po stranách místnosti. Po nahřátí se folie napne a vytvoří rovnou, hladkou plochu pod původním stropem.</p>
                        <p>Díky tomu se dá zakrýt nerovná omítka, praskliny nebo stopy po starších úpravách. A protože jde o moderní stropní podhled bez nutnosti sádrokartonu a tmelení, je montáž obvykle rychlejší a čistší než u klasických řešení.</p>
                    </div>
                    <ul class="mt-6 space-y-2 leading-relaxed text-ink/85" style="list-style-type: disc; padding-left: 1.25rem;">
                        <li>Folie je napnutá v profilech po obvodu místnosti</li>
                        <li>Pod stropem vzniká prostor pro vedení kabelů a svítidel</li>
                        <li>Povrch může být matný, lesklý nebo saténový</li>
                        <li>Strop lze doplnit bodovým či liniovým osvětlením</li>
                    </ul>

                    <h2 class="text-section-title mt-12">Hlavní výhody napínaných stropů</h2>
                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        <div class="border border-line bg-white p-5">
                            <h3 class="text-lg font-extrabold">Hladký a estetický povrch</h3>
                            <p class="mt-2 leading-relaxed text-muted">Čistá plocha bez spár a viditelných šroubů. Interiér působí klidně a upraveně.</p>
                        </div>
                        <div class="border border-line bg-white p-5">
                            <h3 class="text-lg font-extrabold">Barvy a povrchy</h3>
                            <p class="mt-2 leading-relaxed text-muted">Široký výběr odstínů a typů povrchu, které lze sladit s nábytkem, podlahou i stěnami.</p>
                        </div>
                        <div class="border border-line bg-white p-5">
                            <h3 class="text-lg font-extrabold">Integrace osvětlení</h3>
                            <p class="mt-2 leading-relaxed text-muted">Bodová i liniová svítidla a LED podsvícení zapadnou do stropu už při návrhu.</p>
                        </div>
                        <div class="border border-line bg-white p-5">
                            <h3 class="text-lg font-extrabold">Rychlá a čistá montáž</h3>
                            <p class="mt-2 leading-relaxed text-muted">Práce zpravidla probíhají s menší prašností než u klasických podhledů. Konkrétní postup závisí na prostoru.</p>
                        </div>
                        <div class="border border-line bg-white p-5">
                            <h3 class="text-lg font-extrabold">Snadná běžná údržba</h3>
                            <p class="mt-2 leading-relaxed text-muted">Povrch se obvykle udržuje jemným otíráním. Doporučený postup vám předáme při montáži.</p>
                        </div>
                        <div class="border border-line bg-white p-5">
                            <h3 class="text-lg font-extrabold">Zakrytí nerovností</h3>
                            <p class="mt-2 leading-relaxed text-muted">Původní strop zůstane skrytý, takže jeho stav nemusí být před instalací dokonalý.</p>
                        </div>
                        <div class="border border-line bg-white p-5 sm:col-span-2">
                            <h3 class="text-lg font-extrabold">Individuální přizpůsobení</h3>
                            <p class="mt-2 leading-relaxed text-muted">Každý strop vzniká na míru podle půdorysu, výšky místnosti i vašich představ.</p>
                        </div>
                    </div>

                    <h2 class="text-section-title mt-12">Varianty provedení</h2>
                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        <div class="border border-line bg-white p-5">
                            <h3 class="text-lg font-extrabold">Matné stropy</h3>
                            <p class="mt-2 leading-relaxed text-muted">Klidný vzhled podobný nátěru. Hodí se do obývacích pokojů, ložnic i kanceláří, kde je důležitá decentní elegance.</p>
                        </div>
                        <div class="border border-line bg-white p-5">
                            <h3 class="text-lg font-extrabold">Lesklé stropy</h3>
                            <p class="mt-2 leading-relaxed text-muted">Povrch s odrazivostí, který opticky otevírá prostor a zdůrazňuje světlo. Výrazné řešení pro reprezentativní interiéry.</p>
                        </div>
                        <div class="border border-line bg-white p-5">
                            <h3 class="text-lg font-extrabold">Saténové stropy</h3>
                            <p class="mt-2 leading-relaxed text-muted">Něžný odlesk mezi matem a leskem. Příjemná volba, pokud chcete hloubku povrchu bez výrazných odrazů.</p>
                        </div>
                        <div class="border border-line bg-white p-5">
                            <h3 class="text-lg font-extrabold">Podsvícené stropy</h3>
                            <p class="mt-2 leading-relaxed text-muted">Napínané stropy s LED technologií vytvářejí atmosféru a mohou sloužit i jako hlavní zdroj světla. Vhodné pro obývací prostory, ložnice i obchody.</p>
                        </div>
                    </div>
                    <p class="mt-4 text-sm text-muted">Dostupnost konkrétních odstínů a technologií vám potvrdíme při konzultaci.</p>

                    <h2 class="text-section-title mt-12">Kde lze napínané stropy využít?</h2>
                    <p class="mt-6 leading-relaxed text-ink/85">Napínaný strop se hodí všude tam, kde chcete čistý vzhled a možnost promyšleného osvětlení. Vhodnost vždy posuzujeme podle konkrétního prostoru.</p>
                    <ul class="mt-6 space-y-2 leading-relaxed text-ink/85" style="list-style-type: disc; padding-left: 1.25rem;">
                        <li>Obývací pokoje – podsvícení a nepřímé světlo pro večerní atmosféru</li>
                        <li>Ložnice – klidný povrch a jemné osvětlení</li>
                        <li>Kuchyně – čisté linie a bodová svítidla nad pracovní plochou</li>
                        <li>Koupelny – při vhodném technickém řešení, které předem probereme</li>
                        <li>Kanceláře – rovnoměrné světlo a upravený vzhled</li>
                        <li>Obchodní prostory – strop jako součást vizuální identity</li>
                        <li>Hotely a komerční interiéry – reprezentativní stropy na míru</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
</x-layouts.web>
