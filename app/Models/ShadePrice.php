<?php

namespace App\Models;

use App\Enums\ShadePriceCategory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['price_category', 'width', 'height', 'price'])]
class ShadePrice extends Model
{
    protected function casts(): array
    {
        return ['price_category' => ShadePriceCategory::class, 'width' => 'integer', 'height' => 'integer', 'price' => 'float'];
    }
}
