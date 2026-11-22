<?php

namespace App\Http\Controllers\Web;

use App\Domain\Pricing\TextileProductPricer;
use App\Enums\TextileCategory;
use App\Http\Controllers\Controller;
use App\Models\TextileProduct;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        return view('web.home');
    }

    public function kalkulacka(Request $request): View|RedirectResponse
    {
        // Starý odkaz /kalkulacka?typ=… mířil rovnou do kalkulačky šití.
        if ($request->filled('typ')) {
            return redirect()->route('web.kalkulacka.sivani', $request->only('typ'));
        }

        return view('web.kalkulacka');
    }

    public function kalkulackaSivani(): View
    {
        return view('web.kalkulacka-sivani');
    }

    public function kalkulackaStropy(): View
    {
        return view('web.kalkulacka-stropy');
    }

    public function realizace(): View
    {
        return view('web.realizace');
    }

    public function zaclonyAZavesy(): View
    {
        return view('web.zaclony-a-zavesy');
    }

    public function zaclony(): View
    {
        return view('web.zaclony', ['products' => $this->textileProducts(TextileCategory::Curtain)]);
    }

    public function zavesy(): View
    {
        return view('web.zavesy', ['products' => $this->textileProducts(TextileCategory::Drape)]);
    }

    public function zaclona(TextileProduct $product, TextileProductPricer $pricer): View
    {
        return $this->textileProductPage($product, TextileCategory::Curtain, $pricer);
    }

    public function zaves(TextileProduct $product, TextileProductPricer $pricer): View
    {
        return $this->textileProductPage($product, TextileCategory::Drape, $pricer);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, TextileProduct>
     */
    private function textileProducts(TextileCategory $category)
    {
        return TextileProduct::query()
            ->active()
            ->where('category', $category)
            ->with('colors')
            ->orderBy('name')
            ->get();
    }

    /**
     * Produktová stránka látky: fotka, popis, parametry a objednávka šití na míru.
     */
    private function textileProductPage(TextileProduct $product, TextileCategory $category, TextileProductPricer $pricer): View
    {
        // Skrytý produkt (např. čerstvě importovaný) vidí jen dílna, ať si ho může zkontrolovat.
        abort_unless($product->category === $category, 404);
        abort_unless($product->is_active || auth()->user()?->isAdmin(), 404);

        $product->load('colors');
        $vatRate = $pricer->vatRate();

        return view('web.textile-product', [
            'product' => $product,
            'colorsWithImage' => $product->colors->filter(fn ($color) => $color->image_path !== null)->values(),
            'specs' => $this->textileSpecs($product),
            'pricePerMeterVat' => $product->hasPrice() ? round($product->price_per_meter * (100 + $vatRate)) / 100 : null,
        ]);
    }

    /**
     * Parametry pro tabulku na produktové stránce: základní pole + další specifikace z importu.
     *
     * @return array<string, string>
     */
    private function textileSpecs(TextileProduct $product): array
    {
        $specs = array_filter([
            'Materiál' => $product->material,
            $product->category->rollLabel() => $product->roll_height.' cm',
            'Srážení' => $product->shrinkage,
        ]);

        foreach ($product->specs ?? [] as [$label, $value]) {
            $specs[$label] ??= $value;
        }

        $specs['Barvy'] = (string) $product->colors->count();

        if ($product->delivery_days) {
            $specs['Dodací doba'] = $product->delivery_days.' dní';
        }

        return $specs;
    }


    public function zaveseniTextilu(): View
    {
        return view('web.zaveseni-textilu');
    }

    public function vypinanyPodhled(): View
    {
        return view('web.vypinany-podhled');
    }

    public function stiniciTechnika(): View
    {
        return view('web.stinici-technika');
    }

    public function privacy(): View
    {
        return view('web.gdpr');
    }
}
