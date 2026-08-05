<?php

namespace App\Domain\Consumption;

use App\Models\Order;

/**
 * Agregovaná spotřeba látky podle názvu materiálu — pro dílnu v detailu
 * zakázky („voal: 12,4 m"). Spotřeba za kus je zamrazená u položky, takže
 * souhrn odpovídá cenám v okamžiku objednání (oprava bugu „0 metrů", §10).
 */
class MaterialConsumptionAggregator
{
    /**
     * @return list<array{material: string, meters: float}>
     */
    public function forOrder(Order $order): array
    {
        $totals = [];

        foreach ($order->items as $item) {
            $meters = $item->consumed_meters * $item->count;

            if ($meters <= 0) {
                continue;
            }

            // „Voal" a „voal " je tentýž materiál.
            $key = mb_strtolower(trim($item->material));

            $totals[$key] ??= ['material' => trim($item->material), 'meters' => 0.0];
            $totals[$key]['meters'] += $meters;
        }

        return array_values(array_map(
            fn (array $row) => ['material' => $row['material'], 'meters' => round($row['meters'], 2)],
            $totals,
        ));
    }
}
