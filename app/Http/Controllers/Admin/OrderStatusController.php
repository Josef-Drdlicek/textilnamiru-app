<?php

namespace App\Http\Controllers\Admin;

use App\Actions\TransitionOrderStatus;
use App\Enums\OrderStatus;
use App\Exceptions\InvalidStatusTransition;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderStatusController extends Controller
{
    public function store(Request $request, Order $order, TransitionOrderStatus $transitionOrderStatus): RedirectResponse
    {
        $this->authorize('transition', $order);

        $validated = $request->validate([
            'status' => ['required', Rule::enum(OrderStatus::class)],
        ]);

        $to = OrderStatus::from($validated['status']);

        try {
            $transitionOrderStatus->handle($order, $to, $request->user());
        } catch (InvalidStatusTransition $exception) {
            return back()->withErrors(['status' => $exception->getMessage()]);
        }

        return back()->with('status', "Zakázka „{$order->name}“ je nyní ve stavu „{$to->label()}“.");
    }
}
