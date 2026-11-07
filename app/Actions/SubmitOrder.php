<?php

namespace App\Actions;

use App\Domain\Pricing\ItemPricer;
use App\Enums\OrderStatus;
use App\Events\OrderSubmitted;
use App\Exceptions\CannotSubmitEmptyOrder;
use App\Models\Order;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

/**
 * Závazné objednání: zamrazí celkovou cenu i sazbu DPH a převede zakázku
 * do stavu Nová. Od této chvíle je zakázka pro studio jen ke čtení
 * a pozdější změny ceníku ji nijak neovlivní.
 */
class SubmitOrder
{
    public function __construct(
        private readonly ItemPricer $pricer,
        private readonly TransitionOrderStatus $transitionOrderStatus,
    ) {}

    public function handle(Order $order, User $by, CarbonInterface $deadline, ?string $materialsNote): void
    {
        DB::transaction(function () use ($order, $by, $deadline, $materialsNote): void {
            if ($order->items()->count() === 0) {
                throw new CannotSubmitEmptyOrder;
            }

            $order->update([
                'deadline' => $deadline,
                'materials_note' => $materialsNote,
                'submitted_at' => now(),
                'total_price' => round((float) $order->items()->sum('total_price'), 2),
                'vat_rate' => $this->pricer->vatRate(),
            ]);

            $this->transitionOrderStatus->handle($order, OrderStatus::New, $by);
        });

        OrderSubmitted::dispatch($order);
    }
}
