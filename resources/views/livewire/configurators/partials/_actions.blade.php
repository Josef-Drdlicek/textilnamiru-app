<div class="flex flex-wrap items-center justify-between gap-3">
    <a href="{{ route('orders.show', $order) }}" class="rounded-lg border border-stone-300 px-4 py-2 text-sm font-medium text-stone-600 transition hover:bg-stone-100">
        ← Zpět na zakázku
    </a>

    <button
        type="button"
        wire:click="save"
        wire:loading.attr="disabled"
        class="rounded-lg bg-teal-700 px-6 py-2.5 font-semibold text-white transition hover:bg-teal-800 disabled:opacity-50"
    >
        <span wire:loading.remove wire:target="save">
            {{ $item !== null ? 'Uložit změny' : 'Přidat do zakázky' }}
        </span>
        <span wire:loading wire:target="save">Ukládám…</span>
    </button>
</div>
