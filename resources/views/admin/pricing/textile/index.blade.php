<x-layouts.app title="Ceník textilu">
    <div class="space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-stone-900">Ceník textilu</h1>
                <p class="mt-1 text-sm text-stone-500">
                    Látky a jejich cena za běžný metr. Ušití se počítá zvlášť podle Ceníku šití.
                </p>
            </div>
            <a href="{{ route('admin.textile-pricing.create') }}" class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-800">
                Přidat produkt
            </a>
        </div>

        {{-- Filtr kategorie: Vše / Záclony / Závěsy --}}
        <nav class="flex flex-wrap gap-2 text-sm" aria-label="Kategorie">
            @foreach ([null => 'Vše', ...collect(\App\Enums\TextileCategory::cases())->mapWithKeys(fn ($c) => [$c->value => $c->pluralLabel()])->all()] as $value => $label)
                @php $active = ($category?->value ?? '') === (string) $value; @endphp
                <a href="{{ route('admin.textile-pricing.index', array_filter(['kategorie' => $value])) }}"
                   class="rounded-full border px-3 py-1 font-medium transition {{ $active ? 'border-teal-700 bg-teal-50 text-teal-800' : 'border-stone-300 text-stone-600 hover:bg-stone-100' }}"
                   @if ($active) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
            <span class="self-center text-stone-500">{{ $products->count() }} produktů</span>
        </nav>

        <div class="overflow-x-auto rounded-2xl border border-stone-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-stone-200 text-sm">
                <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">
                    <tr>
                        <th class="px-5 py-3">Název</th>
                        <th class="px-5 py-3">Kategorie</th>
                        <th class="px-5 py-3 text-right">Cena bez DPH</th>
                        <th class="px-5 py-3 text-right">Cena s DPH</th>
                        <th class="px-5 py-3"><span class="sr-only">Akce</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($products as $product)
                        <tr>
                            <td class="px-5 py-3 font-medium text-stone-800">
                                {{ $product->name }}
                                @unless ($product->is_active)
                                    <span class="ml-1 rounded bg-stone-100 px-1.5 py-0.5 text-xs font-medium text-stone-500">skrytý</span>
                                @endunless
                            </td>
                            <td class="px-5 py-3 text-stone-600">{{ $product->category->label() }}</td>
                            @if ($product->hasPrice())
                                <td class="px-5 py-3 text-right whitespace-nowrap tabular-nums">{{ number_format($product->price_per_meter, 2, ',', ' ') }} Kč/m</td>
                                <td class="px-5 py-3 text-right whitespace-nowrap tabular-nums">{{ number_format(round($product->price_per_meter * (100 + $vatRate)) / 100, 2, ',', ' ') }} Kč/m</td>
                            @else
                                <td class="px-5 py-3 text-right text-stone-400">—</td>
                                <td class="px-5 py-3 text-right text-stone-400">—</td>
                            @endif
                            <td class="px-5 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('admin.textile-pricing.edit', $product) }}" class="font-medium text-teal-800 hover:underline">Upravit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-6 text-center text-stone-500">Ceník textilu zatím nemá žádné produkty.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
