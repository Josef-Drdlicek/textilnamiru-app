<?php

namespace App\Http\Controllers;

use App\Enums\ItemType;
use App\Livewire\Configurators;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\View\View;

/**
 * Zobrazení konfigurátoru — vlastní logika (přepočet, uložení) žije
 * v Livewire komponentách {@see Configurators}.
 */
class ConfiguratorController extends Controller
{
    public function create(Order $order, ItemType $type): View
    {
        $this->authorize('update', $order);

        return view('orders.configure', [
            'order' => $order,
            'type' => $type,
            'item' => null,
        ]);
    }

    public function edit(Order $order, OrderItem $item): View
    {
        $this->authorize('update', $order);

        return view('orders.configure', [
            'order' => $order,
            'type' => $item->type,
            'item' => $item,
        ]);
    }
}
