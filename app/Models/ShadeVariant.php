<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Provedení rolety (např. Leontina Box) s texty, fotkami a cenovou tabulkou
 * šířka × výška pro každou cenovou skupinu látky A–D. Cena platí „do“ rozměru: rozměr se zaokrouhlí nahoru na krok tabulky.
 */
#[Fillable(['name', 'supplier', 'supplier_url', 'subtitle', 'slogan', 'description', 'technical', 'min_width', 'max_width', 'min_height', 'max_height', 'step', 'fabric_collections', 'sort_order'])]
class ShadeVariant extends Model
{
    protected function casts(): array
    {
        return [
            'technical' => 'array',
            'fabric_collections' => 'array',
            'min_width' => 'integer',
            'max_width' => 'integer',
            'min_height' => 'integer',
            'max_height' => 'integer',
            'step' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<ShadeProduct, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(ShadeProduct::class, 'shade_product_id');
    }

    /**
     * @return HasMany<ShadeVariantImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(ShadeVariantImage::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return HasMany<ShadePrice, $this>
     */
    public function prices(): HasMany
    {
        return $this->hasMany(ShadePrice::class);
    }

    /**
     * Látky, ze kterých se provedení šije (aktivní, z kolekcí provedení), seřazené podle skupiny A–D.
     *
     * @return Collection<int, ShadeFabric>
     */
    public function fabrics(): Collection
    {
        return ShadeFabric::query()
            ->active()
            ->whereIn('collection', $this->fabric_collections ?? [])
            ->orderBy('price_category')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Šířky sloupců cenové tabulky: od minima po maximum po kroku (maximum vždy zahrnuto).
     *
     * @return list<int>
     */
    public function widths(): array
    {
        return $this->axis($this->min_width, $this->max_width);
    }

    /**
     * @return list<int>
     */
    public function heights(): array
    {
        return $this->axis($this->min_height, $this->max_height);
    }

    /**
     * Vejde se rozměr okna do rozsahu provedení? (Cenu počítá App\Domain\Pricing\ShadePricer.)
     */
    public function fits(float $width, float $height): bool
    {
        return $width >= $this->min_width && $width <= $this->max_width
            && $height >= $this->min_height && $height <= $this->max_height;
    }

    /**
     * Popis rozdělený na odstavce.
     *
     * @return list<string>
     */
    public function paragraphs(): array
    {
        return array_values(array_filter(array_map('trim', explode("\n\n", (string) $this->description))));
    }

    /**
     * @return list<int>
     */
    private function axis(int $min, int $max): array
    {
        $step = max(1, $this->step);
        $values = range($min, $max, $step);

        if (end($values) !== $max) {
            $values[] = $max;
        }

        return $values;
    }
}
