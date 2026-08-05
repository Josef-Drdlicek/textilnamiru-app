<?php

namespace App\Http\Controllers;

use App\Actions\CreateDraftOrder;
use App\Enums\OrderStatus;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Rozcestník designéra: založení nové zakázky + rozpracované a odeslané zakázky.
     */
    public function welcome(Request $request): View
    {
        $orders = $request->user()->orders()->latest()->get();

        return view('orders.welcome', [
            'drafts' => $orders->where('status', OrderStatus::Draft),
            'submitted' => $orders->where('status', '!=', OrderStatus::Draft),
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
     * Zrušení rozpracované zakázky designérem.
     */
    public function destroy(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('delete', $order);

        $order->delete();

        return redirect()
            ->route('orders.welcome')
            ->with('status', "Zakázka „{$order->name}“ byla zrušena.");
    }
}
