{{-- Výpočet ceny kolejnice/garnýže: držák a délka vlevo, shrnutí a cena vpravo. --}}
@php $decorative = $variant->product->kind === \App\Enums\RailKind::Decorative; @endphp
<div class="grid gap-8 lg:grid-cols-[1fr_380px]">
    <div class="space-y-4">
    <div class="border-2 border-ink bg-white px-5 pt-5 pb-6">
        <h3 class="font-extrabold">Způsob zavěšení</h3>
        <p class="mt-2 text-sm text-muted">Cena se liší podle držáku. Nevíte si rady? Poradíme při zaměření.</p>
        <div class="mt-4 grid gap-3 sm:grid-cols-2">
            @foreach ($this->mountingOptions() as $option)
                <x-web.estimator-tile model="mounting" :value="$option->value" :selected="$mounting === $option->value" :label="$option->label()" class="p-4">
                    <span class="order-last text-xs text-muted">{{ $option->description() }}</span>
                </x-web.estimator-tile>
            @endforeach
        </div>
        @error('mounting') <p class="mt-3 text-sm text-error" role="alert">{{ $message }}</p> @enderror
    </div>

    <div class="border-2 border-ink bg-white px-5 pt-5 pb-6">
        <h3 class="font-extrabold">Délka {{ $decorative ? 'garnýže' : 'kolejnice' }}</h3>
        <p class="mt-2 text-sm text-muted">
            Zadejte délku v centimetrech – obvykle šířku okna a k ní 10–20 cm na každou stranu, aby šel textil stáhnout mimo okno.
            Cenu počítáme po 50 cm, délku zaokrouhlíme nahoru. Kolejnici zkrátíme přesně na míru.
        </p>
        <div class="mt-4 max-w-56">
            <label for="rail-length" class="block text-sm font-medium">Délka <span class="text-muted">(1–{{ \App\Domain\Pricing\RailPricer::MAX_LENGTH }} cm)</span></label>
            <div class="relative mt-1">
                <input id="rail-length" type="number" inputmode="decimal" min="1" max="{{ \App\Domain\Pricing\RailPricer::MAX_LENGTH }}" wire:model.live.debounce.400ms="length"
                    class="block w-full rounded-lg border border-line bg-white px-3 py-2.5 pr-12 focus:border-brand-600 focus:ring-brand-600"
                    @error('length') aria-invalid="true" aria-describedby="rail-length-error" @enderror>
                <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-sm text-muted">cm</span>
            </div>
            @error('length') <p id="rail-length-error" class="mt-1 text-sm text-error" role="alert">{{ $message }}</p> @enderror
            @if ($band = $this->bandLabel())
                <p class="mt-2 text-sm text-muted">Pásmo ceníku: <span class="font-semibold text-ink">{{ $band }}</span></p>
            @endif
        </div>
        <p class="mt-4 text-xs text-muted">Delší než 6 m? Kolejnice spojíme – pošlete nám poptávku.</p>
    </div>
    </div>

    <div class="lg:sticky lg:top-28 lg:self-start">
        <div class="border-2 border-ink bg-white p-7" aria-live="polite">
            <h3 class="text-lg font-extrabold">Shrnutí a cena</h3>

            <dl class="mt-4 space-y-1.5 text-sm">
                <div class="flex justify-between gap-3">
                    <dt class="text-muted">{{ $decorative ? 'Garnýž' : 'Kolejnice' }}</dt>
                    <dd class="text-right font-medium">{{ $variant->product->name }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-muted">{{ $decorative ? 'Barva' : 'Sestava' }}</dt>
                    <dd class="text-right font-medium">{{ $variant->code ? $variant->code.' · ' : '' }}{{ $variant->name }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-muted">Zavěšení</dt>
                    <dd class="text-right font-medium">{{ $this->selectedMounting()?->label() ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-muted">Délka</dt>
                    <dd class="text-right font-medium">{{ is_numeric($length) ? rtrim(rtrim(number_format((float) $length, 1, ',', ' '), '0'), ',').' cm' : '—' }}</dd>
                </div>
            </dl>

            <div class="mt-5 border-t border-line pt-4">
                <label for="rail-count" class="block text-sm font-medium">Počet kusů</label>
                <input id="rail-count" type="number" inputmode="numeric" min="1" max="99" wire:model.live.debounce.400ms="count"
                    class="mt-1 block w-24 rounded-lg border border-line bg-white px-3 py-2 focus:border-brand-600 focus:ring-brand-600">
                @error('count') <p class="mt-1 text-sm text-error" role="alert">{{ $message }}</p> @enderror
            </div>

            @if (! $this->hasPriceList())
                <p class="mt-5 text-muted">Ceník doplňujeme. Zadejte délku a pošlete nám poptávku, nacenění vám zašleme.</p>
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
                <p class="mt-5 text-muted">Pro tuto délku a držák cenu naceníme individuálně. Pošlete nám poptávku.</p>
            @else
                <p class="mt-5 text-muted">Vyberte držák, zadejte délku a cena se hned objeví.</p>
            @endif

            <button type="button" wire:click="sendToInquiry" class="btn-primary mt-6 w-full">
                Poptat s touto konfigurací
            </button>
            <p class="mt-2 text-center text-xs text-muted">Nezávazně. Výslednou cenu zkontrolujeme a potvrdíme v nabídce.</p>
        </div>
    </div>
</div>
