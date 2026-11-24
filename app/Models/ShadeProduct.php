<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Roleta jako produkt (např. Roleta Maestra Magic) s variantami provedení.
 */
#[Fillable(['name', 'slug', 'sort_order', 'is_active'])]
class ShadeProduct extends Model
{
    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    /**
     * @return HasMany<ShadeVariant, $this>
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ShadeVariant::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function mainVariant(): ?ShadeVariant
    {
        return $this->variants->first();
    }
}
