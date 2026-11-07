<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePricingVariableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:20'],
            'value' => ['required', 'numeric', 'min:0', 'max:1000000'],
        ];
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
