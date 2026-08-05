<?php

namespace App\Domain\Pricing\Parameters;

use App\Casts\ItemParametersCast;

/**
 * Typované parametry položky zakázky specifické pro typ výrobku.
 *
 * Ukládají se do JSON sloupce `order_items.parameters`; hydrataci na správnou
 * třídu podle typu položky řeší {@see ItemParametersCast}.
 */
interface ItemParameters
{
    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static;

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
