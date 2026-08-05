<x-layouts.app title="Ceník">
    <div class="space-y-10">
        <div>
            <h1 class="text-2xl font-bold text-stone-900">Ceník</h1>
            <p class="mt-1 text-sm text-stone-500">
                Změny cen platí pro nové kalkulace. Už objednané zakázky mají ceny zamrazené a nezmění se.
            </p>
        </div>

        <section>
            <h2 class="text-lg font-semibold text-stone-900">Úkony a položky</h2>
            <form method="POST" action="{{ route('admin.pricing.variables.update') }}" class="mt-3">
                @csrf
                @method('PUT')

                <div class="overflow-x-auto rounded-2xl border border-stone-200 bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-stone-200 text-sm">
                        <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">
                            <tr>
                                <th class="px-5 py-3">Položka</th>
                                <th class="px-5 py-3">Kód</th>
                                <th class="px-5 py-3 text-right">Hodnota</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @foreach ($variables as $variable)
                                <tr>
                                    <td class="px-5 py-2.5 font-medium text-stone-800">{{ $variable->name }}</td>
                                    <td class="px-5 py-2.5 font-mono text-xs text-stone-400">{{ $variable->code }}</td>
                                    <td class="px-5 py-2.5 text-right">
                                        <input
                                            type="number"
                                            name="values[{{ $variable->code }}]"
                                            value="{{ old('values.'.$variable->code, rtrim(rtrim(number_format($variable->value, 2, '.', ''), '0'), '.')) }}"
                                            step="0.01"
                                            min="0"
                                            required
                                            class="w-28 rounded-lg border border-stone-300 px-2 py-1 text-right tabular-nums shadow-sm focus:border-teal-500 focus:ring-teal-500"
                                        >
                                        <span class="ml-1 text-xs text-stone-400">{{ $variable->code === 'VAT' ? '%' : 'Kč' }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @error('values.*')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror

                <button type="submit" class="mt-3 rounded-lg bg-teal-700 px-5 py-2 font-semibold text-white transition hover:bg-teal-800">
                    Uložit ceník
                </button>
            </form>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-stone-900">Řasící stuhy</h2>
            <p class="mt-1 text-sm text-stone-500">Poměry řasení oddělujte čárkou — např. „1.5, 2, 2.5“.</p>

            <div class="mt-3 space-y-3">
                @foreach ($ribbons as $ribbon)
                    <form method="POST" action="{{ route('admin.pricing.ribbons.update', $ribbon) }}"
                          class="grid items-end gap-3 rounded-2xl border border-stone-200 bg-white p-4 shadow-sm sm:grid-cols-[1fr_140px_1fr_auto_auto]">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="ribbon-name-{{ $ribbon->id }}" class="block text-xs font-medium text-stone-500">Název</label>
                            <input id="ribbon-name-{{ $ribbon->id }}" name="name" type="text" value="{{ $ribbon->name }}" required
                                class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-1.5 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                        </div>

                        <div>
                            <label for="ribbon-price-{{ $ribbon->id }}" class="block text-xs font-medium text-stone-500">Cena za metr</label>
                            <input id="ribbon-price-{{ $ribbon->id }}" name="price_per_meter" type="number" step="0.01" min="0"
                                value="{{ rtrim(rtrim(number_format($ribbon->price_per_meter, 2, '.', ''), '0'), '.') }}" required
                                class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-1.5 text-right tabular-nums shadow-sm focus:border-teal-500 focus:ring-teal-500">
                        </div>

                        <div>
                            <label for="ribbon-ratios-{{ $ribbon->id }}" class="block text-xs font-medium text-stone-500">Povolené poměry</label>
                            <input id="ribbon-ratios-{{ $ribbon->id }}" name="allowed_ratios" type="text"
                                value="{{ implode(', ', $ribbon->allowed_ratios) }}"
                                @if ($ribbon->allows_custom_ratio) disabled placeholder="vlastní poměr zadává zákazník" @endif
                                class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-1.5 shadow-sm focus:border-teal-500 focus:ring-teal-500 disabled:bg-stone-100 disabled:text-stone-400">
                        </div>

                        <label class="flex items-center gap-2 pb-1.5 text-sm text-stone-600">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" @checked($ribbon->is_active)
                                class="h-4 w-4 rounded border-stone-300 text-teal-700 focus:ring-teal-500">
                            Aktivní
                        </label>

                        <button type="submit" class="rounded-lg border border-teal-700 px-4 py-1.5 text-sm font-semibold text-teal-800 transition hover:bg-teal-50">
                            Uložit
                        </button>
                    </form>
                @endforeach
            </div>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-stone-900">Cenová tabulka římských rolet</h2>
            <p class="mt-1 text-sm text-stone-500">Cena podle pásma šířka × výška [Kč].</p>

            <form method="POST" action="{{ route('admin.pricing.blind-prices.update') }}" class="mt-3">
                @csrf
                @method('PUT')

                <div class="overflow-x-auto rounded-2xl border border-stone-200 bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-stone-200 text-sm">
                        <thead class="bg-stone-50 text-xs font-semibold uppercase tracking-wide text-stone-500">
                            <tr>
                                <th class="px-4 py-3 text-left">výška \ šířka</th>
                                @foreach ($bandWidths as $width)
                                    <th class="px-4 py-3 text-right">≤ {{ $width }} cm</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @foreach ($bandHeights as $height)
                                <tr>
                                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">≤ {{ $height }} cm</th>
                                    @foreach ($bandWidths as $width)
                                        @php $band = $bands->first(fn ($b) => $b->max_width === $width && $b->max_height === $height); @endphp
                                        <td class="px-4 py-2.5 text-right">
                                            <input
                                                type="number"
                                                name="prices[{{ $band->id }}]"
                                                value="{{ rtrim(rtrim(number_format($band->price, 2, '.', ''), '0'), '.') }}"
                                                step="1"
                                                min="0"
                                                required
                                                class="w-24 rounded-lg border border-stone-300 px-2 py-1 text-right tabular-nums shadow-sm focus:border-teal-500 focus:ring-teal-500"
                                            >
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <button type="submit" class="mt-3 rounded-lg bg-teal-700 px-5 py-2 font-semibold text-white transition hover:bg-teal-800">
                    Uložit tabulku rolet
                </button>
            </form>
        </section>
    </div>
</x-layouts.app>
