<?php

namespace App\Http\Controllers;

use App\Actions\SubmitOrder;
use App\Http\Requests\SubmitOrderRequest;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Poslední krok objednávky: soupis dodaného materiálu, termín realizace
 * a rekapitulace před závazným objednáním.
 */
class OrderSubmissionController extends Controller
{
    public function create(Order $order): View
    {
        $this->authorize('submit', $order);

        $order->load('items');

        return view('orders.materials', ['order' => $order]);
    }

    public function store(SubmitOrderRequest $request, Order $order, SubmitOrder $submitOrder): RedirectResponse
    {
        $this->authorize('submit', $order);

        if ($order->items()->count() === 0) {
            return redirect()
                ->route('orders.show', $order)
                ->with('status', 'Zakázku bez položek nelze objednat — přidejte alespoň jednu položku.');
        }

        $submitOrder->handle(
            order: $order,
            by: $request->user(),
            deadline: Carbon::parse($request->string('deadline')->toString()),
            materialsNote: $request->filled('materials_note') ? $request->string('materials_note')->toString() : null,
        );

        return redirect()
            ->route('orders.show', $order)
            ->with('status', "Zakázka „{$order->name}“ byla závazně objednána. Dílna se ozve.");
    }
}
