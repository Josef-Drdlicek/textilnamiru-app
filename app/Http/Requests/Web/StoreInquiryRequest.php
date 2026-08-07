<?php

namespace App\Http\Requests\Web;

use App\Enums\InquiryProduct;
use App\Enums\MeasurementType;
use App\Enums\SpaceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInquiryRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'city' => ['nullable', 'string', 'max:100'],
            'space_type' => ['nullable', Rule::enum(SpaceType::class)],
            'products' => ['nullable', 'array'],
            'products.*' => [Rule::enum(InquiryProduct::class)],
            'measurement' => ['required', Rule::enum(MeasurementType::class)],
            'message' => ['nullable', 'string', 'max:5000'],
            'gdpr' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'jméno a příjmení',
            'email' => 'e-mail',
            'phone' => 'telefon',
            'city' => 'město',
            'space_type' => 'typ prostoru',
            'products' => 'produkty',
            'measurement' => 'způsob zaměření',
            'message' => 'zpráva',
            'gdpr' => 'souhlas se zpracováním osobních údajů',
        ];
    }
}
