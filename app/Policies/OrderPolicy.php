<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        return $user->isAdmin() || $this->owns($user, $order);
    }

    /**
     * Editace zakázky a jejích položek — jen vlastník a jen dokud je rozpracovaná.
     * Po závazném objednání je zakázka (včetně cen) neměnná.
     */
    public function update(User $user, Order $order): bool
    {
        return $this->owns($user, $order) && $order->isDraft();
    }

    public function submit(User $user, Order $order): bool
    {
        return $this->owns($user, $order) && $order->isDraft();
    }

    /**
     * Smazání: studio smí zahodit svou cenovou nabídku (draft) a uklidit
     * svou hotovou zakázku, admin smí smazat zakázku v libovolné fázi.
     * Mazání je soft delete, takže hotová zakázka zůstává dohledatelná.
     */
    public function delete(User $user, Order $order): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $this->owns($user, $order) && ($order->isDraft() || $order->isDone());
    }

    public function transition(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }

    private function owns(User $user, Order $order): bool
    {
        return $order->user_id === $user->id;
    }
}
