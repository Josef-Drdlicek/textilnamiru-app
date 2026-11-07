<div class="grid items-start gap-6 lg:grid-cols-[1fr_360px]">
    <div class="space-y-6 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
        <div class="grid gap-4 sm:grid-cols-3">
            <x-form.field name="width" label="Šířka" hint="10–200 cm">
                <x-form.input name="width" type="number" min="10" max="200" step="0.1" wire:model.live.debounce.400ms="width" unit="cm" />
            </x-form.field>

            <x-form.field name="height" label="Délka" hint="10–200 cm">
                <x-form.input name="height" type="number" min="10" max="200" step="0.1" wire:model.live.debounce.400ms="height" unit="cm" />
            </x-form.field>

            <x-form.field name="depth" label="Tloušťka" hint="1–50 cm">
                <x-form.input name="depth" type="number" min="1" max="50" step="0.1" wire:model.live.debounce.400ms="depth" unit="cm" />
            </x-form.field>
        </div>

        <fieldset>
            <legend class="text-sm font-medium text-stone-500">Provedení</legend>
            <div class="mt-2 grid gap-2 sm:grid-cols-3">
                <x-form.checkbox name="foam" label="Molitan" hint="Počet vrstev podle tloušťky." wire:model.live="foam" />
                <x-form.checkbox name="felt" label="Rouno" wire:model.live="felt" />
                <x-form.checkbox name="ties" label="Úvazy" wire:model.live="ties" />
            </div>
        </fieldset>

        <div class="grid gap-4 sm:grid-cols-2">
            @include('livewire.configurators.partials._zipper-field')
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
