<?php

namespace App\Http\Controllers;

use App\Actions\CreateDraftOrder;
use App\Domain\Pricing\ItemPricer;
use App\Enums\OrderStatus;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Rozcestník studia: založení nové zakázky, cenové nabídky (rozpracované)
     * a odeslané zakázky.
     */
    public function welcome(Request $request, ItemPricer $pricer): View
    {
        $orders = $request->user()->orders()
            ->withSum('items as items_total', 'total_price')
            ->latest()
            ->get();

        return view('orders.welcome', [
            'quotes' => $orders->where('status', OrderStatus::Draft),
            'submitted' => $orders->where('status', '!=', OrderStatus::Draft),
            'vatRate' => $pricer->vatRate(),
        ]);
    }

    public function store(StoreOrderRequest $request, CreateDraftOrder $createDraftOrder): RedirectResponse
    {
        $order = $createDraftOrder->handle($request->user(), $request->string('name')->toString());

        return redirect()->route('orders.show', $order);
    }

    public function show(Request $request, Order $order): View
    {
        $this->authorize('view', $order);

        $order->load('items');

        return view('orders.show', ['order' => $order]);
    }

    /**
     * Smazání cenové nabídky nebo hotové zakázky studiem.
     */
    public function destroy(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('delete', $order);

        $order->delete();

        return redirect()
            ->route('orders.welcome')
            ->with('status', "Zakázka „{$order->name}“ byla smazána.");
    }
}
