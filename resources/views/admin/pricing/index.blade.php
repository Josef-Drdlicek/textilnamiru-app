<x-layouts.app title="Ceník šití">
    <div class="space-y-10">
        <div>
            <h1 class="text-2xl font-bold text-stone-900">Ceník šití</h1>
            <p class="mt-1 text-sm text-stone-500">
                Změny cen platí pro nové kalkulace. Už objednané zakázky mají ceny zamrazené a nezmění se.
            </p>
        </div>

        <section>
            <h2 class="text-lg font-semibold text-stone-900">Úkony a položky</h2>
            @include('admin.pricing._variables', ['variables' => $variables, 'category' => \App\Enums\PricingCategory::Sewing])
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
                                class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-1.5 shadow-sm focus:border-teal-500 focus:ring-teal-500 disabled:bg-stone-100 disabled:text-stone-500">
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

    </div>
</x-layouts.app>
