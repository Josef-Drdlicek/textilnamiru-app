<x-layouts.app title="Cenové nabídky">
    <div class="mx-auto max-w-4xl space-y-10">
        <section>
            <h1 class="text-2xl font-bold text-stone-900">Cenové nabídky</h1>
            <p class="mt-1 text-sm text-stone-500">Rozpracované a odložené zakázky. Ceny jsou orientační podle aktuálního ceníku.</p>

            @if ($quotes->isEmpty())
                <p class="mt-3 rounded-2xl border border-dashed border-stone-300 bg-white px-5 py-8 text-center text-sm text-stone-500">
                    Žádné cenové nabídky. Novou založíte v <a href="{{ route('orders.welcome') }}" class="font-medium text-teal-800 hover:underline">Moje zakázky</a>.
                </p>
            @else
                <div class="mt-3 overflow-x-auto rounded-2xl border border-stone-200 bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-stone-200 text-sm">
                        <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">
                            <tr>
                                <th class="px-5 py-3">Název zakázky</th>
                                <th class="px-5 py-3">Datum zadání</th>
                                <th class="px-5 py-3 text-right">Cena bez DPH</th>
                                <th class="px-5 py-3 text-right">Cena s DPH</th>
                                <th class="px-5 py-3"><span class="sr-only">Akce</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @foreach ($quotes as $quote)
                                @php $net = (float) $quote->items_total; @endphp
                                <tr>
                                    <td class="px-5 py-3 font-medium text-stone-800">
                                        <a href="{{ route('orders.show', $quote) }}" class="text-teal-800 hover:underline">{{ $quote->name }}</a>
                                        @if ($quote->deferred_at)
                                            <span class="ml-1 rounded bg-amber-50 px-1.5 py-0.5 text-xs font-medium text-amber-800">odložená</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-stone-600">{{ ($quote->deferred_at ?? $quote->created_at)->format('j. n. Y') }}</td>
                                    <td class="px-5 py-3 text-right tabular-nums">{{ number_format($net, 2, ',', ' ') }} Kč</td>
                                    <td class="px-5 py-3 text-right tabular-nums">{{ number_format(round($net * (100 + $vatRate)) / 100, 2, ',', ' ') }} Kč</td>
                                    <td class="whitespace-nowrap px-5 py-3 text-right">
                                        <a href="{{ route('orders.show', $quote) }}" class="font-medium text-teal-800 hover:underline">Upravit</a>
                                        <form method="POST" action="{{ route('orders.destroy', $quote) }}" class="ml-3 inline"
                                              onsubmit="return confirm('Opravdu smazat tuto cenovou nabídku? Tuto akci nelze vrátit.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="font-medium text-red-700 hover:underline">Smazat</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
</x-layouts.app>
