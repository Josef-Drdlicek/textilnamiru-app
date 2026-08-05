<?php

namespace App\Domain\Pricing\Exceptions;

use DomainException;

class UnknownPricingVariable extends DomainException
{
    public static function forCode(string $code): self
    {
        return new self("Ceníková proměnná [{$code}] neexistuje.");
    }
}
