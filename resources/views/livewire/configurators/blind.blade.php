<div class="grid items-start gap-6 lg:grid-cols-[1fr_360px]">
    <div class="space-y-6 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
        <div class="grid gap-4 sm:grid-cols-2">
            <x-form.field name="width" label="Šířka" hint="10–300 cm">
                <x-form.input name="width" type="number" min="10" max="300" step="0.1" wire:model.live.debounce.400ms="width" unit="cm" />
            </x-form.field>

            <x-form.field name="height" label="Výška" hint="10–300 cm">
                <x-form.input name="height" type="number" min="10" max="300" step="0.1" wire:model.live.debounce.400ms="height" unit="cm" />
            </x-form.field>

            <x-form.field name="tunnels" label="Počet tunýlků">
                <x-form.input name="tunnels" type="number" min="1" max="30" step="1" wire:model.live.debounce.400ms="tunnels" />
            </x-form.field>
        </div>

        <fieldset>
            <legend class="text-sm font-medium text-stone-500">Provedení</legend>
            <div class="mt-2 grid gap-2 sm:grid-cols-2">
                <x-form.checkbox name="mechanism" label="Vlastní mechanizmus" hint="Dodáte svůj mechanizmus rolety." wire:model.live="mechanism" />
            </div>
        </fieldset>

        <fieldset class="border-t border-stone-100 pt-5">
            <legend class="text-sm font-medium text-stone-500">Pole rolety (nepovinné)</legend>
            <div class="mt-2 grid gap-4 sm:grid-cols-4">
                <x-form.field name="cells" label="Počet polí">
                    <x-form.input name="cells" type="number" min="1" max="12" step="1" wire:model.live.debounce.400ms="cells" />
                </x-form.field>

                <x-form.field name="cellHeightTop" label="Horní pole">
                    <x-form.input name="cellHeightTop" type="number" min="1" max="300" step="0.1" wire:model.live.debounce.400ms="cellHeightTop" unit="cm" />
                </x-form.field>

                <x-form.field name="cellHeight" label="Střední pole">
                    <x-form.input name="cellHeight" type="number" min="1" max="300" step="0.1" wire:model.live.debounce.400ms="cellHeight" unit="cm" />
                </x-form.field>

                <x-form.field name="cellHeightBottom" label="Spodní pole">
                    <x-form.input name="cellHeightBottom" type="number" min="1" max="300" step="0.1" wire:model.live.debounce.400ms="cellHeightBottom" unit="cm" />
                </x-form.field>
            </div>
        </fieldset>

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
