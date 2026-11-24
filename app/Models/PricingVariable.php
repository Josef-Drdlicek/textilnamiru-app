<?php

namespace App\Models;

use App\Enums\PricingCategory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['code', 'category', 'name', 'value', 'unit', 'is_system'])]
class PricingVariable extends Model
{
    protected function casts(): array
    {
        return [
            'value' => 'float',
            'is_system' => 'boolean',
            'category' => PricingCategory::class,
        ];
    }

    /**
     * Popisek hodnoty v ceníku: „%" u procent, jinak „Kč" nebo „Kč/m²".
     */
    public function valueSuffix(): string
    {
        return match ($this->unit) {
            '%' => '%',
            null, '' => 'Kč',
            default => "Kč/{$this->unit}",
        };
    }
}
