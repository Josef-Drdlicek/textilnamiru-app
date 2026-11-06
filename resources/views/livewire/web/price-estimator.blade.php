@php
    use App\Domain\Pricing\PublicPresets;
    use App\Enums\ItemType;
@endphp

<div class="grid gap-10 lg:grid-cols-[1fr_380px]">
    <div>
        <fieldset>
            <legend class="text-lg font-extrabold">1. Co potřebujete ušít?</legend>
            <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-5">
                @foreach (ItemType::cases() as $itemType)
                    <label
                        wire:click="$set('vybranaDlazdice', '{{ $itemType->value }}')"
                        class="flex cursor-pointer flex-col items-center gap-2 border-2 p-4 text-center transition hover:border-ink/40 has-focus-visible:ring-2 has-focus-visible:ring-brand-600 has-focus-visible:ring-offset-2 {{ $vybranaDlazdice === $itemType->value ? 'border-brand-600 bg-brand-50' : 'border-line bg-white' }}"
                    >
                        <input
                            type="radio"
                            name="estimator-type"
                            value="{{ $itemType->value }}"
                            wire:model.live="type"
                            wire:click="$set('vybranaDlazdice', '{{ $itemType->value }}')"
                            class="sr-only"
                        >
                        <x-item-type-icon :type="$itemType" class="h-10 w-10 {{ $vybranaDlazdice === $itemType->value ? 'text-brand-600' : 'text-muted' }}" />
                        <span class="text-sm font-semibold">{{ $itemType->label() }}</span>
                    </label>
                @endforeach
            </div>

            @if ($asPage)
                {{-- Druhá řada dlaždic: názvy a odkazy se mění tady. --}}
                @php
                    $dalsiDlazdice = [
                        ['nazev' => 'Vypínaný podhled', 'href' => '#'], // DOPLNIT ODKAZ
                        ['nazev' => 'DOPLNIT NÁZEV', 'href' => '#'], // DOPLNIT ODKAZ
                        ['nazev' => 'DOPLNIT NÁZEV', 'href' => '#'], // DOPLNIT ODKAZ
                        ['nazev' => 'DOPLNIT NÁZEV', 'href' => '#'], // DOPLNIT ODKAZ
                        ['nazev' => 'DOPLNIT NÁZEV', 'href' => '#'], // DOPLNIT ODKAZ
                    ];
                @endphp
                <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-5">
                    @foreach ($dalsiDlazdice as $dlazdice)
                        <a
                            href="{{ $dlazdice['href'] }}"
                            wire:click.prevent="vybratNahradniDlazdici({{ $loop->index }})"
                            class="flex cursor-pointer flex-col items-center gap-2 border-2 p-4 text-center transition hover:border-ink/40 has-focus-visible:ring-2 has-focus-visible:ring-brand-600 has-focus-visible:ring-offset-2 {{ $vybranaDlazdice === 'extra-'.$loop->index ? 'border-brand-600 bg-brand-50' : 'border-line bg-white' }}"
                        >
                            {{-- DOPLNIT IKONU --}}
                            <span class="h-10 w-10 shrink-0 {{ $vybranaDlazdice === 'extra-'.$loop->index ? 'text-brand-600' : 'text-muted' }}" aria-hidden="true"></span>
                            <span class="text-sm font-semibold">{{ $dlazdice['nazev'] }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </fieldset>

        <fieldset class="mt-8">
            <legend class="text-lg font-extrabold">2. Rozměry</legend>
            {{-- ROZMĚRY: pro Vypínaný podhled (extra-0) je obsah odstraněn, DOPLNIT nový obsah --}}
            @if ($vybranaDlazdice !== 'extra-0')
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
                        <p id="estimator-width-error" class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="estimator-height" class="block text-sm font-medium">
                        {{ $this->isFabricType() ? 'Hotová výška' : 'Výška' }}
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
                        <p id="estimator-height-error" class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p>
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
                        <p class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p>
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
            @else
                {{-- ROZMĚRY extra-0: pouze vizuální, napojení na výpočet a cenu DOPLNIT v dalším kroku --}}
                <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3">
                    <div>
                        <label for="extra0-sirka" class="block text-sm font-medium">Šířka</label>
                        <div class="relative mt-1">
                            <input
                                id="extra0-sirka"
                                type="number"
                                inputmode="decimal"
                                wire:model="extra0Sirka"
                                class="block w-full rounded-lg border border-line bg-white px-3 py-2.5 pr-12 focus:border-brand-600 focus:ring-brand-600"
                            >
                            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-sm text-muted">cm</span>
                        </div>
                    </div>
                    <div>
                        <label for="extra0-vyska" class="block text-sm font-medium">Výška</label>
                        <div class="relative mt-1">
                            <input
                                id="extra0-vyska"
                                type="number"
                                inputmode="decimal"
                                wire:model="extra0Vyska"
                                class="block w-full rounded-lg border border-line bg-white px-3 py-2.5 pr-12 focus:border-brand-600 focus:ring-brand-600"
                            >
                            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-sm text-muted">cm</span>
                        </div>
                    </div>
                    <div>
                        <label for="extra0-pocet" class="block text-sm font-medium">Počet kusů</label>
                        <input
                            id="extra0-pocet"
                            type="number"
                            inputmode="numeric"
                            wire:model="extra0Pocet"
                            class="mt-1 block w-full rounded-lg border border-line bg-white px-3 py-2.5 focus:border-brand-600 focus:ring-brand-600"
                        >
                    </div>
                </div>
                {{-- ROZMĚRY extra-0: zaškrtávátka v jednom řádku, šířka podle pole Šířka --}}
                <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3">
                    <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-line bg-white px-4 py-3 transition has-checked:border-brand-600 has-checked:bg-brand-50">
                        <input type="checkbox" wire:model="extra0Vyplne" class="h-5 w-5 shrink-0 rounded border-line text-brand-600 focus:ring-brand-600">
                        <span class="font-medium">Včetně výplně</span>
                    </label>
                    <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-line bg-white px-4 py-3 transition has-checked:border-brand-600 has-checked:bg-brand-50">
                        <input type="checkbox" wire:model="extra0Volba1" class="h-5 w-5 shrink-0 rounded border-line text-brand-600 focus:ring-brand-600">
                        <span class="font-medium">DOPLNIT NÁZEV</span>
                    </label>
                    <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-line bg-white px-4 py-3 transition has-checked:border-brand-600 has-checked:bg-brand-50">
                        <input type="checkbox" wire:model="extra0Volba2" class="h-5 w-5 shrink-0 rounded border-line text-brand-600 focus:ring-brand-600">
                        <span class="font-medium">DOPLNIT NÁZEV</span>
                    </label>
                </div>
            @endif
        </fieldset>
    </div>

    <div>
        <div class="border-2 border-ink bg-white p-7" aria-live="polite">
            <h3 class="text-lg font-extrabold">3. Orientační cena</h3>

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
                Orientační cena za ušití. Nezahrnuje látku, dopravu a montáž — přesnou
                nabídku připravíme zdarma po zaměření.
                @if ($this->isFabricType())
                    Počítáme se šíří látky {{ $this->itemType() === ItemType::Curtain ? '300' : '140' }}&nbsp;cm.
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
