<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;

class CreateDraftOrder
{
    public function handle(User $user, string $name): Order
    {
        return $user->orders()->create([
            'name' => $name,
            'status' => OrderStatus::Draft,
        ]);
    }
}
