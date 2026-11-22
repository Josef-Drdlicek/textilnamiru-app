<x-layouts.app :title="$variant->name.' – Ceník rolet'">
    @php
        $widths = $variant->widths();
        $heights = $variant->heights();
    @endphp

    <div class="space-y-6">
        <div>
            <a href="{{ route('admin.shade-pricing.index') }}" class="text-sm font-medium text-teal-800 hover:underline">← Ceník rolet</a>
            <h1 class="mt-2 text-2xl font-bold text-stone-900">{{ $variant->product->name }} – {{ $variant->name }}</h1>
            <p class="mt-1 text-sm text-stone-500">
                Každá cenová skupina látky (A–D) má vlastní tabulku. Ceny bez DPH za kus. Cena v políčku platí <strong>do</strong> uvedené šířky a výšky – rozměr okna se zaokrouhlí nahoru
                na nejbližší řádek a sloupec. Prázdné políčko = cenu u takového rozměru naceníme na poptávku.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.shade-pricing.settings', [$variant, 'skupina' => $category->value]) }}"
              class="grid items-end gap-3 rounded-2xl border border-stone-200 bg-white p-4 shadow-sm sm:grid-cols-6">
            @csrf
            @method('PUT')
            @foreach (['min_width' => 'Šířka od', 'max_width' => 'Šířka do', 'min_height' => 'Výška od', 'max_height' => 'Výška do', 'step' => 'Krok'] as $field => $label)
                <div>
                    <label for="{{ $field }}" class="block text-xs font-medium text-stone-500">{{ $label }} [cm]</label>
                    <input id="{{ $field }}" name="{{ $field }}" type="number" min="5" value="{{ old($field, $variant->{$field}) }}" required
                        class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-1.5 text-right tabular-nums shadow-sm focus:border-teal-500 focus:ring-teal-500">
                </div>
            @endforeach
            <fieldset class="sm:col-span-6">
                <legend class="text-xs font-medium text-stone-500">Látky pro toto provedení (Textil pro rolety)</legend>
                <div class="mt-1 flex flex-wrap gap-x-5 gap-y-1 text-sm">
                    @foreach (\App\Enums\ShadeFabricCollection::cases() as $collection)
                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" name="fabric_collections[]" value="{{ $collection->value }}"
                                @checked(in_array($collection->value, old('fabric_collections', $variant->fabric_collections ?? []), true))
                                class="rounded border-stone-300 text-teal-700 focus:ring-teal-500">
                            {{ $collection->label() }}
                        </label>
                    @endforeach
                </div>
            </fieldset>
            <button type="submit" class="rounded-lg border border-teal-700 px-4 py-1.5 text-sm font-semibold text-teal-800 transition hover:bg-teal-50 sm:col-span-2">
                Uložit nastavení
            </button>
            @if ($errors->any())
                <p class="text-sm text-red-600 sm:col-span-6" role="alert">{{ $errors->first() }}</p>
            @endif
        </form>

        <nav aria-label="Cenová skupina látky" class="flex flex-wrap gap-2">
            @foreach (\App\Enums\ShadePriceCategory::cases() as $option)
                @php $current = $option === $category; @endphp
                <a href="{{ route('admin.shade-pricing.edit', [$variant, 'skupina' => $option->value]) }}"
                   @if ($current) aria-current="page" @endif
                   class="rounded-lg border px-4 py-2 text-sm font-semibold transition {{ $current ? 'border-teal-700 bg-teal-700 text-white' : 'border-stone-300 bg-white text-stone-700 hover:bg-stone-50' }}">
                    {{ $option->label() }}
                    <span class="ml-1 text-xs font-normal {{ $current ? 'text-teal-100' : 'text-stone-500' }}">{{ $filled[$option->value] ?? 0 }} cen · {{ $fabricCount[$option->value] ?? 0 }} látek</span>
                </a>
            @endforeach
        </nav>

        <form method="POST" action="{{ route('admin.shade-pricing.prices', $variant) }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="price_category" value="{{ $category->value }}">

            <div class="max-h-[70vh] overflow-auto rounded-2xl border border-stone-200 bg-white shadow-sm">
                <table class="border-separate border-spacing-0 text-xs">
                    <thead>
                        <tr>
                            <th class="sticky top-0 left-0 z-20 border-r border-b border-stone-200 bg-stone-100 px-2 py-2 text-left font-semibold text-stone-600">výška ↓ / šířka →</th>
                            @foreach ($widths as $width)
                                <th class="sticky top-0 z-10 border-b border-stone-200 bg-stone-50 px-1 py-2 text-center font-semibold text-stone-600 tabular-nums">{{ $width }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($heights as $height)
                            <tr>
                                <th class="sticky left-0 z-10 border-r border-stone-200 bg-stone-50 px-2 py-1 text-right font-semibold text-stone-600 tabular-nums">{{ $height }}</th>
                                @foreach ($widths as $width)
                                    <td class="border-b border-stone-100 p-0.5">
                                        <input
                                            type="number"
                                            name="prices[{{ $height }}][{{ $width }}]"
                                            value="{{ old("prices.$height.$width", isset($prices["{$height}x{$width}"]) ? rtrim(rtrim(number_format($prices["{$height}x{$width}"], 2, '.', ''), '0'), '.') : '') }}"
                                            step="0.01"
                                            min="0"
                                            aria-label="Cena pro šířku {{ $width }} cm a výšku {{ $height }} cm"
                                            class="w-16 rounded border border-stone-200 px-1 py-1 text-right tabular-nums focus:border-teal-500 focus:ring-teal-500"
                                        >
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @error('prices.*.*')
                <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
            @enderror

            <button type="submit" class="mt-4 rounded-lg bg-teal-700 px-5 py-2 font-semibold text-white transition hover:bg-teal-800">
                Uložit ceník – {{ mb_strtolower($category->label()) }}
            </button>
        </form>
    </div>
</x-layouts.app>
