<div class="grid gap-4 sm:grid-cols-2">
    <x-form.field name="width" label="Hotová šířka">
        <x-form.input name="width" type="number" min="10" max="2000" step="0.1" wire:model.live.debounce.400ms="width" unit="cm" />
    </x-form.field>

    <x-form.field name="height" label="Hotová výška">
        <x-form.input name="height" type="number" min="10" max="500" step="0.1" wire:model.live.debounce.400ms="height" unit="cm" />
    </x-form.field>

    <x-form.field name="materialWidth" label="Šířka dodaného materiálu">
        <x-form.input name="materialWidth" type="number" min="10" max="1000" step="0.1" wire:model.live.debounce.400ms="materialWidth" unit="cm" />
    </x-form.field>

    <x-form.field name="room" label="Místnost" hint="Nepovinné — pomůže dílně i vám s orientací.">
        <x-form.input name="room" type="text" wire:model.live.debounce.400ms="room" placeholder="např. obývák" />
    </x-form.field>

    <x-form.field name="ribbonCode" label="Řasící stuha">
        <x-form.select name="ribbonCode" wire:model.live="ribbonCode">
            @foreach ($this->ribbons as $ribbon)
                <option value="{{ $ribbon->code }}">
                    {{ $ribbon->name }}{{ $ribbon->price_per_meter > 0 ? ' ('.number_format($ribbon->price_per_meter, 0, ',', ' ').' Kč/m)' : '' }}
                </option>
            @endforeach
        </x-form.select>
    </x-form.field>

    @if ($this->selectedRibbon?->allows_custom_ratio)
        <x-form.field name="ratioCustom" label="Vlastní poměr řasení" hint="Kolikrát se látka nabere — např. 2 = dvojnásobek šířky.">
            <x-form.input name="ratioCustom" type="number" min="1" max="10" step="0.05" wire:model.live.debounce.400ms="ratioCustom" unit="×" />
        </x-form.field>
    @else
        <x-form.field name="ratio" label="Poměr řasení">
            <x-form.select name="ratio" wire:model.live="ratio">
                @foreach ($this->selectedRibbon?->allowed_ratios ?? [] as $allowedRatio)
                    <option value="{{ rtrim(rtrim(number_format((float) $allowedRatio, 2, '.', ''), '0'), '.') }}">
                        {{ number_format((float) $allowedRatio, 2, ',', ' ') }}×
                    </option>
                @endforeach
            </x-form.select>
        </x-form.field>
    @endif

    <x-form.field name="rightLeft" label="Provedení">
        <x-form.select name="rightLeft" wire:model.live="rightLeft">
            @foreach (\App\Enums\RightLeft::cases() as $side)
                <option value="{{ $side->value }}">{{ $side->label() }}</option>
            @endforeach
        </x-form.select>
    </x-form.field>

    <div class="flex flex-col gap-2">
        <x-form.checkbox name="kanyr" label="Kanýr" wire:model.live="kanyr" />

        @if ($kanyr)
            <x-form.field name="kanyrHeight" label="Výška kanýru">
                <x-form.input name="kanyrHeight" type="number" min="1" max="200" step="0.1" wire:model.live.debounce.400ms="kanyrHeight" unit="cm" />
            </x-form.field>
        @endif
    </div>
</div>

<fieldset class="mt-4">
    <legend class="text-sm font-medium text-stone-500">Doplňky</legend>
    <div class="mt-2 grid gap-2 sm:grid-cols-2">
        <x-form.checkbox name="corners" label="Rohová olůvka" wire:model.live="corners" />
        <x-form.checkbox name="bottom" label="Všití průběžného olova" wire:model.live="bottom" />
        <x-form.checkbox name="narasit" label="Nařasit" wire:model.live="narasit" />
        <x-form.checkbox name="snails" label="Navléknout háčky (šnečky)" wire:model.live="snails" />
    </div>
</fieldset>
