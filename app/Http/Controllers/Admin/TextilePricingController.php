<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Pricing\ItemPricer;
use App\Enums\TextileCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveTextileProductRequest;
use App\Models\TextileProduct;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Ceník textilu: látky s cenou za metr. Cena s DPH se jen zobrazuje,
 * počítá se ze sazby VAT v ceníku.
 */
class TextilePricingController extends Controller
{
    public function index(Request $request, ItemPricer $pricer): View
    {
        $category = TextileCategory::tryFrom((string) $request->query('kategorie'));

        return view('admin.pricing.textile.index', [
            'products' => TextileProduct::query()
                ->when($category, fn ($query) => $query->where('category', $category))
                ->orderBy('category')
                ->orderBy('name')
                ->get(),
            'category' => $category,
            'vatRate' => $pricer->vatRate(),
        ]);
    }

    public function create(): View
    {
        return view('admin.pricing.textile.create');
    }

    public function store(SaveTextileProductRequest $request): RedirectResponse
    {
        $data = $request->productData();
        $product = TextileProduct::create($data + ['slug' => $this->uniqueSlug($data['name'])]);
        $this->syncColors($product, $request->colorNames());
        $this->syncImage($request, $product);

        return redirect()
            ->route('admin.textile-pricing.index')
            ->with('status', "Produkt „{$product->name}“ byl přidán do ceníku textilu.");
    }

    public function edit(TextileProduct $product): View
    {
        return view('admin.pricing.textile.edit', ['product' => $product]);
    }

    public function update(SaveTextileProductRequest $request, TextileProduct $product): RedirectResponse
    {
        $product->update($request->productData());
        $this->syncColors($product, $request->colorNames());
        $this->syncImage($request, $product);

        return redirect()
            ->route('admin.textile-pricing.index')
            ->with('status', "Produkt „{$product->name}“ byl uložen.");
    }

    /**
     * Barvy podle seznamu názvů: existující (i s fotkou) zůstanou, nové přibudou,
     * vynechané se smažou i s fotkou. Pořadí odpovídá seznamu.
     *
     * @param  list<string>  $names
     */
    private function syncColors(TextileProduct $product, array $names): void
    {
        $existing = $product->colors()->get()->keyBy('name');

        // Pozor: except() u kolekce modelů vyřazuje podle ID, proto reject() podle názvu.
        foreach ($existing->reject(fn ($color) => in_array($color->name, $names, true)) as $removed) {
            if ($removed->image_path) {
                Storage::disk()->delete($removed->image_path);
            }
            $removed->delete();
        }

        foreach ($names as $index => $name) {
            $existing->get($name)?->update(['sort_order' => $index])
                ?? $product->colors()->create(['name' => $name, 'sort_order' => $index]);
        }
    }

    /**
     * Nahraje novou fotku (starou smaže), nebo fotku odebere, když je zaškrtnuté „Odstranit fotku“.
     */
    private function syncImage(SaveTextileProductRequest $request, TextileProduct $product): void
    {
        $disk = Storage::disk();

        if ($request->hasFile('image')) {
            $old = $product->image_path;
            $product->update(['image_path' => $request->file('image')->store('textile')]);

            if ($old) {
                $disk->delete($old);
            }
        } elseif ($request->boolean('remove_image') && $product->image_path) {
            $disk->delete($product->image_path);
            $product->update(['image_path' => null]);
        }
    }

    /**
     * Adresa produktu se odvodí z názvu a po založení se nemění (odkazy zůstanou platné).
     */
    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'produkt';
        $slug = $base;

        for ($suffix = 2; TextileProduct::where('slug', $slug)->exists(); $suffix++) {
            $slug = "{$base}-{$suffix}";
        }

        return $slug;
    }
}
