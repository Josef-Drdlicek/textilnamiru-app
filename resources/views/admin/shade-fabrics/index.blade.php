<x-layouts.app title="Textil pro rolety">
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-stone-900">Textil pro rolety</h1>
            <p class="mt-1 text-sm text-stone-500">
                Látky, ze kterých si zákazník vybírá u rolet. Cenová skupina A–D určuje, ze které tabulky v Ceníku rolet se bere cena.
                Šíře látky je zároveň největší šířka rolety z této látky.
            </p>
        </div>

        <form method="GET" class="flex flex-wrap items-end gap-3 rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
            <div>
                <label for="hledat" class="block text-xs font-medium text-stone-500">Hledat</label>
                <input id="hledat" name="hledat" type="search" value="{{ $search }}" placeholder="název látky"
                    class="mt-1 rounded-lg border border-stone-300 px-3 py-1.5 shadow-sm focus:border-teal-500 focus:ring-teal-500">
            </div>
            <div>
                <label for="kolekce" class="block text-xs font-medium text-stone-500">Kolekce</label>
                <select id="kolekce" name="kolekce" class="mt-1 rounded-lg border border-stone-300 px-3 py-1.5 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                    <option value="">Všechny</option>
                    @foreach (\App\Enums\ShadeFabricCollection::cases() as $option)
                        <option value="{{ $option->value }}" @selected($collection === $option)>{{ $option->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="skupina" class="block text-xs font-medium text-stone-500">Cenová skupina</label>
                <select id="skupina" name="skupina" class="mt-1 rounded-lg border border-stone-300 px-3 py-1.5 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                    <option value="">Všechny</option>
                    @foreach (\App\Enums\ShadePriceCategory::cases() as $option)
                        <option value="{{ $option->value }}" @selected($category === $option)>{{ $option->value }} ({{ $counts[$option->value] ?? 0 }})</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="rounded-lg bg-teal-700 px-4 py-1.5 text-sm font-semibold text-white transition hover:bg-teal-800">Filtrovat</button>
        </form>

        @if ($fabrics->isEmpty())
            <p class="rounded-2xl border border-dashed border-stone-300 bg-white px-5 py-8 text-center text-sm text-stone-500">
                @if ($search !== '' || $collection || $category)
                    Žádná látka neodpovídá filtru.
                @else
                    Zatím tu nejsou žádné látky. Načtete je příkazem <code>php artisan shades:import-pavon-fabrics</code>.
                @endif
            </p>
        @else
            <div class="overflow-x-auto rounded-2xl border border-stone-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-stone-100 text-sm">
                    <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">
                        <tr>
                            <th class="px-4 py-2">Látka</th>
                            <th class="px-4 py-2">Kolekce</th>
                            <th class="px-4 py-2 text-center">Skupina</th>
                            <th class="px-4 py-2">Šíře</th>
                            <th class="px-4 py-2">Gramáž</th>
                            <th class="px-4 py-2">Vlastnosti</th>
                            <th class="px-4 py-2"><span class="sr-only">Akce</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($fabrics as $fabric)
                            <tr @class(['opacity-60' => ! $fabric->is_active])>
                                <td class="px-4 py-2">
                                    <div class="flex items-center gap-3">
                                        @if ($image = $fabric->imageUrl())
                                            <img src="{{ $image }}" alt="" loading="lazy" width="56" height="56" class="h-14 w-14 shrink-0 rounded object-cover">
                                        @else
                                            <span class="h-14 w-14 shrink-0 rounded bg-stone-100"></span>
                                        @endif
                                        <span>
                                            <span class="block font-semibold text-stone-900">{{ $fabric->name }}</span>
                                            @unless ($fabric->is_active)
                                                <span class="rounded bg-stone-200 px-1.5 py-0.5 text-xs font-medium text-stone-600">skrytá</span>
                                            @endunless
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-2 text-stone-600">{{ $fabric->collection->label() }}</td>
                                <td class="px-4 py-2 text-center"><span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-teal-50 font-bold text-teal-800">{{ $fabric->price_category->value }}</span></td>
                                <td class="px-4 py-2 whitespace-nowrap text-stone-600">{{ $fabric->roll_width ? $fabric->roll_width.' cm' : '—' }}</td>
                                <td class="px-4 py-2 whitespace-nowrap text-stone-600">{{ $fabric->spec('Gramáž') ?? '—' }}</td>
                                <td class="px-4 py-2 text-stone-600">
                                    <details>
                                        <summary class="cursor-pointer text-stone-700">{{ $fabric->spec('Světelná propustnost') ?? 'Vlastnosti' }}</summary>
                                        <dl class="mt-2 space-y-0.5 text-xs">
                                            @foreach ($fabric->specs ?? [] as $row)
                                                <div class="flex gap-2"><dt class="w-36 shrink-0 text-stone-500">{{ $row['label'] }}</dt><dd>{{ $row['value'] }}</dd></div>
                                            @endforeach
                                        </dl>
                                    </details>
                                </td>
                                <td class="px-4 py-2 text-right">
                                    <form method="POST" action="{{ route('admin.shade-fabrics.toggle', $fabric) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="rounded-lg border border-stone-300 px-3 py-1 text-xs font-medium text-stone-600 transition hover:bg-stone-100">
                                            {{ $fabric->is_active ? 'Skrýt' : 'Zobrazit' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $fabrics->links() }}
        @endif
    </div>
</x-layouts.app>
