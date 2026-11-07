<?php

use App\Enums\ItemType;
use App\Livewire\Configurators\PillowConfigurator;
use App\Models\Order;
use App\Models\User;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

function pillowComponent(Order $order): Testable
{
    return Livewire::actingAs($order->user)
        ->test(PillowConfigurator::class, ['order' => $order])
        ->set('material', 'bavlna')
        ->set('width', 45)
        ->set('height', 38);
}

it('recalculates the price live on the server', function () {
    $order = Order::factory()->create();

    pillowComponent($order)
        ->assertSet('unitPrice', 193.0)
        ->assertSet('unitPriceVat', 233.53)
        ->set('stuffing', true)
        ->assertSet('unitPrice', 356.0)
        ->set('count', 3)
        ->assertSet('totalPrice', 1068.0);
});

it('hides the price while inputs are invalid', function () {
    $order = Order::factory()->create();

    pillowComponent($order)
        ->set('width', null)
        ->assertSet('unitPrice', null)
        ->assertSet('priceLines', []);
});

it('saves the item with a server-calculated price', function () {
    $order = Order::factory()->create();

    pillowComponent($order)
        ->set('count', 2)
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('orders.show', $order));

    $item = $order->items()->sole();

    expect($item)
        ->type->toBe(ItemType::Pillow)
        ->material->toBe('bavlna')
        ->count->toBe(2)
        ->unit_price->toBe(193.0)
        ->total_price->toBe(386.0)
        ->consumed_meters->toBe(0.0)
        ->and($item->parameters->width)->toBe(45.0);
});

it('ignores a tampered price — the stored price always comes from the calculator', function () {
    $order = Order::factory()->create();

    pillowComponent($order)
        ->set('unitPrice', 0.01)
        ->set('totalPrice', 0.01)
        ->call('save')
        ->assertHasNoErrors();

    expect($order->items()->sole()->unit_price)->toBe(193.0);
});

it('validates dimensions within 10–100 cm', function () {
    $order = Order::factory()->create();

    pillowComponent($order)
        ->set('width', 150)
        ->assertHasErrors('width')
        ->call('save')
        ->assertHasErrors('width');

    expect($order->items()->count())->toBe(0);
});

it('requires the material', function () {
    $order = Order::factory()->create();

    pillowComponent($order)
        ->set('material', '')
        ->call('save')
        ->assertHasErrors('material');
});

it('loads a stored item for editing and reprices it on save', function () {
    $order = Order::factory()->create();

    pillowComponent($order)->call('save');
    $item = $order->items()->sole();

    Livewire::actingAs($order->user)
        ->test(PillowConfigurator::class, ['order' => $order, 'item' => $item])
        ->assertSet('width', 45.0)
        ->assertSet('material', 'bavlna')
        ->set('stuffing', true)
        ->call('save')
        ->assertHasNoErrors();

    expect($item->refresh()->unit_price)->toBe(356.0);
});

it('refuses configuration of a foreign order (IDOR)', function () {
    $order = Order::factory()->create();
    $intruder = User::factory()->create();

    Livewire::actingAs($intruder)
        ->test(PillowConfigurator::class, ['order' => $order])
        ->assertForbidden();
});

it('refuses configuration of a submitted order', function () {
    $order = Order::factory()->submitted()->create();

    Livewire::actingAs($order->user)
        ->test(PillowConfigurator::class, ['order' => $order])
        ->assertForbidden();
});
