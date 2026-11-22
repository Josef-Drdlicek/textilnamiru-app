<?php

namespace App\Domain\Suppliers\Pavon;

use App\Enums\ShadeFabricCollection;
use App\Models\ShadeFabric;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Import katalogu textilií Pavon do Textilu pro rolety: fotka, název, vlastnosti
 * a cenová skupina A–D. Látky ve výprodeji se nezakládají; látku, která do výprodeje
 * přešla nebo z katalogu zmizela, import skryje. Ručně skrytou látku znovu nezobrazí.
 */
class PavonFabricImporter
{
    /** @var list<string> */
    public array $warnings = [];

    public int $skippedSale = 0;

    public int $hidden = 0;

    public function __construct(
        private PavonClient $client,
        private PavonFabricParser $parser,
    ) {}

    /**
     * @param  (callable(ShadeFabricCollection $collection, int $count): void)|null  $progress
     */
    public function import(bool $withImages = true, ?callable $progress = null): int
    {
        $seen = [];
        $order = 0;

        foreach (ShadeFabricCollection::cases() as $collection) {
            try {
                $fabrics = $this->parser->parse($this->client->page($collection->supplierPath()));
            } catch (Throwable $e) {
                $this->warnings[] = "Kolekci {$collection->label()} se nepodařilo načíst ({$e->getMessage()}).";

                continue;
            }

            $count = 0;
            foreach ($fabrics as $fabric) {
                if (isset($seen[$fabric->code])) {
                    continue;
                }

                if ($fabric->onSale) {
                    $this->skippedSale++;
                    $this->hide($fabric->code);

                    continue;
                }

                if ($fabric->priceCategory === null) {
                    $this->warnings[] = "{$fabric->name}: chybí cenová skupina, látku jsem přeskočil.";

                    continue;
                }

                $seen[$fabric->code] = true;
                $this->save($fabric, $collection, $order++, $withImages);
                $count++;
            }

            $progress && $progress($collection, $count);
        }

        // Látky, které z katalogu zmizely, nejde objednat – skrýt (jen když se načetlo všechno).
        if ($seen !== [] && $this->warnings === []) {
            ShadeFabric::query()->where('supplier', PavonImporter::SUPPLIER)
                ->whereNotIn('code', array_keys($seen))
                ->where('is_active', true)
                ->each(function (ShadeFabric $fabric) {
                    $fabric->update(['is_active' => false]);
                    $this->hidden++;
                });
        }

        return count($seen);
    }

    private function save(PavonFabric $fabric, ShadeFabricCollection $collection, int $order, bool $withImages): void
    {
        $record = ShadeFabric::query()->firstOrNew(['code' => $fabric->code]);

        if (! $record->exists) {
            $record->fill(['slug' => $this->uniqueSlug($fabric->code), 'is_active' => true]);
        }

        $record->fill([
            'supplier' => PavonImporter::SUPPLIER,
            'name' => $fabric->name,
            'collection' => $collection,
            'price_category' => $fabric->priceCategory,
            'roll_width' => $fabric->rollWidth,
            'specs' => $fabric->specs,
            'sort_order' => $order,
        ]);

        $imageChanged = $fabric->imageUrl !== $record->source_image_url;
        $record->source_image_url = $fabric->imageUrl;

        $missing = ! $record->image_path || ! Storage::disk()->exists($record->image_path);
        if ($withImages && $fabric->imageUrl && ($missing || $imageChanged)) {
            try {
                $extension = strtolower(pathinfo(parse_url($fabric->imageUrl, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION)) ?: 'jpg';
                $file = sprintf('shades/%s/latky/%s.%s', PavonImporter::SUPPLIER, $record->slug, $extension);
                Storage::disk()->put($file, $this->client->download($fabric->imageUrl));
                $record->image_path = $file;
            } catch (Throwable $e) {
                $this->warnings[] = "{$fabric->name}: fotku se nepodařilo stáhnout ({$e->getMessage()}).";
            }
        }

        $record->save();
    }

    private function hide(string $code): void
    {
        $updated = ShadeFabric::query()->where('code', $code)->where('is_active', true)->update(['is_active' => false]);
        $this->hidden += $updated;
    }

    private function uniqueSlug(string $code): string
    {
        $base = Str::slug($code) ?: 'latka';
        $slug = $base;
        $i = 2;

        while (ShadeFabric::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
