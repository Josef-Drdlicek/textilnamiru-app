<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Exceptions\InvalidStatusTransition;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Jediné místo, kudy se mění stav zakázky — vynucuje povolené přechody
 * a zapisuje audit (kdo a kdy stav posunul).
 */
class TransitionOrderStatus
{
    public function handle(Order $order, OrderStatus $to, User $by): void
    {
        DB::transaction(function () use ($order, $to, $by): void {
            if (! $order->status->canTransitionTo($to)) {
                throw InvalidStatusTransition::between($order->status, $to);
            }

            $from = $order->status;

            $order->update(['status' => $to]);

            $order->statusChanges()->create([
                'user_id' => $by->id,
                'from_status' => $from,
                'to_status' => $to,
            ]);
        });
    }
}
