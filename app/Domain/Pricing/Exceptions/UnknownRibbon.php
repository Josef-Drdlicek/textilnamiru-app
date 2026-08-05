<?php

namespace App\Domain\Pricing\Exceptions;

use DomainException;

class UnknownRibbon extends DomainException
{
    public static function forCode(string $code): self
    {
        return new self("Řasící stuha [{$code}] neexistuje.");
    }
}
