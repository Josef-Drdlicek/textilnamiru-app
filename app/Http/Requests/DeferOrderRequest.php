<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Odložení objednávky — oproti závaznému objednání je termín nepovinný.
 */
class DeferOrderRequest extends FormRequest
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
            'deadline' => ['nullable', 'date', 'after:today'],
            'materials_note' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'deadline' => 'datum požadované realizace',
            'materials_note' => 'soupis dodaného materiálu',
        ];
    }
}
