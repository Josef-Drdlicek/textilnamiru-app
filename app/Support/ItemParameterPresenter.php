<?php

namespace App\Support;

use App\Models\Ribbon;
use Illuminate\Support\Facades\Lang;

/**
 * Převede uložené parametry položky na čitelné řádky „popisek: hodnota"
 * pro admin detail, poptávku a výrobní list. Popisky, jednotky a hodnoty
 * výčtů bere z lang/cs/parameters.php.
 */
class ItemParameterPresenter
{
    /**
     * @param  array<string, mixed>  $parameters
     * @return list<array{label: string, value: string}>
     */
    public function rows(array $parameters): array
    {
        $rows = [];

        foreach ($parameters as $key => $value) {
            if ($value === null || $value === false) {
                continue;
            }

            $rows[] = [
                'label' => __("parameters.{$key}"),
                'value' => $this->formatValue($key, $value),
            ];
        }

        return $rows;
    }

    private function formatValue(string $key, mixed $value): string
    {
        if ($value === true) {
            return 'ano';
        }

        if ($key === 'ribbon_code') {
            return Ribbon::query()->where('code', $value)->value('name') ?? (string) $value;
        }

        if (is_string($value) && Lang::has("parameters.values.{$value}")) {
            return __("parameters.values.{$value}");
        }

        if (is_numeric($value)) {
            $number = rtrim(rtrim(number_format((float) $value, 2, ',', ' '), '0'), ',');
            $unit = Lang::has("parameters.units.{$key}") ? ' '.__("parameters.units.{$key}") : '';

            return $number.$unit;
        }

        return (string) $value;
    }
}
