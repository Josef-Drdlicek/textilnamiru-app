<?php

use App\Enums\ItemType;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;

it('renders the configurator page for every product type', function (ItemType $type) {
    $order = Order::factory()->create();

    $this->actingAs($order->user)
        ->get(route('items.create', [$order, $type]))
        ->assertOk()
        ->assertSee($type->label());
})->with(ItemType::cases());

it('refuses the configurator page for a foreign order (IDOR)', function () {
    $order = Order::factory()->create();
    $intruder = User::factory()->create();

    $this->actingAs($intruder)
        ->get(route('items.create', [$order, ItemType::Pillow]))
        ->assertForbidden();
});

it('refuses the configurator page for a submitted order', function () {
    $order = Order::factory()->submitted()->create();

    $this->actingAs($order->user)
        ->get(route('items.create', [$order, ItemType::Pillow]))
        ->assertForbidden();
});

it('renders the edit page for an existing item', function () {
    $item = OrderItem::factory()->create();

    $this->actingAs($item->order->user)
        ->get(route('items.edit', [$item->order, $item]))
        ->assertOk();
});

it('deletes an item from a draft order', function () {
    $item = OrderItem::factory()->create();

    $this->actingAs($item->order->user)
        ->delete(route('items.destroy', $item))
        ->assertRedirect(route('orders.show', $item->order));

    expect(OrderItem::count())->toBe(0);
});

it('refuses deleting a foreign item (IDOR)', function () {
    $item = OrderItem::factory()->create();
    $intruder = User::factory()->create();

    $this->actingAs($intruder)
        ->delete(route('items.destroy', $item))
        ->assertForbidden();

    expect(OrderItem::count())->toBe(1);
});

it('refuses deleting an item of a submitted order', function () {
    $order = Order::factory()->submitted()->create();
    $item = OrderItem::factory()->for($order)->create();

    $this->actingAs($order->user)
        ->delete(route('items.destroy', $item))
        ->assertForbidden();
});

it('serves the SVG schema of an item to its owner', function () {
    $item = OrderItem::factory()->create();

    $this->actingAs($item->order->user)
        ->get(route('items.schema', $item))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/svg+xml')
        ->assertSee('<svg', escape: false);
});

it('refuses the schema of a foreign item (IDOR)', function () {
    $item = OrderItem::factory()->create();
    $intruder = User::factory()->create();

    $this->actingAs($intruder)
        ->get(route('items.schema', $item))
        ->assertForbidden();
});

it('lets an admin view any item schema', function () {
    $item = OrderItem::factory()->create();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('items.schema', $item))
        ->assertOk();
});
