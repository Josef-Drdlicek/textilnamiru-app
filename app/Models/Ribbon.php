<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['code', 'name', 'price_per_meter', 'allowed_ratios', 'allows_custom_ratio', 'sort_order', 'is_active'])]
class Ribbon extends Model
{
    protected function casts(): array
    {
        return [
            'price_per_meter' => 'float',
            'allowed_ratios' => 'array',
            'allows_custom_ratio' => 'boolean',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
