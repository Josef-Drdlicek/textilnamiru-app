<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Sestava kolejnice (např. T1-11 Jednokolejnička rovná) nebo barva garnýže,
 * s cenou podle délky a způsobu zavěšení.
 */
#[Fillable(['name', 'code', 'description', 'features', 'specs', 'components', 'sort_order'])]
class RailVariant extends Model
{
    protected function casts(): array
    {
        return [
            'features' => 'array',
            'specs' => 'array',
            'components' => 'array',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<RailProduct, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(RailProduct::class, 'rail_product_id');
    }

    /**
     * @return HasMany<RailPrice, $this>
     */
    public function prices(): HasMany
    {
        return $this->hasMany(RailPrice::class)->orderBy('length_to');
    }

    /**
     * @return list<string>
     */
    public function paragraphs(): array
    {
        return array_values(array_filter(array_map('trim', explode("\n\n", (string) $this->description))));
    }
}
