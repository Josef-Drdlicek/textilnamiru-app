<?php

namespace App\Events;

use App\Models\Inquiry;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Z veřejného webu přišla nová poptávka. Zatím bez posluchačů — až bude
 * nastavený mailer a dílna potvrdí cílovou adresu, stačí přidat listener.
 */
class InquirySubmitted
{
    use Dispatchable;

    public function __construct(
        public readonly Inquiry $inquiry,
    ) {}
}
