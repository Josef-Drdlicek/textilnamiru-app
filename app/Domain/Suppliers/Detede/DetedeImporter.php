<?php

namespace App\Domain\Suppliers\Detede;

use App\Enums\RailKind;
use App\Models\RailImage;
use App\Models\RailPrice;
use App\Models\RailProduct;
use App\Models\RailVariant;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Import kolejnic a dekoračních garnýží DeTeDe do Ceníku kolejnic. Každý typ kolejnice
 * je samostatný produkt (varianty = sestavy), u garnýží jsou varianty barvy.
 *
 * Web dodavatele je skládaný obrázky bez pevné struktury, proto texty a odkazy
 * na fotky leží v ručně přepsaném katalogu database/data/detede-catalog.json
 * a import z něj jen zakládá produkty a stahuje fotky. Ceny ani viditelnost nemění,
 * nové produkty jsou skryté.
 */
class DetedeImporter
{
    public const string SUPPLIER = 'detede';

    public const string IMAGE_BASE = 'https://static.wixstatic.com/media/4586fe_';

    /** Fotky stahujeme zmenšené na nejvýš 1600 px (originály mívají 3840 px). */
    private const string IMAGE_TRANSFORM = '/v1/fit/w_1600,h_1600,q_90/';

    private const int MAX_IMAGE_BYTES = 12 * 1024 * 1024;

    /** @var list<string> */
    public array $warnings = [];

    public int $images = 0;

    /** Kolik starých produktů import odstranil (např. dřívější řady „N-Line“). */
    public int $removed = 0;

    /** @var array<string, string> stažené fotky v tomto běhu: zdroj => cesta */
    private array $downloaded = [];

    public static function catalogPath(): string
    {
        return database_path('data/detede-catalog.json');
    }

    /**
     * @param  (callable(string $product): void)|null  $progress
     */
    public function import(bool $withImages = true, ?callable $progress = null, ?string $catalogPath = null): int
    {
        $catalog = json_decode((string) file_get_contents($catalogPath ?? self::catalogPath()), true, flags: JSON_THROW_ON_ERROR);
        $count = 0;
        $slugs = [];

        foreach ($catalog['products'] as $order => $data) {
            $progress && $progress($data['name']);
            $slugs[] = Str::slug($data['name']);

            try {
                $this->saveProduct($data, $order, $withImages);
                $count++;
            } catch (Throwable $e) {
                $this->warnings[] = "{$data['name']}: nepodařilo se uložit ({$e->getMessage()}).";
            }
        }

        $this->removeObsolete($slugs);

        return $count;
    }

    /**
     * Produkty dodavatele, které v katalogu už nejsou (např. dřívější řady „N-Line“
     * rozdělené na jednotlivé kolejnice): bez cen se smažou, s cenami jen skryjí.
     *
     * @param  list<string>  $slugs
     */
    private function removeObsolete(array $slugs): void
    {
        RailProduct::query()
            ->where('supplier', self::SUPPLIER)
            ->whereNotIn('slug', $slugs)
            ->with('variants')
            ->each(function (RailProduct $product) {
                $hasPrices = RailPrice::query()->whereIn('rail_variant_id', $product->variants->pluck('id'))->exists();

                if ($hasPrices) {
                    $product->update(['is_active' => false]);
                    $this->warnings[] = "{$product->name}: v katalogu už není, ale má vyplněné ceny – jen jsem ho skryl.";

                    return;
                }

                $product->delete();
                $this->removed++;
            });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function saveProduct(array $data, int $order, bool $withImages): void
    {
        $product = RailProduct::query()->firstOrNew(['slug' => Str::slug($data['name'])]);
        $isNew = ! $product->exists;

        $product->fill([
            'kind' => RailKind::from($data['kind']),
            'line' => $data['line'] ?? null,
            'name' => $data['name'],
            'code' => $data['code'] ?? null,
            'subtitle' => $data['subtitle'] ?? null,
            'description' => $data['description'] ?? null,
            'mountings' => $data['mountings'] ?? null,
            'features' => $data['features'] ?? [],
            'specs' => $this->specs($data['specs'] ?? []),
            'components' => $this->components($data['components'] ?? []),
            'supplier' => self::SUPPLIER,
            'supplier_url' => $data['source'] ?? null,
            'sort_order' => $order,
        ]);

        if ($isNew) {
            $product->is_active = false;
        }

        $product->save();

        $images = [];
        foreach ($data['images'] ?? [] as $image) {
            $images[] = ['variant' => null, 'role' => RailImage::PHOTO, 'id' => $image['id'], 'alt' => $image['alt'] ?? $data['name']];
        }

        if (! empty($data['overview'])) {
            $images[] = ['variant' => null, 'role' => RailImage::PHOTO, 'id' => $data['overview'], 'alt' => $data['name']];
        }

        $kept = [];
        foreach ($data['variants'] as $variantOrder => $variantData) {
            /** @var RailVariant $variant */
            $variant = $product->variants()->firstOrNew(['name' => $variantData['name']]);
            $kept[] = $variantData['name'];
            $variant->fill([
                'code' => $variantData['code'] ?? null,
                'description' => $variantData['description'] ?? null,
                'features' => $variantData['features'] ?? [],
                'specs' => $this->specs($variantData['specs'] ?? []),
                'components' => $this->components($variantData['components'] ?? []),
                'sort_order' => $variantOrder,
            ])->save();

            foreach ($variantData['images'] ?? [] as $image) {
                $images[] = ['variant' => $variant->id, 'role' => RailImage::PHOTO, 'id' => $image['id'], 'alt' => $image['alt'] ?? $variant->name];
            }

            foreach ($variantData['components'] ?? [] as $component) {
                $images[] = ['variant' => $variant->id, 'role' => RailImage::COMPONENT, 'id' => $component['image'], 'alt' => $component['name']];
            }
        }

        // Varianty, které z katalogu zmizely a nemají ceny, smazat.
        $product->variants()->whereNotIn('name', $kept)->whereDoesntHave('prices')->delete();

        foreach ($data['components'] ?? [] as $component) {
            $images[] = ['variant' => null, 'role' => RailImage::COMPONENT, 'id' => $component['image'], 'alt' => $component['name']];
        }

        foreach ($data['gallery'] ?? [] as $id) {
            $images[] = ['variant' => null, 'role' => RailImage::PHOTO, 'id' => $id, 'alt' => $data['name'].' – realizace'];
        }

        foreach ($images as $index => $image) {
            $this->saveImage($product, $image, $index, $withImages);
        }
    }

    /**
     * @param  array{variant: ?int, role: string, id: string, alt: string}  $image
     */
    private function saveImage(RailProduct $product, array $image, int $order, bool $withImages): void
    {
        $source = self::imageUrl($image['id']);

        $record = RailImage::query()->firstOrNew([
            'rail_product_id' => $product->id,
            'rail_variant_id' => $image['variant'],
            'role' => $image['role'],
            'source_url' => $source,
        ]);
        $record->fill(['alt' => $image['alt'], 'sort_order' => $order]);

        if ($withImages && (! $record->image_path || ! Storage::disk()->exists($record->image_path))) {
            try {
                $record->image_path = $this->download($source, $image['id']);
            } catch (Throwable $e) {
                $this->warnings[] = "{$product->name}: fotku {$image['id']} se nepodařilo stáhnout ({$e->getMessage()}).";
            }
        }

        $record->save();
    }

    private function download(string $source, string $id): string
    {
        if (isset($this->downloaded[$source])) {
            return $this->downloaded[$source];
        }

        $extension = strtolower(pathinfo($id, PATHINFO_EXTENSION)) ?: 'jpg';
        $file = sprintf('rails/%s/%s.%s', self::SUPPLIER, substr(sha1($id), 0, 16), $extension);

        if (! Storage::disk()->exists($file)) {
            $body = $this->request()->get($source.self::IMAGE_TRANSFORM.basename($id))->throw()->body();

            if ($body === '' || strlen($body) > self::MAX_IMAGE_BYTES) {
                throw new RuntimeException('soubor je prázdný nebo větší než 12 MB');
            }

            Storage::disk()->put($file, $body);
            $this->images++;

            if (! app()->runningUnitTests()) {
                usleep(150 * 1000);
            }
        }

        return $this->downloaded[$source] = $file;
    }

    public static function imageUrl(string $id): string
    {
        return str_starts_with($id, 'http') ? $id : self::IMAGE_BASE.$id;
    }

    /**
     * @param  array<string, string>  $specs
     * @return list<array{label: string, value: string}>
     */
    private function specs(array $specs): array
    {
        return array_map(fn ($label, $value) => ['label' => $label, 'value' => $value], array_keys($specs), $specs);
    }

    /**
     * @param  list<array<string, mixed>>  $components
     * @return list<array{code: ?string, name: string, group: string, image: ?string}>
     */
    private function components(array $components): array
    {
        return array_map(fn (array $component) => [
            'code' => $component['code'] ?? null,
            'name' => $component['name'],
            'group' => $component['group'] ?? 'Díly',
            'image' => isset($component['image']) ? self::imageUrl($component['image']) : null,
        ], $components);
    }

    private function request(): PendingRequest
    {
        return Http::withUserAgent('TextilNaMiru-katalog/1.0')->timeout(60)->retry(3, 1000);
    }
}
