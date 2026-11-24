<?php

namespace App\Models;

use App\Enums\RailKind;
use App\Enums\RailMounting;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Kolejnice (např. N1 Napoli, varianty = sestavy) nebo dekorační garnýž (varianty = barvy).
 * Cena varianty podle délky a způsobu zavěšení (Ceník kolejnic).
 */
#[Fillable(['kind', 'line', 'name', 'code', 'slug', 'subtitle', 'description', 'features', 'specs', 'components', 'mountings', 'supplier', 'supplier_url', 'sort_order', 'is_active'])]
class RailProduct extends Model
{
    protected function casts(): array
    {
        return [
            'kind' => RailKind::class,
            'features' => 'array',
            'specs' => 'array',
            'components' => 'array',
            'mountings' => 'array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<RailVariant, $this>
     */
    public function variants(): HasMany
    {
        return $this->hasMany(RailVariant::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return HasMany<RailImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(RailImage::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Způsoby zavěšení, které produkt nabízí (výchozí: strop i stěna).
     *
     * @return list<RailMounting>
     */
    public function mountingOptions(): array
    {
        $options = array_values(array_filter(array_map(fn ($value) => RailMounting::tryFrom((string) $value), $this->mountings ?? [])));

        return $options ?: RailMounting::cases();
    }

    /**
     * Popisek variant: u kolejnic sestava, u garnýží barva.
     */
    public function variantLabel(): string
    {
        return $this->kind === RailKind::Decorative ? 'Barva' : 'Sestava';
    }

    /**
     * @return list<string>
     */
    public function paragraphs(): array
    {
        return array_values(array_filter(array_map('trim', explode("\n\n", (string) $this->description))));
    }

    /**
     * Fotka na dlaždici: první fotka první varianty, jinak fotka produktu.
     */
    public function coverImage(): ?RailImage
    {
        $photos = $this->images->filter(fn (RailImage $image) => $image->role === RailImage::PHOTO && $image->image_path !== null);

        // Garnýž: fotka první barvy; kolejnice: hlavní fotka kolejnice.
        return $this->kind === RailKind::Decorative
            ? ($photos->firstWhere('rail_variant_id', $this->variants->first()?->id) ?? $photos->first())
            : ($photos->whereNull('rail_variant_id')->first() ?? $photos->first());
    }
}
