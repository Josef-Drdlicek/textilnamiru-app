<?php

namespace App\Policies;

use App\Models\OrderItem;
use App\Models\User;

/**
 * Oprávnění k položce se odvozují od zakázky, do které patří.
 */
class OrderItemPolicy
{
    public function view(User $user, OrderItem $item): bool
    {
        return (new OrderPolicy)->view($user, $item->order);
    }

    public function update(User $user, OrderItem $item): bool
    {
        return (new OrderPolicy)->update($user, $item->order);
    }

    public function delete(User $user, OrderItem $item): bool
    {
        return (new OrderPolicy)->update($user, $item->order);
    }
}
