<?php

namespace App\Http\Controllers;

use App\Actions\DeferOrder;
use App\Http\Requests\DeferOrderRequest;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;

class OrderDeferralController extends Controller
{
    public function store(DeferOrderRequest $request, Order $order, DeferOrder $deferOrder): RedirectResponse
    {
        $this->authorize('update', $order);

        $deferOrder->handle(
            order: $order,
            deadline: $request->filled('deadline') ? Carbon::parse($request->string('deadline')->toString()) : null,
            materialsNote: $request->filled('materials_note') ? $request->string('materials_note')->toString() : null,
        );

        return redirect()
            ->route('orders.welcome')
            ->with('status', "Zakázka „{$order->name}“ je uložená mezi cenovými nabídkami.");
    }
}
