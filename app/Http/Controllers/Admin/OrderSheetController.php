<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\ItemParameterPresenter;
use Illuminate\View\View;

/**
 * Tiskový výrobní list pro dílnu: hlavička zakázky a ke každé položce
 * nákres, parametry a rozpis ceny na jedné stránce.
 */
class OrderSheetController extends Controller
{
    public function __invoke(Order $order, ItemParameterPresenter $presenter): View
    {
        $this->authorize('view', $order);

        $order->load(['user', 'items']);

        return view('admin.orders.sheet', [
            'order' => $order,
            'presenter' => $presenter,
        ]);
    }
}
