<?php

namespace App\Domain\Suppliers\Pavon;

use App\Models\ShadeProduct;
use App\Models\ShadeVariant;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Import rolet Pavon (PavonCatalog) do Ceníku rolet: texty, technické informace,
 * rozměry a fotky každé varianty. Ceny se neimportují – tabulku vyplňuje dílna
 * a opakovaný import ji nemění. Nové produkty jsou skryté.
 */
class PavonImporter
{
    public const string SUPPLIER = 'pavon';

    /** Výchozí rozměry, když je dodavatel neuvádí [cm]. */
    public const int DEFAULT_MIN = 40;

    public const int DEFAULT_MAX_HEIGHT = 250;

    /** @var list<string> */
    public array $warnings = [];

    public function __construct(
        private PavonClient $client,
        private PavonPageParser $parser,
    ) {}

    /**
     * @param  (callable(string $variant): void)|null  $progress
     */
    public function import(bool $withImages = true, ?callable $progress = null): int
    {
        $count = 0;
        $productOrder = 0;

        foreach (PavonCatalog::PRODUCTS as $productName => $variants) {
            $product = ShadeProduct::query()->firstOrCreate(
                ['slug' => Str::slug($productName)],
                ['name' => $productName, 'is_active' => false],
            );
            $product->update(['sort_order' => $productOrder++]);

            $variantOrder = 0;
            foreach ($variants as $variantName => $path) {
                $progress && $progress($variantName);

                try {
                    $page = $this->parser->parse($this->client->page($path));
                    $this->saveVariant($product, $variantName, $path, $page, $variantOrder++, $withImages);
                    $count++;
                } catch (Throwable $e) {
                    $this->warnings[] = "{$variantName}: stránku se nepodařilo načíst ({$e->getMessage()}).";
                }
            }
        }

        return $count;
    }

    private function saveVariant(ShadeProduct $product, string $name, string $path, PavonPage $page, int $order, bool $withImages): void
    {
        /** @var ShadeVariant $variant */
        $variant = $product->variants()->firstOrNew(['supplier_url' => PavonCatalog::BASE_URL.$path]);

        // Rozměry jen při založení – pak je dílna může v ceníku upravit a import je nepřepíše.
        if (! $variant->exists) {
            $variant->fill([
                'min_width' => $page->minWidth ?? self::DEFAULT_MIN,
                'max_width' => $page->maxWidth ?? 250,
                'min_height' => $page->minHeight ?? self::DEFAULT_MIN,
                'max_height' => $page->maxHeight ?? self::DEFAULT_MAX_HEIGHT,
                'step' => 10,
            ]);
        }

        $variant->fabric_collections ??= PavonCatalog::fabricCollections($product->name);

        $variant->fill([
            'name' => $name,
            'supplier' => self::SUPPLIER,
            'subtitle' => $page->subtitle,
            'slogan' => $page->slogan,
            'description' => implode("\n\n", $page->paragraphs),
            'technical' => $page->technical,
            'sort_order' => $order,
        ])->save();

        foreach ($page->images as $index => $image) {
            $record = $variant->images()->firstOrNew(['source_url' => $image['url']]);
            $record->fill(['alt' => $image['alt'] ?: $name, 'sort_order' => $index]);

            if ($withImages && (! $record->image_path || ! Storage::disk()->exists($record->image_path))) {
                try {
                    $extension = strtolower(pathinfo(parse_url($image['url'], PHP_URL_PATH) ?? '', PATHINFO_EXTENSION)) ?: 'jpg';
                    $file = sprintf('shades/%s/%s/%02d.%s', self::SUPPLIER, Str::slug($name), $index + 1, $extension);
                    Storage::disk()->put($file, $this->client->download($image['url']));
                    $record->image_path = $file;
                } catch (Throwable $e) {
                    $this->warnings[] = "{$name}: fotku {$image['url']} se nepodařilo stáhnout ({$e->getMessage()}).";
                }
            }

            $record->save();
        }
    }
}
