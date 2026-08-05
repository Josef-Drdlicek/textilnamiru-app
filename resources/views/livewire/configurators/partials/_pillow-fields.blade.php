<div class="grid gap-4 sm:grid-cols-2">
    <x-form.field name="width" label="Šířka" hint="10–100 cm">
        <x-form.input name="width" type="number" min="10" max="100" step="0.1" wire:model.live.debounce.400ms="width" unit="cm" />
    </x-form.field>

    <x-form.field name="height" label="Výška" hint="10–100 cm">
        <x-form.input name="height" type="number" min="10" max="100" step="0.1" wire:model.live.debounce.400ms="height" unit="cm" />
    </x-form.field>

    @if ($showDepth ?? false)
        <x-form.field name="depth" label="Hloubka">
            <x-form.input name="depth" type="number" min="1" max="300" step="0.1" wire:model.live.debounce.400ms="depth" unit="cm" />
        </x-form.field>
    @endif

    <x-form.field name="zipper" label="Umístění zipu">
        <x-form.select name="zipper" wire:model.live="zipper">
            @foreach (\App\Enums\ZipperSide::cases() as $side)
                <option value="{{ $side->value }}">{{ $side->label() }}</option>
            @endforeach
        </x-form.select>
    </x-form.field>

    <div class="{{ ($showDepth ?? false) ? '' : 'sm:col-span-2' }}">
        <x-form.checkbox name="stuffing" label="Včetně výplně" hint="Duté vlákno — příplatek dle rozměru." wire:model.live="stuffing" />
    </div>
</div>
