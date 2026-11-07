<div class="grid items-start gap-6 lg:grid-cols-[1fr_360px]">
    <div class="space-y-6 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
        <div class="grid gap-4 sm:grid-cols-3">
            <x-form.field name="width" label="Šířka místnosti" hint="10–2000 cm">
                <x-form.input name="width" type="number" min="10" max="2000" step="0.1" wire:model.live.debounce.400ms="width" unit="cm" />
            </x-form.field>

            <x-form.field name="length" label="Délka místnosti" hint="10–2000 cm">
                <x-form.input name="length" type="number" min="10" max="2000" step="0.1" wire:model.live.debounce.400ms="length" unit="cm" />
            </x-form.field>

            <x-form.field name="surface" label="Povrch">
                <x-form.select name="surface" wire:model.live="surface">
                    @foreach (\App\Enums\CeilingSurface::cases() as $option)
                        <option value="{{ $option->value }}">{{ $option->label() }}</option>
                    @endforeach
                </x-form.select>
            </x-form.field>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <x-form.field name="ledLength" label="LED pásek" hint="Nepovinné.">
                <x-form.input name="ledLength" type="number" min="0" max="500" step="0.1" wire:model.live.debounce.400ms="ledLength" unit="m" />
            </x-form.field>

            <x-form.field name="distance" label="Doprava" hint="Nepovinné.">
                <x-form.input name="distance" type="number" min="0" max="2000" step="1" wire:model.live.debounce.400ms="distance" unit="km" />
            </x-form.field>
        </div>

        <div class="grid gap-4 border-t border-stone-100 pt-5 sm:grid-cols-2">
            @include('livewire.configurators.partials._common-fields')
        </div>

        @include('livewire.configurators.partials._actions')
    </div>

    <aside class="space-y-6 lg:sticky lg:top-6">
        @include('livewire.configurators.partials._price-panel')
        @include('livewire.configurators.partials._schema')
    </aside>
</div>
