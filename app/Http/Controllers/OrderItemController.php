<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use Illuminate\Http\RedirectResponse;

class OrderItemController extends Controller
{
    public function destroy(OrderItem $item): RedirectResponse
    {
        $this->authorize('delete', $item);

        $item->delete();

        return redirect()
            ->route('orders.show', $item->order)
            ->with('status', 'Položka byla odebrána ze zakázky.');
    }
}
