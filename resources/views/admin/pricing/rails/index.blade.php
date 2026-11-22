<x-layouts.app title="Ceník kolejnic">
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-stone-900">Ceník kolejnic</h1>
            <p class="mt-1 text-sm text-stone-500">
                Kolejnice a dekorační garnýže pro Zavěšení textilu. Každá kolejnice má vlastní ceník: vyplňte cenu za kus
                pro každou sestavu (u garnýží barvu), stropní i stěnový držák a pásmo délky po 50 cm až do 6 m (Kč bez DPH).
                Délka se zaokrouhlí nahoru na pásmo – např. 120 cm spadá do pásma 101–150 cm. Prázdné pole = cenu naceníme na poptávku.
            </p>
        </div>

        @php $groups = $products->groupBy(fn ($product) => $product->kind->value); @endphp
        @forelse (collect(\App\Enums\RailKind::cases())->filter(fn ($kind) => $groups->has($kind->value)) as $kind)
            @php $group = $groups->get($kind->value); @endphp
            <h2 class="pt-2 text-lg font-bold text-stone-900">{{ $kind->label() }}</h2>

            @foreach ($group as $product)
                <section id="produkt-{{ $product->slug }}" class="scroll-mt-6 rounded-2xl border border-stone-200 bg-white shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-stone-200 bg-stone-50 px-5 py-3">
                        <h3 class="font-semibold text-stone-900">
                            @if ($product->line)
                                <span class="mr-1 rounded bg-teal-50 px-1.5 py-0.5 text-xs font-semibold text-teal-800">{{ $product->line }}</span>
                            @endif
                            {{ $product->name }}
                            @if ($product->subtitle)
                                <span class="font-normal text-stone-500">· {{ $product->subtitle }}</span>
                            @endif
                            @unless ($product->is_active)
                                <span class="ml-1 rounded bg-stone-200 px-1.5 py-0.5 text-xs font-medium text-stone-600">skrytý</span>
                            @endunless
                        </h3>
                        <div class="flex items-center gap-3 text-sm">
                            <a href="{{ route('web.zaveseni.show', $product) }}" class="font-medium text-teal-800 hover:underline" target="_blank">Náhled na webu</a>
                            <form method="POST" action="{{ route('admin.rail-pricing.toggle', $product) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="rounded-lg border border-stone-300 px-3 py-1 font-medium text-stone-600 transition hover:bg-stone-100">
                                    {{ $product->is_active ? 'Skrýt z webu' : 'Zobrazit na webu' }}
                                </button>
                            </form>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.rail-pricing.update', $product) }}" class="p-4">
                        @csrf
                        @method('PUT')
                        <div class="overflow-x-auto">
                            <table class="border-separate border-spacing-0 text-xs">
                                <thead>
                                    <tr>
                                        <th class="sticky left-0 z-10 bg-white px-2 py-2 text-left font-semibold text-stone-600">{{ $product->variantLabel() }} / zavěšení</th>
                                        @foreach ($bands as $band)
                                            <th class="px-1 py-2 text-center font-semibold whitespace-nowrap text-stone-600 tabular-nums">{{ \App\Domain\Pricing\RailPricer::bandLabel($band) }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($product->variants as $variant)
                                        @foreach ($product->mountingOptions() as $mounting)
                                            @php
                                                $prices = $variant->prices->where('mounting', $mounting)->pluck('price', 'length_to');
                                                $label = trim(($variant->code ? $variant->code.' ' : '').$variant->name).' – '.mb_strtolower($mounting->label());
                                            @endphp
                                            <tr @class(['border-t-2 border-stone-200' => $loop->first && ! $loop->parent->first])>
                                                <th class="sticky left-0 z-10 bg-white px-2 py-1 text-left font-medium whitespace-nowrap text-stone-800">
                                                    @if ($loop->first)
                                                        <span class="block">{{ $variant->code ? $variant->code.' · ' : '' }}{{ $variant->name }}</span>
                                                    @endif
                                                    <span class="block pl-3 text-xs font-normal text-stone-500">{{ $mounting->label() }}</span>
                                                </th>
                                                @foreach ($bands as $band)
                                                    <td class="p-0.5">
                                                        <input
                                                            type="number"
                                                            name="prices[{{ $variant->id }}][{{ $mounting->value }}][{{ $band }}]"
                                                            value="{{ old("prices.{$variant->id}.{$mounting->value}.{$band}", isset($prices[$band]) ? rtrim(rtrim(number_format($prices[$band], 2, '.', ''), '0'), '.') : '') }}"
                                                            step="0.01"
                                                            min="0"
                                                            aria-label="{{ $label }}, délka {{ \App\Domain\Pricing\RailPricer::bandLabel($band) }}"
                                                            class="w-20 rounded border border-stone-200 px-1 py-1 text-right tabular-nums focus:border-teal-500 focus:ring-teal-500"
                                                        >
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <button type="submit" class="mt-3 rounded-lg bg-teal-700 px-4 py-1.5 text-sm font-semibold text-white transition hover:bg-teal-800">
                            Uložit ceny – {{ $product->name }}
                        </button>
                    </form>
                </section>
            @endforeach
        @empty
            <p class="rounded-2xl border border-dashed border-stone-300 bg-white px-5 py-8 text-center text-sm text-stone-500">
                Ceník kolejnic je zatím prázdný. Kolejnice a garnýže načtete příkazem <code>php artisan rails:import-detede</code>.
            </p>
        @endforelse

        @error('prices.*.*.*')
            <p class="text-sm text-red-600" role="alert">{{ $message }}</p>
        @enderror
    </div>
</x-layouts.app>
