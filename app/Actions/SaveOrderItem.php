<?php

namespace App\Actions;

use App\Domain\Pricing\ItemPricer;
use App\Domain\Pricing\Parameters\ItemParameters;
use App\Enums\ItemType;
use App\Models\Order;
use App\Models\OrderItem;

/**
 * Uloží položku zakázky. Cena, spotřeba i rozpad ceny se VŽDY počítají
 * na serveru z parametrů — klient žádnou cenu neposílá (zadání §11.1).
 */
class SaveOrderItem
{
    public function __construct(
        private readonly ItemPricer $pricer,
    ) {}

    public function handle(
        Order $order,
        ItemType $type,
        ItemParameters $parameters,
        string $material,
        int $count,
        ?string $room = null,
        ?string $notes = null,
        ?OrderItem $existing = null,
    ): OrderItem {
        $result = $this->pricer->price($type, $parameters);

        $attributes = [
            'type' => $type,
            'material' => $material,
            'room' => $room,
            'count' => $count,
            'notes' => $notes,
            'parameters' => $parameters,
            'unit_price' => $result->unitPrice,
            'total_price' => $result->totalPrice($count),
            'consumed_meters' => $result->consumedMeters,
            'price_breakdown' => $result->linesToArray(),
        ];

        if ($existing !== null) {
            $existing->update($attributes);

            return $existing;
        }

        return $order->items()->create($attributes);
    }
}
