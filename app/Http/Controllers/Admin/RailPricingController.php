<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Pricing\RailPricer;
use App\Enums\RailMounting;
use App\Http\Controllers\Controller;
use App\Models\RailProduct;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Ceník kolejnic: kolejnice a dekorační garnýže (Zavěšení textilu). Cena se zadává
 * pro každou sestavu (u garnýží barvu) a způsob zavěšení (stropní / stěnový držák)
 * podle délky v pásmech po 50 cm do 6 m, Kč bez DPH za kus.
 */
class RailPricingController extends Controller
{
    public function index(): View
    {
        return view('admin.pricing.rails.index', [
            'products' => RailProduct::query()->with(['variants.prices'])->orderBy('sort_order')->get(),
            'bands' => RailPricer::bands(),
        ]);
    }

    /**
     * Uloží ceny všech variant produktu: vyplněná pásma přepíše, prázdná smaže.
     */
    public function update(Request $request, RailProduct $product): RedirectResponse
    {
        $request->validate([
            'prices' => ['array'],
            'prices.*' => ['array'],
            'prices.*.*' => ['array'],
            'prices.*.*.*' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
        ], attributes: ['prices.*.*.*' => 'cena']);

        $bands = RailPricer::bands();
        $mountings = array_map(fn (RailMounting $mounting) => $mounting->value, $product->mountingOptions());
        $variantIds = $product->variants()->pluck('id')->all();
        $rows = [];

        // prices[sestava][držák][pásmo] = cena
        foreach ($request->input('prices', []) as $variantId => $byMounting) {
            if (! in_array((int) $variantId, $variantIds, true)) {
                continue;
            }

            foreach ($byMounting as $mounting => $cells) {
                if (! in_array($mounting, $mountings, true)) {
                    continue;
                }

                foreach ($cells as $lengthTo => $price) {
                    if ($price === null || $price === '' || ! in_array((int) $lengthTo, $bands, true)) {
                        continue;
                    }

                    $rows[] = [
                        'rail_variant_id' => (int) $variantId,
                        'mounting' => $mounting,
                        'length_to' => (int) $lengthTo,
                        'price' => (float) $price,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        DB::transaction(function () use ($variantIds, $rows) {
            DB::table('rail_prices')->whereIn('rail_variant_id', $variantIds)->delete();
            foreach (array_chunk($rows, 200) as $chunk) {
                DB::table('rail_prices')->insert($chunk);
            }
        });

        return redirect()
            ->to(route('admin.rail-pricing.index').'#produkt-'.$product->slug)
            ->with('status', "Ceník „{$product->name}“ byl uložen (".count($rows).' cen).');
    }

    public function toggle(RailProduct $product): RedirectResponse
    {
        $product->update(['is_active' => ! $product->is_active]);

        return redirect()
            ->to(route('admin.rail-pricing.index').'#produkt-'.$product->slug)
            ->with('status', "„{$product->name}“ ".($product->is_active ? 'je teď vidět na webu.' : 'je skrytý.'));
    }
}
