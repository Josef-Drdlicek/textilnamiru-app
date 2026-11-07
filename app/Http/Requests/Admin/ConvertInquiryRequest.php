<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConvertInquiryRequest extends FormRequest
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
            'user_id' => [
                'required',
                // Pozn.: boolean hodnoty se při serializaci pravidla mění na
                // prázdný řetězec — proto 1/0.
                Rule::exists('users', 'id')->where('is_active', 1)->where('is_admin', 0),
            ],
            'name' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'user_id' => 'studio',
            'name' => 'název zakázky',
        ];
    }
}
