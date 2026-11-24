<x-layouts.app title="Ceník rolet">
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-stone-900">Ceník rolet</h1>
            <p class="mt-1 text-sm text-stone-500">
                Cena rolety se řídí rozměrem okna a cenovou skupinou látky. U každé varianty vyplňte tabulku šířka × výška
                pro skupiny A–D (Kč bez DPH za kus).
            </p>
        </div>

        @forelse ($products as $product)
            <section class="overflow-x-auto rounded-2xl border border-stone-200 bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-stone-200 bg-stone-50 px-5 py-3">
                    <h2 class="font-semibold text-stone-900">
                        {{ $product->name }}
                        @unless ($product->is_active)
                            <span class="ml-1 rounded bg-stone-200 px-1.5 py-0.5 text-xs font-medium text-stone-600">skrytá</span>
                        @endunless
                    </h2>
                    <div class="flex items-center gap-3 text-sm">
                        <a href="{{ route('web.rolety.show', $product->slug) }}" class="font-medium text-teal-800 hover:underline" target="_blank">Náhled na webu</a>
                        <form method="POST" action="{{ route('admin.shade-pricing.toggle', $product) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="rounded-lg border border-stone-300 px-3 py-1 font-medium text-stone-600 transition hover:bg-stone-100">
                                {{ $product->is_active ? 'Skrýt z webu' : 'Zobrazit na webu' }}
                            </button>
                        </form>
                    </div>
                </div>
                <table class="min-w-full divide-y divide-stone-100 text-sm">
                    <thead class="text-left text-xs font-semibold uppercase tracking-wide text-stone-500">
                        <tr>
                            <th class="px-5 py-2">Varianta</th>
                            <th class="px-5 py-2">Šířka</th>
                            <th class="px-5 py-2">Výška</th>
                            @foreach (\App\Enums\ShadePriceCategory::cases() as $category)
                                <th class="px-3 py-2 text-right">Sk. {{ $category->value }}</th>
                            @endforeach
                            <th class="px-5 py-2"><span class="sr-only">Akce</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($product->variants as $variant)
                            @php $cells = count($variant->widths()) * count($variant->heights()); @endphp
                            <tr>
                                <td class="px-5 py-2.5 font-medium text-stone-800">{{ $variant->name }}</td>
                                <td class="px-5 py-2.5 text-stone-600">{{ $variant->min_width }}–{{ $variant->max_width }} cm</td>
                                <td class="px-5 py-2.5 text-stone-600">{{ $variant->min_height }}–{{ $variant->max_height }} cm</td>
                                @foreach (\App\Enums\ShadePriceCategory::cases() as $category)
                                    @php $count = $filled[$variant->id][$category->value] ?? 0; @endphp
                                    <td class="px-3 py-2.5 text-right tabular-nums {{ $count ? 'text-stone-700' : 'text-stone-400' }}">{{ $count }} / {{ $cells }}</td>
                                @endforeach
                                <td class="px-5 py-2.5 text-right">
                                    <a href="{{ route('admin.shade-pricing.edit', $variant) }}" class="font-medium text-teal-800 hover:underline">Upravit ceny</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        @empty
            <p class="rounded-2xl border border-dashed border-stone-300 bg-white px-5 py-8 text-center text-sm text-stone-500">
                Ceník rolet je zatím prázdný. Rolety načtete příkazem <code>php artisan shades:import-pavon</code>.
            </p>
        @endforelse
    </div>
</x-layouts.app>
