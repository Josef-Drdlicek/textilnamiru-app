<x-layouts.app :title="'Objednání – '.$order->name">
    <div class="mx-auto max-w-3xl space-y-8">
        <div>
            <p class="text-sm text-stone-500">
                <a href="{{ route('orders.welcome') }}" class="hover:underline">Moje zakázky</a>
                <span class="mx-1">/</span>
                <a href="{{ route('orders.show', $order) }}" class="hover:underline">{{ $order->name }}</a>
                <span class="mx-1">/</span>
                Objednání
            </p>
            <h1 class="mt-1 text-2xl font-bold text-stone-900">Závazné objednání</h1>
            <p class="mt-1 text-sm text-stone-500">
                Zkontrolujte položky, doplňte dodaný materiál a termín. Po objednání už zakázku nepůjde měnit.
            </p>
        </div>

        <section class="overflow-x-auto rounded-2xl border border-stone-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-stone-200 text-sm">
                <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">
                    <tr>
                        <th class="px-5 py-3">Výrobek</th>
                        <th class="px-5 py-3">Materiál</th>
                        <th class="px-5 py-3 text-right">Kusů</th>
                        <th class="px-5 py-3 text-right">Celkem bez DPH</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($order->items as $item)
                        <tr>
                            <td class="px-5 py-3 font-medium text-stone-800">
                                {{ $item->type->label() }}
                                @if ($item->room)
                                    <span class="text-stone-400">· {{ $item->room }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">{{ $item->material }}</td>
                            <td class="px-5 py-3 text-right">{{ $item->count }}</td>
                            <td class="px-5 py-3 text-right tabular-nums">{{ number_format($item->total_price, 2, ',', ' ') }} Kč</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-stone-50 font-semibold text-stone-800">
                    <tr>
                        <td colspan="3" class="px-5 py-3 text-right">Celkem bez DPH</td>
                        <td class="px-5 py-3 text-right tabular-nums">{{ number_format($order->items->sum('total_price'), 2, ',', ' ') }} Kč</td>
                    </tr>
                </tfoot>
            </table>
        </section>

        <form
            method="POST"
            action="{{ route('orders.submit', $order) }}"
            class="space-y-5 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm"
        >
            @csrf

            <div>
                <label for="materials_note" class="block text-sm font-medium text-stone-700">Soupis dodaného materiálu</label>
                <p class="text-xs text-stone-400">Vypište látky, které do dílny dodáte — typ, barvu a metráž.</p>
                <textarea
                    id="materials_note"
                    name="materials_note"
                    rows="4"
                    placeholder="např. voal bílý – 9 m&#10;závěsovina šedá – 6 m"
                    class="mt-2 block w-full rounded-lg border border-stone-300 px-3 py-2 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                >{{ old('materials_note', $order->materials_note) }}</textarea>
                @error('materials_note')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="deadline" class="block text-sm font-medium text-stone-700">Datum požadované realizace</label>
                <input
                    id="deadline"
                    name="deadline"
                    type="date"
                    required
                    min="{{ now()->addDay()->toDateString() }}"
                    value="{{ old('deadline', $order->deadline?->toDateString()) }}"
                    class="mt-1 block w-full rounded-lg border border-stone-300 px-3 py-2 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:max-w-xs"
                >
                @error('deadline')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-stone-100 pt-5">
                <a href="{{ route('orders.show', $order) }}" class="rounded-lg border border-stone-300 px-4 py-2 text-sm font-medium text-stone-600 transition hover:bg-stone-100">
                    ← Zpět na zakázku
                </a>
                <div class="flex flex-wrap gap-3">
                    {{-- Odložení nevyžaduje termín, proto formnovalidate. --}}
                    <button type="submit" formaction="{{ route('orders.defer', $order) }}" formnovalidate
                        class="rounded-lg border border-teal-700 px-5 py-2.5 font-semibold text-teal-800 transition hover:bg-teal-50">
                        Odložit objednávku
                    </button>
                    <button type="submit"
                        onclick="return confirm('Závazně objednat tuto zakázku? Po objednání ji už nepůjde upravit.');"
                        class="rounded-lg bg-teal-700 px-6 py-2.5 font-semibold text-white transition hover:bg-teal-800">
                        Závazně objednat
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.app>
