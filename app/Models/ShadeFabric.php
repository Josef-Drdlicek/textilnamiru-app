<?php

namespace App\Models;

use App\Enums\ShadeFabricCollection;
use App\Enums\ShadePriceCategory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Látka na rolety (Textil pro rolety): fotka, vlastnosti a cenová skupina A–D.
 */
#[Fillable(['supplier', 'code', 'name', 'slug', 'collection', 'price_category', 'roll_width', 'specs', 'image_path', 'source_image_url', 'is_active', 'sort_order'])]
class ShadeFabric extends Model
{
    protected function casts(): array
    {
        return [
            'collection' => ShadeFabricCollection::class,
            'price_category' => ShadePriceCategory::class,
            'roll_width' => 'integer',
            'specs' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function imageUrl(): ?string
    {
        return $this->image_path ? route('web.shade-fabric-image', ['fabric' => $this, 'v' => $this->updated_at?->timestamp]) : null;
    }

    /**
     * Hodnota vlastnosti podle popisku, např. spec('Gramáž') → „175 g/m2“.
     */
    public function spec(string $label): ?string
    {
        foreach ($this->specs ?? [] as $row) {
            if (($row['label'] ?? null) === $label) {
                return $row['value'];
            }
        }

        return null;
    }

    /**
     * Jde z látky ušít roletu dané šířky? (Šíře látky = největší šířka rolety.)
     */
    public function fitsWidth(?float $width): bool
    {
        return $width === null || $this->roll_width === null || $width <= $this->roll_width;
    }
}
