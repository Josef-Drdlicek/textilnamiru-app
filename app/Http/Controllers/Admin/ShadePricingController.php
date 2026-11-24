<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ShadeFabricCollection;
use App\Enums\ShadePriceCategory;
use App\Http\Controllers\Controller;
use App\Models\ShadeProduct;
use App\Models\ShadeVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Ceník rolet: cena podle rozměru okna. Každá varianta má pro každou cenovou
 * skupinu látky (A–D) tabulku šířka × výška (krok zpravidla 10 cm);
 * cena v buňce platí do dané šířky a výšky, bez DPH.
 */
class ShadePricingController extends Controller
{
    /** Strop velikosti tabulky (formulář s víc políčky by PHP neodeslal celý). */
    public const int MAX_CELLS = 900;

    public function index(): View
    {
        // Počty vyplněných cen: [varianta][skupina] => počet
        $filled = [];
        foreach (DB::table('shade_prices')->selectRaw('shade_variant_id, price_category, count(*) as total')->groupBy('shade_variant_id', 'price_category')->get() as $row) {
            $filled[$row->shade_variant_id][$row->price_category] = (int) $row->total;
        }

        return view('admin.pricing.shades.index', [
            'products' => ShadeProduct::query()->with('variants')->orderBy('sort_order')->get(),
            'filled' => $filled,
        ]);
    }

    public function edit(Request $request, ShadeVariant $variant): View
    {
        $category = ShadePriceCategory::tryFrom((string) $request->query('skupina')) ?? ShadePriceCategory::A;

        return view('admin.pricing.shades.edit', [
            'variant' => $variant->load('product'),
            'category' => $category,
            'prices' => $variant->prices()->where('price_category', $category)->get()->mapWithKeys(fn ($p) => ["{$p->height}x{$p->width}" => $p->price]),
            'filled' => $variant->prices()->selectRaw('price_category, count(*) as total')->groupBy('price_category')->pluck('total', 'price_category'),
            'fabricCount' => $variant->fabrics()->countBy(fn ($fabric) => $fabric->price_category->value),
        ]);
    }

    /**
     * Uloží celou tabulku jedné cenové skupiny: vyplněné buňky přepíše, prázdné smaže.
     */
    public function updatePrices(Request $request, ShadeVariant $variant): RedirectResponse
    {
        $request->validate([
            'price_category' => ['required', Rule::enum(ShadePriceCategory::class)],
            'prices' => ['array'],
            'prices.*' => ['array'],
            'prices.*.*' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
        ], attributes: ['prices.*.*' => 'cena']);

        $category = ShadePriceCategory::from($request->input('price_category'));
        $widths = $variant->widths();
        $heights = $variant->heights();
        $rows = [];

        foreach ($request->input('prices', []) as $height => $cells) {
            foreach ($cells as $width => $price) {
                if ($price === null || $price === '' || ! in_array((int) $height, $heights, true) || ! in_array((int) $width, $widths, true)) {
                    continue;
                }

                $rows[] = [
                    'shade_variant_id' => $variant->id,
                    'price_category' => $category->value,
                    'width' => (int) $width,
                    'height' => (int) $height,
                    'price' => (float) $price,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::transaction(function () use ($variant, $category, $rows) {
            $variant->prices()->where('price_category', $category)->delete();
            foreach (array_chunk($rows, 200) as $chunk) {
                DB::table('shade_prices')->insert($chunk);
            }
        });

        return redirect()
            ->route('admin.shade-pricing.edit', [$variant, 'skupina' => $category->value])
            ->with('status', 'Ceník „'.$variant->name.'“ – '.mb_strtolower($category->label()).' byl uložen ('.count($rows).' cen).');
    }

    /**
     * Rozsah a krok tabulky. Ceny mimo novou mřížku zůstanou uložené (návrat
     * k původnímu kroku je vrátí), ale nezobrazí se a ShadePricer s nimi nepočítá.
     */
    public function updateSettings(Request $request, ShadeVariant $variant): RedirectResponse
    {
        $data = $request->validate([
            'min_width' => ['required', 'integer', 'min:10', 'max:1000'],
            'max_width' => ['required', 'integer', 'gte:min_width', 'max:1000'],
            'min_height' => ['required', 'integer', 'min:10', 'max:1000'],
            'max_height' => ['required', 'integer', 'gte:min_height', 'max:1000'],
            'step' => ['required', 'integer', 'min:5', 'max:100'],
            'fabric_collections' => ['required', 'array', 'min:1'],
            'fabric_collections.*' => [Rule::enum(ShadeFabricCollection::class)],
        ], attributes: [
            'fabric_collections' => 'látky',
            'min_width' => 'nejmenší šířka', 'max_width' => 'největší šířka',
            'min_height' => 'nejmenší výška', 'max_height' => 'největší výška', 'step' => 'krok',
        ]);

        $cells = (intdiv($data['max_width'] - $data['min_width'], $data['step']) + 2)
            * (intdiv($data['max_height'] - $data['min_height'], $data['step']) + 2);

        if ($cells > self::MAX_CELLS) {
            return back()->withErrors(['step' => 'Tabulka by měla přes '.self::MAX_CELLS.' políček – zvětšete krok nebo zmenšete rozsah.'])->withInput();
        }

        $variant->update($data);

        return redirect()
            ->route('admin.shade-pricing.edit', [$variant, 'skupina' => $request->query('skupina', 'A')])
            ->with('status', 'Nastavení provedení bylo uloženo.');
    }

    public function toggle(ShadeProduct $product): RedirectResponse
    {
        $product->update(['is_active' => ! $product->is_active]);

        return redirect()
            ->route('admin.shade-pricing.index')
            ->with('status', "„{$product->name}“ ".($product->is_active ? 'je teď vidět na webu.' : 'je skrytá.'));
    }
}
