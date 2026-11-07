<?php

namespace App\Actions;

use App\Models\Order;
use Carbon\CarbonInterface;

/**
 * Odložení objednávky: studio uloží materiál a termín, ale zakázku
 * neodešle. Zůstává rozpracovaná a v přehledu se ukazuje jako cenová nabídka.
 */
class DeferOrder
{
    public function handle(Order $order, ?CarbonInterface $deadline, ?string $materialsNote): void
    {
        $order->update([
            'deadline' => $deadline,
            'materials_note' => $materialsNote,
            'deferred_at' => now(),
        ]);
    }
}
