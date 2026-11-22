<?php

namespace App\Http\Requests\Admin;

use App\Enums\TextileCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveTextileProductRequest extends FormRequest
{
    /** Největší fotka produktu [kB]. */
    public const int MAX_IMAGE_KB = 8192;

    public function authorize(): bool
    {
        return true; // přístup hlídá middleware admin
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::enum(TextileCategory::class)],
            'price_per_meter' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'roll_height' => ['required', 'integer', 'min:50', 'max:600'],
            'material' => ['nullable', 'string', 'max:255'],
            'shrinkage' => ['nullable', 'string', 'max:20'],
            'delivery_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'colors' => ['required', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.self::MAX_IMAGE_KB],
            'remove_image' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'název',
            'category' => 'kategorie',
            'price_per_meter' => 'cena bez DPH',
            'roll_height' => 'výška role',
            'material' => 'materiál',
            'shrinkage' => 'srážení',
            'delivery_days' => 'dodací doba',
            'colors' => 'barvy',
            'description' => 'popis',
            'image' => 'fotka',
        ];
    }

    /**
     * Data pro model: barvy z textu „Bílá, Přírodní“ na seznam.
     *
     * @return array<string, mixed>
     */
    public function productData(): array
    {
        $data = $this->validated();

        $data['is_active'] = $this->boolean('is_active');
        unset($data['colors'], $data['image'], $data['remove_image']); // barvy a fotku řeší kontroler

        return $data;
    }

    /**
     * Barvy z textu „Bílá, Přírodní“ jako seznam názvů (bez duplicit).
     *
     * @return list<string>
     */
    public function colorNames(): array
    {
        return collect(explode(',', (string) $this->validated('colors')))
            ->map(fn (string $color) => trim($color))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
