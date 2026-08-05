<?php

namespace App\Exceptions;

use App\Enums\OrderStatus;
use DomainException;

class InvalidStatusTransition extends DomainException
{
    public static function between(OrderStatus $from, OrderStatus $to): self
    {
        return new self("Zakázku nelze převést ze stavu „{$from->label()}“ do stavu „{$to->label()}“.");
    }
}
