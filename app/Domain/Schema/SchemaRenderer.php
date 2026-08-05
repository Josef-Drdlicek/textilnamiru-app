<?php

namespace App\Domain\Schema;

use App\Domain\Pricing\Parameters\ItemParameters;

/**
 * Technický nákres položky ve SVG. Stejný renderer obsluhuje živý náhled
 * v konfigurátoru i trvalý nákres položky pro dílnu.
 */
interface SchemaRenderer
{
    public function render(ItemParameters $parameters): string;
}
