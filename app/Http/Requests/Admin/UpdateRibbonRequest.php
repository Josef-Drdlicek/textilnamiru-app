<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRibbonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Povolené poměry přichází z formuláře jako text „1,5; 2; 2,5" —
     * před validací je převedeme na pole čísel.
     */
    protected function prepareForValidation(): void
    {
        if (! is_string($this->input('allowed_ratios'))) {
            return;
        }

        // Čárka následovaná mezerou odděluje hodnoty; čárka uvnitř čísla
        // je desetinná („1,5; 2" i „1.5, 2, 2.5" jsou platné zápisy).
        $normalized = str_replace(', ', ';', $this->input('allowed_ratios'));

        $ratios = collect(preg_split('/[;\s]+/', $normalized))
            ->filter(fn (string $part) => $part !== '')
            ->map(fn (string $part) => str_replace(',', '.', $part))
            ->map(fn (string $part) => is_numeric($part) ? (float) $part : $part)
            ->values()
            ->all();

        $this->merge(['allowed_ratios' => $ratios]);
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        $allowsCustom = (bool) $this->route('ribbon')?->allows_custom_ratio;

        return [
            'name' => ['required', 'string', 'max:255'],
            'price_per_meter' => ['required', 'numeric', 'min:0', 'max:100000'],
            'allowed_ratios' => [$allowsCustom ? 'nullable' : 'required', 'array', $allowsCustom ? 'max:0' : 'min:1'],
            'allowed_ratios.*' => ['numeric', 'min:1', 'max:10'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'název stuhy',
            'price_per_meter' => 'cena za metr',
            'allowed_ratios' => 'povolené poměry řasení',
        ];
    }
}
