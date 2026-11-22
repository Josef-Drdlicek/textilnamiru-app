<?php

namespace App\Http\Controllers\Web;

use App\Domain\Pricing\RailPricer;
use App\Domain\Pricing\ShadePricer;
use App\Domain\Pricing\TextileProductPricer;
use App\Enums\TextileCategory;
use App\Http\Controllers\Controller;
use App\Models\RailImage;
use App\Models\RailProduct;
use App\Models\ShadeProduct;
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


    /**
     * Zavěšení textilu: zveřejněné kolejnice a dekorační garnýže z Ceníku kolejnic.
     */
    public function zaveseniTextilu(): View
    {
        return view('web.zaveseni-textilu', [
            'groups' => RailProduct::query()->active()->with(['variants', 'images'])->orderBy('sort_order')->get()
                ->groupBy(fn (RailProduct $product) => $product->kind->value),
        ]);
    }

    /**
     * Detail kolejnice/garnýže: sestavy či barvy (?varianta=), fotky, vlastnosti, díly a ceny podle délky a držáku.
     */
    public function zaveseniProdukt(Request $request, RailProduct $product, RailPricer $pricer): View
    {
        // Skrytý (čerstvě importovaný) produkt vidí jen dílna.
        abort_unless($product->is_active || auth()->user()?->isAdmin(), 404);

        $product->load(['variants.prices', 'images']);
        abort_if($product->variants->isEmpty(), 404);

        $variantIndex = $product->variants->has((int) $request->query('varianta', 0)) ? (int) $request->query('varianta', 0) : 0;
        $variant = $product->variants->get($variantIndex);
        $priceFrom = $pricer->lowestPrice($product->variants);

        $photos = $product->images->filter(fn (RailImage $image) => $image->image_path !== null);
        $componentImages = $photos->where('role', RailImage::COMPONENT)
            ->filter(fn (RailImage $image) => $image->rail_variant_id === null || $image->rail_variant_id === $variant->id)
            ->keyBy('source_url');

        return view('web.zaveseni-produkt', [
            'product' => $product,
            'variant' => $variant,
            'variantIndex' => $variantIndex,
            // Garnýž: nejdřív fotka zvolené barvy; kolejnice: nejdřív fotky kolejnice, pak zvolená sestava.
            'images' => $product->kind === \App\Enums\RailKind::Decorative
                ? $photos->where('role', RailImage::PHOTO)->where('rail_variant_id', $variant->id)
                    ->concat($photos->where('role', RailImage::PHOTO)->whereNull('rail_variant_id'))->values()
                : $photos->where('role', RailImage::PHOTO)->whereNull('rail_variant_id')->take(1)
                    ->concat($photos->where('role', RailImage::PHOTO)->where('rail_variant_id', $variant->id))
                    ->concat($photos->where('role', RailImage::PHOTO)->whereNull('rail_variant_id')->skip(1))->values(),
            'components' => collect([...($variant->components ?? []), ...($product->components ?? [])])
                ->map(fn (array $component) => $component + ['url' => $componentImages->get($component['image'])?->url()])
                ->groupBy('group'),
            'features' => array_values(array_unique([...($product->features ?? []), ...($variant->features ?? [])])),
            'specs' => [...($product->specs ?? []), ...($variant->specs ?? [])],
            // Tabulka cen zvolené sestavy: řádek = pásmo délky, sloupec = držák.
            'bandPrices' => $variant->prices->groupBy('length_to')->map(fn ($prices, $lengthTo) => [
                'label' => RailPricer::bandLabel((int) $lengthTo),
                'prices' => $prices->mapWithKeys(fn ($price) => [$price->mounting->value => $pricer->withVat($price->price)]),
            ])->values(),
            'priceFromVat' => $priceFrom !== null ? $pricer->withVat($priceFrom) : null,
        ]);
    }

    public function vypinanyPodhled(): View
    {
        return view('web.vypinany-podhled');
    }

    public function stiniciTechnika(): View
    {
        return view('web.stinici-technika');
    }

    public function rolety(): View
    {
        return view('web.rolety', [
            'products' => ShadeProduct::query()
                ->active()
                ->with(['variants.images'])
                ->orderBy('sort_order')
                ->get(),
        ]);
    }

    /**
     * Detail rolety: varianty (přepínané parametrem ?varianta=), fotky, popis, technické informace.
     */
    public function roleta(Request $request, ShadeProduct $product, ShadePricer $pricer): View
    {
        // Skrytou roletu (čerstvě importovanou) vidí jen dílna, ať si ji může zkontrolovat.
        abort_unless($product->is_active || auth()->user()?->isAdmin(), 404);

        $product->load(['variants.images']);
        abort_if($product->variants->isEmpty(), 404);

        $variantIndex = $product->variants->has((int) $request->query('varianta', 0)) ? (int) $request->query('varianta', 0) : 0;
        $variant = $product->variants->get($variantIndex);
        $priceFrom = $pricer->lowestPrice($variant);

        return view('web.roleta', [
            'product' => $product,
            'variant' => $variant,
            'variantIndex' => $variantIndex,
            'images' => $variant->images->filter(fn ($image) => $image->image_path !== null)->values(),
            'priceFromVat' => $priceFrom !== null ? $pricer->withVat($priceFrom) : null,
        ]);
    }

    public function privacy(): View
    {
        return view('web.gdpr');
    }
}
