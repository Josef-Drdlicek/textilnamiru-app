<?php

namespace App\Actions;

use App\Models\PricingVariable;
use Illuminate\Support\Str;

/**
 * Vlastní řádek ceníku přidaný dílnou. Kód se odvodí z názvu
 * („LED pásek 24 V" → LED_PASEK_24_V), při kolizi dostane číselnou příponu.
 */
class AddCustomPricingVariable
{
    public function handle(string $name, ?string $unit, float $value): PricingVariable
    {
        return PricingVariable::create([
            'code' => $this->uniqueCode($name),
            'name' => $name,
            'unit' => $unit,
            'value' => $value,
            'is_system' => false,
        ]);
    }

    private function uniqueCode(string $name): string
    {
        $base = Str::of($name)->ascii()->upper()->replaceMatches('/[^A-Z0-9]+/', '_')->trim('_')->toString();
        $base = $base !== '' ? $base : 'POLOZKA';

        $code = $base;

        for ($suffix = 2; PricingVariable::where('code', $code)->exists(); $suffix++) {
            $code = "{$base}_{$suffix}";
        }

        return $code;
    }
}
