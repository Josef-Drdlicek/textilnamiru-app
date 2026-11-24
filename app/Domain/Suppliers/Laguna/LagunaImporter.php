<?php

namespace App\Domain\Suppliers\Laguna;

use App\Domain\Suppliers\SupplierImage;
use App\Models\TextileProduct;
use App\Models\TextileProductColor;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Import katalogu Laguna Textil (záclony, dekorační látky = závěsy) do Ceníku textilu.
 *
 * Označení „11101/180/03“ = název / výška / barva. Jeden produkt = název + výška,
 * barvy pod ním s vlastní fotkou. Položky bez barvy („5857/45“) jsou samostatné produkty.
 *
 * Nové produkty jsou skryté a bez ceny (dílna je doplní a zveřejní).
 * Opakovaný import: aktualizuje specifikace a barvy od dodavatele, ale nesahá na
 * název, cenu, popis, viditelnost, výšku role a materiál (dílna je mohla opravit),
 * na barvy přidané ručně ani na barvy, které dílna smazala (excluded_colors).
 */
class LagunaImporter
{
    public const string SUPPLIER = 'laguna';

    public const int MIN_HEIGHT = 30;

    public const int MAX_HEIGHT = 600;

    /** Název jediné barvy u látek, které dodavatel nemá v barvách. */
    public const string SINGLE_COLOR = 'Základní';

    /** @var list<string> */
    public array $warnings = [];

    public function __construct(
        private LagunaClient $client,
        private LagunaDetailParser $parser,
    ) {}

    /**
     * Seskupí položky katalogu do produktů (a u každé skupiny načte detail kvůli parametrům).
     *
     * @param  list<LagunaItem>  $items
     * @param  (callable(int $done, int $total): void)|null  $progress
     * @return list<LagunaProductData>
     */
    public function group(array $items, ?callable $progress = null, LagunaCatalog $catalog = LagunaCatalog::Curtains): array
    {
        $category = $catalog->textileCategory();
        $groups = [];
        foreach ($items as $item) {
            $groups[$item->groupKey()][] = $item;
        }

        $products = [];
        $done = 0;

        foreach ($groups as $key => $groupItems) {
            // Jedna nedostupná stránka nesmí po minutách stahování shodit celý import.
            try {
                $detail = $this->parser->parse($this->client->detailHtml($groupItems[0]->link));
            } catch (Throwable $e) {
                $this->warnings[] = "{$key}: detail se nepodařilo načíst, produkt jsem přeskočil ({$e->getMessage()}).";
                $progress && $progress(++$done, count($groups));

                continue;
            }

            $segments = $groupItems[0]->segments();
            $secondLast = $segments[count($segments) - 2] ?? null;

            // „V/207/45“, „5857/45“: poslední část je výška (potvrdí detail), barva chybí → každá položka = produkt.
            if ($detail['height'] !== null
                && $this->isHeight($groupItems[0]->lastSegment())
                && (int) $groupItems[0]->lastSegment() === $detail['height']) {
                foreach ($groupItems as $item) {
                    $height = (int) $item->lastSegment();
                    $base = $this->baseName($item, array_slice($item->segments(), 0, -1));
                    $products[] = new LagunaProductData("{$base}/{$height}", $base, $height, [self::SINGLE_COLOR => $item->imageUrl], $detail['material'], $detail['specs'], $category);
                }
            } else {
                $hasHeightSegment = $this->isHeight($secondLast);
                $height = $hasHeightSegment ? (int) $secondLast : ($detail['height'] ?? 0);
                $nameSegments = array_slice($segments, 0, $hasHeightSegment ? -2 : -1);
                $base = $this->baseName($groupItems[0], $nameSegments);

                $colors = [];
                foreach ($groupItems as $item) {
                    $colors[$this->uniqueColor($colors, $item->lastSegment())] = $item->imageUrl;
                }
                ksort($colors, SORT_NATURAL);

                if ($height === 0) {
                    $this->warnings[] = "{$key}: nepodařilo se zjistit výšku.";
                }

                $products[] = new LagunaProductData("{$base}/{$height}", $base, $height, $colors, $detail['material'], $detail['specs'], $category);
            }

            $progress && $progress(++$done, count($groups));
        }

        return $products;
    }

    /**
     * Uloží produkt a barvy; fotky stáhne, jen když chybí nebo se u dodavatele změnily.
     */
    public function save(LagunaProductData $data, bool $withImages = true): TextileProduct
    {
        $product = TextileProduct::query()->firstOrNew(['category' => $data->category, 'supplier_code' => $data->code]);

        if (! $product->exists) {
            $product->fill([
                'name' => $data->name(),
                'slug' => $this->uniqueSlug($data->name()),
                'supplier' => self::SUPPLIER,
                'price_per_meter' => null,
                'is_active' => false,
            ]);
        }

        $product->roll_height ??= $data->height;
        $product->material ??= $data->material;
        $product->specs = $data->specs;
        $product->save();

        $existing = $product->colors()->get()->keyBy('name');
        $excluded = $product->excluded_colors ?? [];
        $order = 0;

        foreach ($data->colors as $name => $imageUrl) {
            if (in_array((string) $name, $excluded, true)) {
                continue;
            }

            /** @var TextileProductColor $color */
            $color = $existing->get($name) ?? $product->colors()->make(['name' => $name]);
            $color->sort_order = $order++;

            if ($withImages && ($color->source_image_url !== $imageUrl || ! $color->image_path || ! Storage::disk()->exists($color->image_path))) {
                $this->downloadImage($product, $color, $imageUrl);
            }

            $color->source_image_url = $imageUrl;
            $color->save();
        }

        // Barvy, které dodavatel už nemá (jen importované, ruční zůstanou).
        // reject() podle názvu – except() u kolekce modelů by vyřazoval podle ID.
        $supplierNames = array_map('strval', array_keys($data->colors));
        foreach ($existing->reject(fn (TextileProductColor $color) => in_array($color->name, $supplierNames, true)) as $gone) {
            if ($gone->source_image_url !== null) {
                $gone->image_path && Storage::disk()->delete($gone->image_path);
                $gone->delete();
            }
        }

        return $product;
    }

    private function downloadImage(TextileProduct $product, TextileProductColor $color, string $url): void
    {
        try {
            $path = SupplierImage::store(
                sprintf('textile/%s/%s/%s', self::SUPPLIER, $product->slug, Str::slug($color->name) ?: 'barva'),
                $this->client->download($url),
            );

            if ($color->image_path && $color->image_path !== $path) {
                Storage::disk()->delete($color->image_path);
            }

            $color->image_path = $path;
        } catch (Throwable $e) {
            $this->warnings[] = "{$product->supplier_code} barva {$color->name}: fotku se nepodařilo stáhnout ({$e->getMessage()}).";
        }
    }

    private function isHeight(?string $segment): bool
    {
        return $segment !== null
            && ctype_digit($segment)
            && (int) $segment >= self::MIN_HEIGHT
            && (int) $segment <= self::MAX_HEIGHT;
    }

    /**
     * @param  list<string>  $segments
     */
    private function baseName(LagunaItem $item, array $segments): string
    {
        return implode('/', $segments).($item->suffix() ? ' '.$item->suffix() : '');
    }

    /**
     * Dodavatel občas uvede stejný kód barvy dvakrát (např. ALISSA/300/01) – druhý dostane „01 (2)“.
     *
     * @param  array<string, string>  $colors
     */
    private function uniqueColor(array $colors, string $code): string
    {
        $name = $code;
        for ($i = 2; isset($colors[$name]); $i++) {
            $name = "{$code} ({$i})";
        }

        return $name;
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'zaclona';
        $slug = $base;

        for ($suffix = 2; TextileProduct::where('slug', $slug)->exists(); $suffix++) {
            $slug = "{$base}-{$suffix}";
        }

        return $slug;
    }
}
