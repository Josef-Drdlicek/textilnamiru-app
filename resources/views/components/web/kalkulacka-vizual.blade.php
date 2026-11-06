{{-- Vizuální kopie kalkulačky šití. Bez Livewire, výpočtu a ukládání. --}}
@props(['h1'])

<section class="scroll-mt-20 border-y border-line bg-white" data-kalkulacka-vizual>
    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:py-20">
        <p class="eyebrow">Orientační kalkulačka</p>
        <h1 class="text-section-title mt-2">{{ $h1 }}</h1>
        <p class="mt-3 max-w-2xl text-lg text-muted">DOPLNIT TEXT</p>

        <div class="mt-10 grid gap-10 lg:grid-cols-[1fr_380px]">
            <div>
                <fieldset>
                    <legend class="text-lg font-extrabold">1. DOPLNIT TEXT</legend>
                    <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-5">
                        @for ($i = 0; $i < 5; $i++)
                            <button
                                type="button"
                                data-vizualni-dlazdice
                                aria-pressed="{{ $i === 0 ? 'true' : 'false' }}"
                                class="flex cursor-pointer flex-col items-center gap-2 border-2 p-4 text-center transition hover:border-ink/40 focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2 {{ $i === 0 ? 'border-brand-600 bg-brand-50' : 'border-line bg-white' }}"
                            >
                                {{-- DOPLNIT IKONU --}}
                                <span class="h-10 w-10 shrink-0 {{ $i === 0 ? 'text-brand-600' : 'text-muted' }}" aria-hidden="true"></span>
                                <span class="text-sm font-semibold">DOPLNIT NÁZEV</span>
                            </button>
                        @endfor
                    </div>
                    <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-5">
                        @for ($i = 0; $i < 5; $i++)
                            <button
                                type="button"
                                data-vizualni-dlazdice
                                aria-pressed="false"
                                class="flex cursor-pointer flex-col items-center gap-2 border-2 border-line bg-white p-4 text-center transition hover:border-ink/40 focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2"
                            >
                                {{-- DOPLNIT IKONU --}}
                                <span class="h-10 w-10 shrink-0 text-muted" aria-hidden="true"></span>
                                <span class="text-sm font-semibold">DOPLNIT NÁZEV</span>
                            </button>
                        @endfor
                    </div>
                </fieldset>

                <fieldset class="mt-8">
                    <legend class="text-lg font-extrabold">2. Rozměry</legend>
                    <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3">
                        <div>
                            <label for="vizual-pole-1" class="block text-sm font-medium">DOPLNIT NÁZEV</label>
                            <div class="relative mt-1">
                                <input
                                    id="vizual-pole-1"
                                    type="text"
                                    inputmode="decimal"
                                    class="block w-full rounded-lg border border-line bg-white px-3 py-2.5 pr-12 focus:border-brand-600 focus:ring-brand-600"
                                >
                                <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-sm text-muted">DOPLNIT</span>
                            </div>
                        </div>
                        <div>
                            <label for="vizual-pole-2" class="block text-sm font-medium">DOPLNIT NÁZEV</label>
                            <div class="relative mt-1">
                                <input
                                    id="vizual-pole-2"
                                    type="text"
                                    inputmode="decimal"
                                    class="block w-full rounded-lg border border-line bg-white px-3 py-2.5 pr-12 focus:border-brand-600 focus:ring-brand-600"
                                >
                                <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-sm text-muted">DOPLNIT</span>
                            </div>
                        </div>
                        <div>
                            <label for="vizual-pole-3" class="block text-sm font-medium">DOPLNIT NÁZEV</label>
                            <input
                                id="vizual-pole-3"
                                type="text"
                                inputmode="numeric"
                                class="mt-1 block w-full rounded-lg border border-line bg-white px-3 py-2.5 focus:border-brand-600 focus:ring-brand-600"
                            >
                        </div>
                    </div>

                    <div class="mt-5 max-w-sm">
                        <label for="vizual-select" class="block text-sm font-medium">DOPLNIT NÁZEV</label>
                        <select
                            id="vizual-select"
                            class="mt-1 block w-full rounded-lg border border-line bg-white px-3 py-2.5 focus:border-brand-600 focus:ring-brand-600"
                        >
                            <option>DOPLNIT NÁZEV</option>
                            <option>DOPLNIT NÁZEV</option>
                            <option>DOPLNIT NÁZEV</option>
                        </select>
                        <p class="mt-1 text-xs text-muted">DOPLNIT TEXT</p>
                    </div>
                </fieldset>
            </div>

            <div>
                <div class="border-2 border-ink bg-white p-7">
                    <h3 class="text-lg font-extrabold">3. DOPLNIT TEXT</h3>
                    <p class="text-price-xl mt-4">—</p>
                    <p class="text-sm text-muted">DOPLNIT TEXT</p>

                    <details class="mt-4 border-t border-line pt-3 text-sm">
                        <summary class="cursor-pointer font-semibold text-brand-600">DOPLNIT TEXT</summary>
                        <ul class="mt-2 space-y-1">
                            <li class="flex justify-between gap-3">
                                <span class="text-muted">DOPLNIT TEXT</span>
                                <span class="tabular-nums">—</span>
                            </li>
                        </ul>
                    </details>

                    <p class="mt-5 text-xs leading-relaxed text-muted">DOPLNIT TEXT</p>

                    {{-- DOPLNIT NAPOJENÍ --}}
                    <a
                        href="#"
                        data-vizualni-odkaz
                        class="btn-primary mt-6 w-full"
                    >
                        DOPLNIT NÁZEV
                    </a>
                    <p class="mt-2 text-center text-xs text-muted">DOPLNIT TEXT</p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    // Jen vizuální výběr dlaždice — bez ukládání a bez výpočtu.
    (function () {
        const koren = document.querySelector('[data-kalkulacka-vizual]');
        if (!koren) {
            return;
        }

        const dlazdice = koren.querySelectorAll('[data-vizualni-dlazdice]');
        const vybranoRamecek = ['border-brand-600', 'bg-brand-50'];
        const klidRamecek = ['border-line', 'bg-white'];

        dlazdice.forEach((prvek) => {
            prvek.addEventListener('click', () => {
                dlazdice.forEach((jina) => {
                    jina.classList.remove(...vybranoRamecek);
                    jina.classList.add(...klidRamecek);
                    jina.setAttribute('aria-pressed', 'false');
                    const ikona = jina.querySelector('[aria-hidden="true"]');
                    if (ikona) {
                        ikona.classList.remove('text-brand-600');
                        ikona.classList.add('text-muted');
                    }
                });

                prvek.classList.remove(...klidRamecek);
                prvek.classList.add(...vybranoRamecek);
                prvek.setAttribute('aria-pressed', 'true');
                const ikona = prvek.querySelector('[aria-hidden="true"]');
                if (ikona) {
                    ikona.classList.remove('text-muted');
                    ikona.classList.add('text-brand-600');
                }
            });
        });

        koren.querySelectorAll('[data-vizualni-odkaz]').forEach((odkaz) => {
            odkaz.addEventListener('click', (udalost) => {
                udalost.preventDefault();
            });
        });
    })();
</script>
