{{-- Výpočet ceny rolety: kroky vlevo (rozměry, látka; doplňky přibudou), shrnutí a cena vpravo. --}}
<div class="grid gap-8 lg:grid-cols-[1fr_380px]">
    <ol class="space-y-3">
        @foreach ($this::STEPS as $key)
            @php
                $open = $step === $key;
                $value = $this->stepValue($key);
            @endphp
            <li wire:key="shade-step-{{ $key }}" class="border-2 bg-white {{ $open ? 'border-ink' : 'border-line' }}">
                <button
                    type="button"
                    wire:click="openStep('{{ $key }}')"
                    class="flex w-full items-center gap-4 px-5 py-4 text-left"
                    aria-expanded="{{ $open ? 'true' : 'false' }}"
                    aria-controls="roleta-krok-{{ $key }}"
                >
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sm font-bold {{ $value ? 'bg-ink text-white' : 'bg-surface-alt text-muted' }}">{{ $loop->iteration }}</span>
                    <span class="flex-1">
                        <span class="block font-extrabold">{{ $this->stepTitle($key) }}</span>
                        <span class="block text-sm {{ $value ? 'text-ink' : 'text-muted' }}">{{ $value ?? 'Nevybráno' }}</span>
                    </span>
                    @unless ($open)
                        <span class="text-sm font-semibold text-brand-600">Upravit</span>
                    @endunless
                </button>

                @if ($open)
                    <div id="roleta-krok-{{ $key }}" class="border-t border-line px-5 pt-5 pb-6">
                        @switch($key)
                            @case('dimensions')
                                <p class="text-sm text-muted">
                                    Zadejte rozměr rolety – šířku a výšku plochy, kterou má roleta zakrýt.
                                    Cenu počítáme podle ceníku po {{ $variant->step }} cm, rozměr mezi dvěma hodnotami zaokrouhlíme nahoru.
                                </p>
                                <div class="mt-4 grid max-w-md grid-cols-2 gap-4">
                                    <div>
                                        <label for="shade-width" class="block text-sm font-medium">Šířka <span class="text-muted">({{ $variant->min_width }}–{{ $variant->max_width }} cm)</span></label>
                                        <div class="relative mt-1">
                                            <input id="shade-width" type="number" inputmode="decimal" min="{{ $variant->min_width }}" max="{{ $variant->max_width }}" wire:model.live.debounce.400ms="width"
                                                class="block w-full rounded-lg border border-line bg-white px-3 py-2.5 pr-12 focus:border-brand-600 focus:ring-brand-600"
                                                @error('width') aria-invalid="true" aria-describedby="shade-width-error" @enderror>
                                            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-sm text-muted">cm</span>
                                        </div>
                                        @error('width') <p id="shade-width-error" class="mt-1 text-sm text-error" role="alert">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label for="shade-height" class="block text-sm font-medium">Výška <span class="text-muted">({{ $variant->min_height }}–{{ $variant->max_height }} cm)</span></label>
                                        <div class="relative mt-1">
                                            <input id="shade-height" type="number" inputmode="decimal" min="{{ $variant->min_height }}" max="{{ $variant->max_height }}" wire:model.live.debounce.400ms="height"
                                                class="block w-full rounded-lg border border-line bg-white px-3 py-2.5 pr-12 focus:border-brand-600 focus:ring-brand-600"
                                                @error('height') aria-invalid="true" aria-describedby="shade-height-error" @enderror>
                                            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-sm text-muted">cm</span>
                                        </div>
                                        @error('height') <p id="shade-height-error" class="mt-1 text-sm text-error" role="alert">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                                <p class="mt-3 text-xs text-muted">Větší rozměr vám vyrobíme po domluvě – pošlete nám poptávku.</p>
                                @break

                            @case('fabric')
                                <p class="text-sm text-muted">
                                    Vyberte látku. Látky jsou rozdělené do cenových skupin A–D, A je nejlevnější.
                                    Fotka nahoře se přepne na zvolenou látku.
                                </p>
                                @forelse ($this->fabrics->groupBy(fn ($fabric) => $fabric->price_category->value) as $group => $options)
                                    <h4 class="mt-5 text-sm font-extrabold">Cenová skupina {{ $group }}</h4>
                                    <div class="mt-2 grid grid-cols-3 gap-3 sm:grid-cols-5">
                                        @foreach ($options as $option)
                                            @php $fits = $option->fitsWidth(is_numeric($width) ? (float) $width : null); @endphp
                                            <x-web.estimator-tile model="fabricId" :value="$option->id" :selected="(int) $fabricId === $option->id" :label="$option->name" :class="$fits ? 'p-2' : 'p-2 opacity-50'" wire:key="fabric-{{ $option->id }}">
                                                @if ($optionImage = $option->imageUrl())
                                                    <img src="{{ $optionImage }}" alt="" loading="lazy" class="aspect-square w-full object-cover">
                                                @endif
                                                @unless ($fits)
                                                    <span class="order-last text-xs text-muted">jen do {{ $option->roll_width }} cm</span>
                                                @endunless
                                            </x-web.estimator-tile>
                                        @endforeach
                                    </div>
                                @empty
                                    <p class="mt-4 text-sm text-muted">Látky k tomuto provedení doplňujeme. Pošlete nám poptávku a vzorník vám ukážeme.</p>
                                @endforelse
                                @error('fabricId') <p class="mt-3 text-sm text-error" role="alert">{{ $message }}</p> @enderror
                                @break
                        @endswitch

                        @if (count($this::STEPS) > 1)
                            <div class="mt-6 flex flex-wrap gap-3">
                                @unless ($loop->first)
                                    <button type="button" wire:click="back" class="btn-secondary btn-sm">Zpět</button>
                                @endunless
                                @unless ($loop->last)
                                    <button type="button" wire:click="next" class="btn-primary btn-sm">Pokračovat</button>
                                @endunless
                            </div>
                        @endif
                    </div>
                @endif
            </li>
        @endforeach
    </ol>

    <div class="space-y-4 lg:sticky lg:top-28 lg:self-start">
        <div class="border-2 border-ink bg-white p-7" aria-live="polite">
            <h3 class="text-lg font-extrabold">Shrnutí a cena</h3>

            <dl class="mt-4 space-y-1.5 text-sm">
                <div class="flex justify-between gap-3">
                    <dt class="text-muted">Provedení</dt>
                    <dd class="text-right font-medium">{{ $variant->name }}</dd>
                </div>
                @foreach ($this::STEPS as $key)
                    <div class="flex justify-between gap-3">
                        <dt class="text-muted">{{ $this->stepTitle($key) }}</dt>
                        <dd class="text-right font-medium">{{ $this->stepValue($key) ?? '—' }}</dd>
                    </div>
                @endforeach
            </dl>

            <div class="mt-5 border-t border-line pt-4">
                <label for="shade-count" class="block text-sm font-medium">Počet kusů</label>
                <input id="shade-count" type="number" inputmode="numeric" min="1" max="99" wire:model.live.debounce.400ms="count"
                    class="mt-1 block w-24 rounded-lg border border-line bg-white px-3 py-2 focus:border-brand-600 focus:ring-brand-600">
                @error('count') <p class="mt-1 text-sm text-error" role="alert">{{ $message }}</p> @enderror
            </div>

            @if (! $this->hasPriceList())
                <p class="mt-5 text-muted">Ceník tohoto provedení doplňujeme. Zadejte rozměr, vyberte látku a pošlete nám poptávku, nacenění vám zašleme.</p>
            @elseif ($totalPriceVat !== null)
                <p class="text-price-xl mt-5">{{ number_format($totalPriceVat, 0, ',', ' ') }}&nbsp;Kč</p>
                <p class="text-sm text-muted">vč. DPH ({{ number_format($totalPrice, 0, ',', ' ') }}&nbsp;Kč bez DPH)</p>

                <details class="mt-4 border-t border-line pt-3 text-sm">
                    <summary class="cursor-pointer font-semibold text-brand-600">Z čeho se cena skládá</summary>
                    <ul class="mt-2 space-y-1">
                        @foreach ($priceLines as $line)
                            <li class="flex justify-between gap-3">
                                <span class="text-muted">{{ $line['label'] }}</span>
                                <span class="tabular-nums">{{ number_format($line['amount'], 2, ',', ' ') }} Kč</span>
                            </li>
                        @endforeach
                    </ul>
                    <p class="mt-2 text-xs text-muted">Rozpis platí pro 1 kus, bez DPH.</p>
                </details>
            @elseif ($priceMissing)
                <p class="mt-5 text-muted">Pro tento rozměr cenu naceníme individuálně. Pošlete nám poptávku.</p>
            @else
                <p class="mt-5 text-muted">Zadejte rozměr, vyberte látku a cena se hned objeví.</p>
            @endif

            <button type="button" wire:click="sendToInquiry" class="btn-primary mt-6 w-full">
                Poptat s touto konfigurací
            </button>
            <p class="mt-2 text-center text-xs text-muted">Nezávazně. Výslednou cenu zkontrolujeme a potvrdíme v nabídce.</p>
        </div>

        {{-- Popis vybrané látky pod cenou, ať je vidět vedle výběru. --}}
        @if ($chosen = $this->selectedFabric())
            <div class="border border-line bg-white p-6" wire:key="fabric-detail-{{ $chosen->id }}">
                <div class="flex items-center gap-4">
                    @if ($chosenImage = $chosen->imageUrl())
                        <img src="{{ $chosenImage }}" alt="" loading="lazy" width="64" height="64" class="h-16 w-16 shrink-0 object-cover">
                    @endif
                    <div>
                        <h3 class="font-extrabold">Látka {{ $chosen->name }}</h3>
                        <p class="text-sm text-muted">Cenová skupina {{ $chosen->price_category->value }}</p>
                    </div>
                </div>
                <dl class="mt-4 space-y-1.5 text-sm">
                    @foreach ($chosen->specs ?? [] as $row)
                        <div class="flex justify-between gap-3">
                            <dt class="shrink-0 text-muted">{{ $row['label'] }}</dt>
                            <dd class="text-right">{{ $row['value'] }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        @endif
    </div>
</div>
