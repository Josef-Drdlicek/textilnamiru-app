<?php

namespace App\Domain\Pricing\Exceptions;

use DomainException;

class NoBlindPriceBand extends DomainException
{
    public static function forSize(float $width, float $height): self
    {
        return new self("Pro rozměr rolety {$width} × {$height} cm neexistuje cenové pásmo.");
    }
}
