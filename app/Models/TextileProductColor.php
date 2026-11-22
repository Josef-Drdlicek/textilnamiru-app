<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Barva látky z ceníku textilu, volitelně s vlastní fotkou.
 */
#[Fillable(['name', 'image_path', 'source_image_url', 'sort_order'])]
class TextileProductColor extends Model
{
    /**
     * @return BelongsTo<TextileProduct, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(TextileProduct::class, 'textile_product_id');
    }

    /**
     * Popisek pro zákazníka: kód dodavatele („03“) jako „Barva 03“, jinak název.
     */
    public function label(): string
    {
        return ctype_digit($this->name) ? "Barva {$this->name}" : $this->name;
    }

    public function imageUrl(): ?string
    {
        return $this->image_path
            ? route('web.textile-color-image', ['color' => $this, 'v' => $this->updated_at?->timestamp])
            : null;
    }
}
