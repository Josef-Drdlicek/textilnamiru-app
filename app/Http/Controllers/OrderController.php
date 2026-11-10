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
    /**
     * Moje zakázky: založení nové zakázky a přehled odeslaných zakázek.
     */
    public function welcome(Request $request): View
    {
        return view('orders.welcome', [
            'submitted' => $request->user()->orders()
                ->where('status', '!=', OrderStatus::Draft)
                ->latest()
                ->get(),
        ]);
    }

    /**
     * Cenové nabídky: rozpracované a odložené zakázky s orientační cenou.
     */
    public function quotes(Request $request, ItemPricer $pricer): View
    {
        return view('orders.quotes', [
            'quotes' => $request->user()->orders()
                ->where('status', OrderStatus::Draft)
                ->withSum('items as items_total', 'total_price')
                ->latest()
                ->get(),
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

        // Smazaná nabídka vrací na seznam nabídek, hotová zakázka na Moje zakázky.
        $backTo = $order->isDraft() ? 'orders.quotes' : 'orders.welcome';

        $order->delete();

        return redirect()
            ->route($backTo)
            ->with('status', "Zakázka „{$order->name}“ byla smazána.");
    }
}
