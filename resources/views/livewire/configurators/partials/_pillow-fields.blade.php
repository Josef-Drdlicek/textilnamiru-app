<div class="grid gap-4 sm:grid-cols-2">
    <x-form.field name="width" label="Šířka" hint="10–100 cm">
        <x-form.input name="width" type="number" min="10" max="100" step="0.1" wire:model.live.debounce.400ms="width" unit="cm" />
    </x-form.field>

    <x-form.field name="height" label="Výška" hint="10–100 cm">
        <x-form.input name="height" type="number" min="10" max="100" step="0.1" wire:model.live.debounce.400ms="height" unit="cm" />
    </x-form.field>

    @include('livewire.configurators.partials._zipper-field')
</div>

<fieldset>
    <legend class="text-sm font-medium text-stone-500">Provedení</legend>
    <div class="mt-2 grid gap-2 sm:grid-cols-2">
        <x-form.checkbox name="stuffing" label="Včetně výplně" hint="Duté vlákno — příplatek dle rozměru." wire:model.live="stuffing" />
        <x-form.checkbox name="complex" label="Složitý tvar" hint="Náročnější ušití." wire:model.live="complex" />
    </div>
</fieldset>
