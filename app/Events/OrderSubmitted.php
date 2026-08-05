<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Zakázka byla závazně objednána. Zatím bez posluchačů — až dílna potvrdí,
 * že chce e-mailové notifikace (zadání §13.6), stačí přidat listener.
 */
class OrderSubmitted
{
    use Dispatchable;

    public function __construct(
        public readonly Order $order,
    ) {}
}
