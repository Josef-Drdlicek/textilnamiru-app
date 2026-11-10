@php
    use App\Domain\Pricing\PublicPresets;
    use App\Enums\CeilingColor;
    use App\Enums\CeilingFinish;
    use App\Enums\CeilingLighting;

    // Obor s provedeními (stropy) má navíc krok „2. Specifikace“, další kroky se posunou o jedna.
    $variants = $this->productCategory()->estimatorVariants();
    $step = $variants === [] ? 2 : 3;
    $selectedVariant = $this->selectedVariant();
@endphp

<div class="grid gap-10 lg:grid-cols-[1fr_380px]">
    <div>
        <fieldset>
            <legend class="text-lg font-extrabold">{{ $this->productCategory()->estimatorQuestion() }}</legend>
            @if ($variants !== [])
                {{-- Provedení s fotkou: dlaždice o 20 % větší než výrobkové (šířka 1,2 × pětina řádku). --}}
                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3 md:grid-cols-[repeat(3,calc((100%_-_3rem)/5*1.2))]">
                    @foreach ($variants as $ceilingVariant)
                        <x-web.estimator-tile
                            model="variant"
                            :value="$ceilingVariant->value"
                            :selected="$variant === $ceilingVariant->value"
                            :label="$ceilingVariant->label()"
                            class="p-5"
                        >
                            <span class="block aspect-[4/3] w-full overflow-hidden">
                                <x-web.img
                                    :name="$ceilingVariant->imageName()"
                                    :alt="'Vypínaný strop – '.mb_strtolower($ceilingVariant->label())"
                                    width="480"
                                    height="360"
                                />
                            </span>
                        </x-web.estimator-tile>
                    @endforeach
                </div>
            @else
                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-5">
                    @foreach ($this->productCategory()->itemTypes() as $itemType)
                        <x-web.estimator-tile
                            model="type"
                            :value="$itemType->value"
                            :selected="$type === $itemType->value"
                            :label="$itemType->label()"
                            class="p-4"
                        >
                            <x-item-type-icon :type="$itemType" class="h-10 w-10 {{ $type === $itemType->value ? 'text-brand-600' : 'text-muted' }}" />
                        </x-web.estimator-tile>
                    @endforeach
                </div>
            @endif
        </fieldset>

        @if ($variants !== [])
            <fieldset class="mt-8">
                <legend class="text-lg font-extrabold">2. Specifikace</legend>
                <div class="mt-4 grid max-w-xl gap-4 sm:grid-cols-2">
                    @if ($selectedVariant?->hasFinishAndColor())
                        <div>
                            <label for="estimator-finish" class="block text-sm font-medium">Druh</label>
                            <select
                                id="estimator-finish"
                                wire:model.live="finish"
                                class="mt-1 block w-full rounded-lg border border-line bg-white px-3 py-2.5 focus:border-brand-600 focus:ring-brand-600"
                            >
                                @foreach (CeilingFinish::cases() as $option)
                                    <option value="{{ $option->value }}">{{ $option->label() }}</option>
                                @endforeach
                            </select>
                            @error('finish')
                                <p class="mt-1 text-sm text-error" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="estimator-color" class="block text-sm font-medium">Barva</label>
                            <select
                                id="estimator-color"
                                wire:model.live="color"
                                class="mt-1 block w-full rounded-lg border border-line bg-white px-3 py-2.5 focus:border-brand-600 focus:ring-brand-600"
                            >
                                @foreach (CeilingColor::cases() as $option)
                                    <option value="{{ $option->value }}">{{ $option->label() }}</option>
                                @endforeach
                            </select>
                            @error('color')
                                <p class="mt-1 text-sm text-error" role="alert">{{ $message }}</p>
                            @enderror
                        </div>
                    @elseif ($selectedVariant?->hasLighting())
                        <div>
                            <label for="estimator-lighting" class="block text-sm font-medium">Světlo</label>
                            <select
                                id="estimator-lighting"
                                wire:model.live="lighting"
                                class="mt-1 block w-full rounded-lg border border-line bg-white px-3 py-2.5 focus:border-brand-600 focus:ring-brand-600"
                            >
                                @foreach (CeilingLighting::cases() as $option)
                                    <option value="{{ $option->value }}">{{ $option->label() }}</option>
                                @endforeach
                            </select>
                            @error('lighting')
                                <p class="mt-1 text-sm text-error" role="alert">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                </div>
            </fieldset>
        @endif

        <fieldset class="mt-8">
            <legend class="text-lg font-extrabold">{{ $step }}. Rozměry</legend>
            <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3">
                <div>
                    <label for="estimator-width" class="block text-sm font-medium">
                        {{ $this->isFabricType() ? 'Hotová šířka' : 'Šířka' }}
                    </label>
                    <div class="relative mt-1">
                        <input
                            id="estimator-width"
                            type="number"
                            inputmode="decimal"
                            min="10"
                            wire:model.live.debounce.400ms="width"
                            class="block w-full rounded-lg border border-line bg-white px-3 py-2.5 pr-12 focus:border-brand-600 focus:ring-brand-600"
                            @error('width') aria-invalid="true" aria-describedby="estimator-width-error" @enderror
                        >
                        <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-sm text-muted">cm</span>
                    </div>
                    @error('width')
                        <p id="estimator-width-error" class="mt-1 text-sm text-error" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="estimator-height" class="block text-sm font-medium">
                        {{ $this->isFabricType() ? 'Hotová výška' : $this->itemType()->heightLabel() }}
                    </label>
                    <div class="relative mt-1">
                        <input
                            id="estimator-height"
                            type="number"
                            inputmode="decimal"
                            min="10"
                            wire:model.live.debounce.400ms="height"
                            class="block w-full rounded-lg border border-line bg-white px-3 py-2.5 pr-12 focus:border-brand-600 focus:ring-brand-600"
                            @error('height') aria-invalid="true" aria-describedby="estimator-height-error" @enderror
                        >
                        <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-sm text-muted">cm</span>
                    </div>
                    @error('height')
                        <p id="estimator-height-error" class="mt-1 text-sm text-error" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="estimator-count" class="block text-sm font-medium">Počet kusů</label>
                    <input
                        id="estimator-count"
                        type="number"
                        inputmode="numeric"
                        min="1"
                        max="999"
                        wire:model.live.debounce.400ms="count"
                        class="mt-1 block w-full rounded-lg border border-line bg-white px-3 py-2.5 focus:border-brand-600 focus:ring-brand-600"
                    >
                    @error('count')
                        <p class="mt-1 text-sm text-error" role="alert">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            @if ($this->isFabricType())
                <div class="mt-5 max-w-sm">
                    <label for="estimator-ruffle" class="block text-sm font-medium">Nařasení</label>
                    <select
                        id="estimator-ruffle"
                        wire:model.live="ruffle"
                        class="mt-1 block w-full rounded-lg border border-line bg-white px-3 py-2.5 focus:border-brand-600 focus:ring-brand-600"
                    >
                        @foreach (PublicPresets::RUFFLE_OPTIONS as $key => $option)
                            <option value="{{ $key }}">{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-muted">Čím bohatší nařasení, tím plnější vzhled — a víc látky.</p>
                </div>
            @endif

            @if ($this->hasStuffingOption())
                <label class="mt-5 flex max-w-sm cursor-pointer items-center gap-3 rounded-lg border border-line bg-white px-4 py-3 transition has-checked:border-brand-600 has-checked:bg-brand-50">
                    <input
                        type="checkbox"
                        wire:model.live="stuffing"
                        class="h-5 w-5 rounded border-line text-brand-600 focus:ring-brand-600"
                    >
                    <span class="font-medium">Včetně výplně</span>
                </label>
            @endif
        </fieldset>
    </div>

    <div>
        <div class="border-2 border-ink bg-white p-7" aria-live="polite">
            <h3 class="text-lg font-extrabold">{{ $step + 1 }}. Orientační cena</h3>

            @if ($totalPriceVat !== null)
                <p class="text-price-xl mt-4">
                    {{ number_format($totalPriceVat, 0, ',', ' ') }}&nbsp;Kč
                </p>
                <p class="text-sm text-muted">
                    vč. DPH za {{ $count }}&nbsp;{{ $count === 1 ? 'kus' : ($count < 5 ? 'kusy' : 'kusů') }}
                    ({{ number_format($totalPrice, 0, ',', ' ') }}&nbsp;Kč bez DPH)
                </p>

                @if (count($priceLines))
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
                @endif
            @else
                <p class="mt-4 text-muted">Zadejte rozměry a cena se hned objeví.</p>
            @endif

            <p class="mt-5 text-xs leading-relaxed text-muted">
                {{ $this->productCategory()->estimatorNote() }}
                @if ($materialWidth = PublicPresets::materialWidth($this->itemType()))
                    Počítáme se šíří látky {{ number_format($materialWidth, 0, ',', ' ') }}&nbsp;cm.
                @endif
            </p>

            <button
                type="button"
                wire:click="sendToInquiry"
                class="btn-primary mt-6 w-full disabled:pointer-events-none disabled:opacity-40"
                @if ($totalPriceVat === null) disabled aria-disabled="true" @endif
            >
                Poptat s touto konfigurací
            </button>
            <p class="mt-2 text-center text-xs text-muted">Nezávazně — konfigurace se přiloží k poptávce.</p>
        </div>
    </div>
</div>
