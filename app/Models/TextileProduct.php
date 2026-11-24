<?php

namespace App\Models;

use App\Enums\TextileCategory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Látka z ceníku textilu. Cena je za běžný metr bez DPH; ušití se počítá
 * zvlášť kalkulátorem záclony (app/Domain/Pricing). Cena může chybět
 * (importovaná látka) — pak se ušití nepočítá a produkt jde jen poptat.
 */
#[Fillable(['name', 'slug', 'supplier', 'supplier_code', 'category', 'description', 'specs', 'excluded_colors', 'material', 'roll_height', 'shrinkage', 'delivery_days', 'price_per_meter', 'image_path', 'is_active'])]
class TextileProduct extends Model
{
    /** Rezerva na lem a úchyt: hotová výška musí být o tolik menší než výška role [cm]. */
    public const int HEIGHT_ALLOWANCE = 5;

    /** Nejvyšší závěs – šije se i ze svislých pruhů, výška role ho neomezuje [cm]. */
    public const int MAX_DRAPE_HEIGHT = 400;

    protected function casts(): array
    {
        return [
            'category' => TextileCategory::class,
            'roll_height' => 'integer',
            'delivery_days' => 'integer',
            'price_per_meter' => 'float',
            'specs' => 'array',
            'excluded_colors' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<TextileProductColor, $this>
     */
    public function colors(): HasMany
    {
        return $this->hasMany(TextileProductColor::class)->orderBy('sort_order')->orderBy('id');
    }

    public function hasPrice(): bool
    {
        return $this->price_per_meter !== null;
    }

    /**
     * Hlavní fotka: zvolené barvy, jinak vlastní fotka produktu, jinak první barvy s fotkou.
     */
    public function mainImageUrl(?TextileProductColor $color = null): ?string
    {
        return $color?->imageUrl()
            ?? $this->imageUrl()
            ?? $this->colors->first(fn (TextileProductColor $c) => $c->image_path !== null)?->imageUrl();
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Největší hotová výška, kterou z role ušijeme [cm].
     */
    public function maxHeight(): int
    {
        return $this->category === TextileCategory::Drape
            ? self::MAX_DRAPE_HEIGHT
            : $this->roll_height - self::HEIGHT_ALLOWANCE;
    }

    /**
     * Základ názvu zástupné fotky v public/images/web/ (viz <x-web.img>), když fotka není nahraná.
     */
    public function imageName(): string
    {
        return 'textil-'.$this->slug;
    }

    /**
     * Adresa nahrané fotky; parametr v mění se s úpravou produktu, takže prohlížeč nedrží starou fotku.
     */
    public function imageUrl(): ?string
    {
        return $this->image_path
            ? route('web.textile-image', ['product' => $this, 'v' => $this->updated_at?->timestamp])
            : null;
    }
}
