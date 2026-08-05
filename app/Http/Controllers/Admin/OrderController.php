<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Consumption\MaterialConsumptionAggregator;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderFilterRequest;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Přehled zakázek ve 4 stavových sekcích s filtrem podle data objednání.
     */
    public function index(OrderFilterRequest $request): View
    {
        $filter = $request->validated();

        $orders = Order::query()
            ->submitted()
            ->with('user')
            ->when($filter['submitted_from'] ?? null, fn ($query, $from) => $query->whereDate('submitted_at', '>=', $from))
            ->when($filter['submitted_to'] ?? null, fn ($query, $to) => $query->whereDate('submitted_at', '<=', $to))
            ->orderByDesc('submitted_at')
            ->get()
            ->groupBy(fn (Order $order) => $order->status->value);

        return view('admin.orders.index', [
            'sections' => OrderStatus::adminSections(),
            'orders' => $orders,
            'filter' => $filter,
        ]);
    }

    public function show(Order $order, MaterialConsumptionAggregator $aggregator): View
    {
        $this->authorize('view', $order);

        $order->load(['user', 'items', 'statusChanges.user']);

        return view('admin.orders.show', [
            'order' => $order,
            'consumption' => $aggregator->forOrder($order),
        ]);
    }

    public function destroy(Order $order): RedirectResponse
    {
        $this->authorize('delete', $order);

        $order->delete();

        return redirect()
            ->route('admin.orders.index')
            ->with('status', "Zakázka „{$order->name}“ byla smazána.");
    }
}
