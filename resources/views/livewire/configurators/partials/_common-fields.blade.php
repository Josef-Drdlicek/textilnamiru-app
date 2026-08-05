<x-form.field name="material" label="Materiál (látka)" hint="Název látky, kterou dodáte — dílna podle něj sčítá spotřebu.">
    <x-form.input name="material" type="text" wire:model.live.debounce.400ms="material" placeholder="např. voal bílý" />
</x-form.field>

<x-form.field name="count" label="Počet kusů">
    <x-form.input name="count" type="number" min="1" max="999" step="1" wire:model.live.debounce.400ms="count" unit="ks" />
</x-form.field>

<x-form.field name="notes" label="Poznámka pro dílnu" hint="Nepovinné." class="sm:col-span-2">
    <textarea
        id="notes"
        rows="2"
        wire:model.live.debounce.600ms="notes"
        class="block w-full rounded-lg border border-stone-300 px-3 py-2 shadow-sm focus:border-teal-500 focus:ring-teal-500"
    ></textarea>
</x-form.field>
