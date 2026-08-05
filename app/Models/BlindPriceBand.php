<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['max_width', 'max_height', 'price'])]
class BlindPriceBand extends Model
{
    protected function casts(): array
    {
        return [
            'max_width' => 'integer',
            'max_height' => 'integer',
            'price' => 'float',
        ];
    }
}
