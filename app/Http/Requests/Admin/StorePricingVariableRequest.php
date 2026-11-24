<?php

namespace App\Http\Requests\Admin;

use App\Enums\PricingCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePricingVariableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:20'],
            'value' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'category' => ['nullable', Rule::enum(PricingCategory::class)],
        ];
    }

    /**
     * Ceník, do kterého řádek přidáváme (bez volby ceník šití).
     */
    public function category(): PricingCategory
    {
        return $this->enum('category', PricingCategory::class) ?? PricingCategory::Sewing;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'název',
            'unit' => 'jednotka',
            'value' => 'cena',
        ];
    }
}
