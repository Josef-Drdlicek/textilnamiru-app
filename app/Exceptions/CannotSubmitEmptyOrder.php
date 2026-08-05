<?php

namespace App\Exceptions;

use DomainException;

class CannotSubmitEmptyOrder extends DomainException
{
    public function __construct()
    {
        parent::__construct('Zakázku bez položek nelze závazně objednat.');
    }
}
