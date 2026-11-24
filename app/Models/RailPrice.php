<?php

namespace App\Models;

use App\Enums\RailMounting;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['mounting', 'length_to', 'price'])]
class RailPrice extends Model
{
    protected function casts(): array
    {
        return ['mounting' => RailMounting::class, 'length_to' => 'integer', 'price' => 'float'];
    }
}
