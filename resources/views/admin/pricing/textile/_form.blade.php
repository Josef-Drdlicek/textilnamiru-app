@php $product ??= null; @endphp

<div class="grid gap-5 sm:grid-cols-2">
    <x-form.field name="name" label="Název" class="sm:col-span-2">
        <x-form.input name="name" type="text" :value="old('name', $product?->name)" required />
    </x-form.field>

    <x-form.field name="category" label="Kategorie">
        <x-form.select name="category" required>
            @foreach (\App\Enums\TextileCategory::cases() as $category)
                <option value="{{ $category->value }}" @selected(old('category', $product?->category?->value) === $category->value)>{{ $category->label() }}</option>
            @endforeach
        </x-form.select>
    </x-form.field>

    <x-form.field name="price_per_meter" label="Cena bez DPH [Kč/m]" hint="Bez ceny jde produkt na webu jen poptat.">
        <x-form.input name="price_per_meter" type="number" step="0.01" min="0" :value="old('price_per_meter', $product?->price_per_meter)" />
    </x-form.field>

    <x-form.field name="roll_height" label="Výška role [cm]" hint="Největší hotová výška výrobku je o 5 cm menší.">
        <x-form.input name="roll_height" type="number" min="50" max="600" :value="old('roll_height', $product?->roll_height)" required />
    </x-form.field>

    <x-form.field name="delivery_days" label="Dodací doba [dny]">
        <x-form.input name="delivery_days" type="number" min="0" max="365" :value="old('delivery_days', $product?->delivery_days)" />
    </x-form.field>

    <x-form.field name="material" label="Materiál">
        <x-form.input name="material" type="text" :value="old('material', $product?->material)" placeholder="např. 100% polyester" />
    </x-form.field>

    <x-form.field name="shrinkage" label="Srážení">
        <x-form.input name="shrinkage" type="text" :value="old('shrinkage', $product?->shrinkage)" placeholder="např. ±1 %" />
    </x-form.field>

    <x-form.field name="colors" label="Barvy" hint="Oddělte čárkou, např. „Bílá, Přírodní, Stříbrná“." class="sm:col-span-2">
        <x-form.input name="colors" type="text" :value="old('colors', $product?->colors->pluck('name')->implode(', '))" required />
        @if ($product?->colors->contains(fn ($color) => $color->image_path))
            <ul class="mt-3 grid grid-cols-4 gap-2 sm:grid-cols-6">
                @foreach ($product->colors as $color)
                    <li class="text-center text-xs text-stone-600">
                        @if ($url = $color->imageUrl())
                            <img src="{{ $url }}" alt="{{ $color->label() }}" loading="lazy" class="aspect-square w-full rounded-md border border-stone-200 object-cover">
                        @else
                            <span class="flex aspect-square w-full items-center justify-center rounded-md border border-dashed border-stone-300 text-stone-400">bez fotky</span>
                        @endif
                        <span class="mt-1 block">{{ $color->name }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-form.field>

    <x-form.field name="image" label="Fotka" :hint="'JPG, PNG nebo WebP, nejvýš '.(\App\Http\Requests\Admin\SaveTextileProductRequest::MAX_IMAGE_KB / 1024).' MB. Ukáže se na webu u produktu.'" class="sm:col-span-2">
        <div class="flex flex-wrap items-start gap-4">
            @if ($imageUrl = $product?->imageUrl())
                <img src="{{ $imageUrl }}" alt="Současná fotka" class="h-28 w-28 rounded-lg border border-stone-200 object-cover">
            @endif
            <div class="space-y-2">
                <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp"
                    class="block text-sm text-stone-600 file:mr-3 file:rounded-lg file:border-0 file:bg-teal-50 file:px-4 file:py-2 file:font-semibold file:text-teal-800 hover:file:bg-teal-100">
                @if ($imageUrl)
                    <label class="flex items-center gap-2 text-sm text-stone-700">
                        <input type="hidden" name="remove_image" value="0">
                        <input type="checkbox" name="remove_image" value="1" class="h-4 w-4 rounded border-stone-300 text-teal-700 focus:ring-teal-500">
                        Odstranit fotku
                    </label>
                @endif
            </div>
        </div>
    </x-form.field>

    <x-form.field name="description" label="Popis" class="sm:col-span-2">
        <textarea id="description" name="description" rows="4"
            class="block w-full rounded-lg border border-stone-300 px-3 py-2 shadow-sm focus:border-teal-500 focus:ring-teal-500">{{ old('description', $product?->description) }}</textarea>
    </x-form.field>

    <div class="sm:col-span-2">
        <label class="flex items-center gap-2 text-sm text-stone-700">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product?->is_active ?? true))
                class="h-4 w-4 rounded border-stone-300 text-teal-700 focus:ring-teal-500">
            Zobrazit na webu
        </label>
    </div>
</div>

<div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-stone-100 pt-5">
    <a href="{{ route('admin.textile-pricing.index') }}" class="rounded-lg border border-stone-300 px-4 py-2 text-sm font-medium text-stone-600 transition hover:bg-stone-100">
        ← Zpět na ceník textilu
    </a>
    <button type="submit" class="rounded-lg bg-teal-700 px-6 py-2.5 font-semibold text-white transition hover:bg-teal-800">
        {{ $product ? 'Uložit změny' : 'Přidat produkt' }}
    </button>
</div>
