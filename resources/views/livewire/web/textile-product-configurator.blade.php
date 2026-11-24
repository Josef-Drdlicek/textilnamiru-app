@php
    use App\Enums\CurtainHem;
    use App\Enums\CurtainMakeType;
    use App\Livewire\Web\TextileProductConfigurator as Configurator;

    $steps = $this->availableSteps();
    // Podstatné jméno do vět průvodce: záclona / závěs (4. pád: záclonu / závěs).
    $noun = mb_strtolower($product->category->label());
    $nounAccusative = $product->category === \App\Enums\TextileCategory::Curtain ? 'záclonu' : 'závěs';
@endphp

{{-- Průvodce vlevo (rozbalovací kroky s „Upravit“), shrnutí a cena vpravo. --}}
<div class="grid gap-8 lg:grid-cols-[1fr_380px]">
    <ol class="space-y-3">
        @foreach ($steps as $key)
            @php
                $open = $step === $key;
                $value = $this->stepValue($key);
            @endphp
            <li wire:key="step-{{ $key }}" class="border-2 bg-white {{ $open ? 'border-ink' : 'border-line' }}">
                <button
                    type="button"
                    wire:click="openStep('{{ $key }}')"
                    class="flex w-full items-center gap-4 px-5 py-4 text-left"
                    aria-expanded="{{ $open ? 'true' : 'false' }}"
                    aria-controls="krok-{{ $key }}"
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
                    <div id="krok-{{ $key }}" class="border-t border-line px-5 pt-5 pb-6">
                        @switch($key)
                            @case('type')
                                <p class="text-sm text-muted">Z látky ušijeme různé typy zavěšení. Vyberte, který se nejvíc blíží vaší představě.</p>
                                <div class="mt-4 grid gap-3 sm:grid-cols-3">
                                    @foreach (CurtainMakeType::cases() as $option)
                                        <x-web.estimator-tile model="makeType" :value="$option->value" :selected="$makeType === $option->value" :label="$option->label($product->category)" class="p-4">
                                            <span class="order-last text-xs text-muted">{{ $option->description() }}</span>
                                            @unless ($option->isPricedOnline())
                                                <span class="rounded-full bg-brand-100 px-2.5 py-0.5 text-xs font-semibold text-brand-700">Na poptávku</span>
                                            @endunless
                                        </x-web.estimator-tile>
                                    @endforeach
                                </div>
                                @if ($this->selectedMakeType()?->isPricedOnline() === false)
                                    <p class="mt-4 text-sm text-muted">Tento typ naceníme individuálně. Pošlete nám poptávku, látku i typ zavěšení k ní přiložíme.</p>
                                @endif
                                @break

                            @case('color')
                                <p class="text-sm text-muted">Vyberte barvu látky pro výsledný výrobek. Fotka nahoře se přepne na zvolenou barvu.</p>
                                <div class="mt-4 grid grid-cols-3 gap-3 sm:grid-cols-5">
                                    @foreach ($product->colors as $option)
                                        <x-web.estimator-tile model="color" :value="$option->name" :selected="$color === $option->name" :label="$option->label()" class="p-2" wire:key="color-{{ $option->id }}">
                                            @if ($optionImage = $option->imageUrl())
                                                <img src="{{ $optionImage }}" alt="" loading="lazy" class="aspect-square w-full object-cover">
                                            @endif
                                        </x-web.estimator-tile>
                                    @endforeach
                                </div>
                                @break

                            @case('hem')
                                <p class="text-sm text-muted">Zvolte, jak máme ušít spodní okraj. Způsob ovlivňuje vzhled i cenu.</p>
                                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                    @foreach (CurtainHem::cases() as $option)
                                        <x-web.estimator-tile model="hem" :value="$option->value" :selected="$hem === $option->value" :label="$option->label()" class="p-4">
                                            <span class="order-last text-xs text-muted">{{ $option->description() }}</span>
                                        </x-web.estimator-tile>
                                    @endforeach
                                </div>
                                @break

                            @case('split')
                                <p class="text-sm text-muted">Chcete {{ $nounAccusative }} vcelku, nebo rozdělit na dva díly (opona), které se roztahují do stran?</p>
                                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                    <x-web.estimator-tile model="split" value="one" :selected="$split === 'one'" label="Nerozdělovat (jeden kus)" class="p-4" />
                                    <x-web.estimator-tile model="split" value="two" :selected="$split === 'two'" label="Rozdělit (dva díly)" class="p-4" />
                                </div>
                                @break

                            @case('dimensions')
                                <p class="text-sm text-muted">
                                    Zadejte hotové rozměry na okně — šířku, kterou má {{ $noun }} zakrýt, a výšku od kolejnice dolů.
                                    Kolik látky je potřeba na nařasení, spočítáme sami. Dejte pozor, aby se {{ $noun }} nedotýkal{{ $product->category === \App\Enums\TextileCategory::Curtain ? 'a' : '' }} podlahy.
                                </p>
                                <div class="mt-4 grid max-w-md grid-cols-2 gap-4">
                                    <div>
                                        <label for="product-width" class="block text-sm font-medium">Šířka <span class="text-muted">(od {{ Configurator::MIN_WIDTH }} cm)</span></label>
                                        <div class="relative mt-1">
                                            <input id="product-width" type="number" inputmode="decimal" min="{{ Configurator::MIN_WIDTH }}" max="{{ Configurator::MAX_WIDTH }}" wire:model.live.debounce.400ms="width"
                                                class="block w-full rounded-lg border border-line bg-white px-3 py-2.5 pr-12 focus:border-brand-600 focus:ring-brand-600"
                                                @error('width') aria-invalid="true" aria-describedby="product-width-error" @enderror>
                                            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-sm text-muted">cm</span>
                                        </div>
                                        @error('width') <p id="product-width-error" class="mt-1 text-sm text-error" role="alert">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label for="product-height" class="block text-sm font-medium">Výška <span class="text-muted">({{ Configurator::MIN_HEIGHT }}–{{ $product->maxHeight() }} cm)</span></label>
                                        <div class="relative mt-1">
                                            <input id="product-height" type="number" inputmode="decimal" min="{{ Configurator::MIN_HEIGHT }}" max="{{ $product->maxHeight() }}" wire:model.live.debounce.400ms="height"
                                                class="block w-full rounded-lg border border-line bg-white px-3 py-2.5 pr-12 focus:border-brand-600 focus:ring-brand-600"
                                                @error('height') aria-invalid="true" aria-describedby="product-height-error" @enderror>
                                            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-sm text-muted">cm</span>
                                        </div>
                                        @error('height') <p id="product-height-error" class="mt-1 text-sm text-error" role="alert">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                                @break

                            @case('tape')
                                <p class="text-sm text-muted">
                                    Řasící páska určuje vzhled skladů a poměr řasení. Poměr 1:2 znamená, že na 2 m široké okno ušijeme 4 m látky,
                                    takže {{ $noun }} není rovná deska, ale vlní se. 1:2 je standard; nižší poměr ušetří látku i cenu.
                                </p>
                                <label for="product-tape" class="mt-4 block text-sm font-medium">Řasící páska a poměr řasení</label>
                                <select id="product-tape" wire:model.live="tape"
                                    class="mt-1 block w-full max-w-md rounded-lg border border-line bg-white px-3 py-2.5 focus:border-brand-600 focus:ring-brand-600">
                                    <option value="">Vyberte pásku…</option>
                                    @foreach ($this->tapeOptions() as $optionValue => $optionLabel)
                                        <option value="{{ $optionValue }}">{{ $optionLabel }}</option>
                                    @endforeach
                                </select>
                                @break
                        @endswitch

                        @foreach (['makeType', 'color', 'hem', 'split', 'tape'] as $field)
                            @error($field) <p class="mt-3 text-sm text-error" role="alert">{{ $message }}</p> @enderror
                        @endforeach

                        <div class="mt-6 flex flex-wrap gap-3">
                            @unless ($loop->first)
                                <button type="button" wire:click="back" class="btn-secondary btn-sm">Zpět</button>
                            @endunless
                            @unless ($loop->last)
                                <button type="button" wire:click="next" class="btn-primary btn-sm">Pokračovat</button>
                            @endunless
                        </div>
                    </div>
                @endif
            </li>
        @endforeach
    </ol>

    <div>
        <div class="border-2 border-ink bg-white p-7 lg:sticky lg:top-28" aria-live="polite">
            <h3 class="text-lg font-extrabold">Shrnutí a cena</h3>

            <dl class="mt-4 space-y-1.5 text-sm">
                @foreach ($steps as $key)
                    <div class="flex justify-between gap-3">
                        <dt class="text-muted">{{ $this->stepTitle($key) }}</dt>
                        <dd class="text-right font-medium">{{ $this->stepValue($key) ?? '—' }}</dd>
                    </div>
                @endforeach
            </dl>

            @if ($this->selectedMakeType()?->isPricedOnline() !== false)
                <div class="mt-5 border-t border-line pt-4">
                    <label for="product-count" class="block text-sm font-medium">Počet kusů</label>
                    <input id="product-count" type="number" inputmode="numeric" min="1" max="99" wire:model.live.debounce.400ms="count"
                        class="mt-1 block w-24 rounded-lg border border-line bg-white px-3 py-2 focus:border-brand-600 focus:ring-brand-600">
                    @error('count') <p class="mt-1 text-sm text-error" role="alert">{{ $message }}</p> @enderror
                </div>
            @endif

            @if (! $product->hasPrice())
                <p class="mt-5 text-muted">Cenu této látky doplníme. Projděte kroky a pošlete nám poptávku, nacenění vám zašleme.</p>
            @elseif ($totalPriceVat !== null)
                <p class="text-price-xl mt-5">{{ number_format($totalPriceVat, 0, ',', ' ') }}&nbsp;Kč</p>
                <p class="text-sm text-muted">
                    vč. DPH ({{ number_format($totalPrice, 0, ',', ' ') }}&nbsp;Kč bez DPH)
                    · látky {{ number_format($fabricMeters, 2, ',', ' ') }}&nbsp;m
                </p>

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
            @elseif ($this->selectedMakeType()?->isPricedOnline() !== false)
                <p class="mt-5 text-muted">Projděte všechny kroky a cena se hned objeví.</p>
            @endif

            <button
                type="button"
                wire:click="sendToInquiry"
                class="btn-primary mt-6 w-full disabled:pointer-events-none disabled:opacity-40"
                @if ($totalPriceVat === null && $this->canPrice()) disabled aria-disabled="true" @endif
            >
                Poptat s touto konfigurací
            </button>
            <p class="mt-2 text-center text-xs text-muted">
                Nezávazně. Výslednou cenu zkontrolujeme a potvrdíme v nabídce.
                @if ($product->delivery_days) Dodací doba přibližně {{ $product->delivery_days }} dní. @endif
            </p>
        </div>
    </div>
</div>
