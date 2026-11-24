<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;

it('creates a draft order and redirects to it', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('orders.store'), ['name' => 'Novákovi – obývák']);

    $order = Order::sole();

    $response->assertRedirect(route('orders.show', $order));

    expect($order)
        ->name->toBe('Novákovi – obývák')
        ->status->toBe(OrderStatus::Draft)
        ->user_id->toBe($user->id);
});

it('requires a name to create an order', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('orders.store'), ['name' => ''])
        ->assertSessionHasErrors('name');

    expect(Order::count())->toBe(0);
});

it('lists submitted orders on the welcome page and quotes on their own page', function () {
    $user = User::factory()->create();
    $quote = Order::factory()->for($user)->create(['name' => 'Rozpracovaná zakázka']);
    OrderItem::factory()->for($quote)->create(['total_price' => 100.0]);
    $submitted = Order::factory()->for($user)->submitted()->create(['name' => 'Odeslaná zakázka']);

    $this->actingAs($user)
        ->get(route('orders.welcome'))
        ->assertOk()
        ->assertSeeInOrder(['Nová zakázka', 'Zakázky', 'Odeslaná zakázka'])
        ->assertSee($submitted->status->label())
        ->assertDontSee('Rozpracovaná zakázka');

    $this->actingAs($user)
        ->get(route('orders.quotes'))
        ->assertOk()
        ->assertSeeInOrder(['Cenové nabídky', 'Rozpracovaná zakázka', '100,00 Kč', '121,00 Kč'])
        ->assertDontSee('Odeslaná zakázka');
});

it('shows only the studio its own quotes', function () {
    Order::factory()->create(['name' => 'Cizí nabídka']);

    $this->actingAs(User::factory()->create())
        ->get(route('orders.quotes'))
        ->assertOk()
        ->assertDontSee('Cizí nabídka');
});

it('defers an order with materials and an optional deadline', function () {
    $order = Order::factory()->create();

    $this->actingAs($order->user)
        ->post(route('orders.defer', $order), ['materials_note' => 'voal bílý – 9 m'])
        ->assertRedirect(route('orders.quotes'));

    expect($order->refresh())
        ->status->toBe(OrderStatus::Draft)
        ->deferred_at->not->toBeNull()
        ->materials_note->toBe('voal bílý – 9 m')
        ->deadline->toBeNull();
});

it('refuses to defer a submitted order', function () {
    $order = Order::factory()->submitted()->create();

    $this->actingAs($order->user)
        ->post(route('orders.defer', $order))
        ->assertForbidden();
});

it('lets the owner delete a done order', function () {
    $order = Order::factory()->submitted()->status(OrderStatus::Done)->create();

    $this->actingAs($order->user)
        ->delete(route('orders.destroy', $order))
        ->assertRedirect(route('orders.welcome'));

    $this->assertSoftDeleted($order);
});

it('shows an order to its owner', function () {
    $order = Order::factory()->create();

    $this->actingAs($order->user)
        ->get(route('orders.show', $order))
        ->assertOk()
        ->assertSee($order->name);
});

it('denies access to a foreign order (IDOR)', function () {
    $order = Order::factory()->create();
    $intruder = User::factory()->create();

    $this->actingAs($intruder)
        ->get(route('orders.show', $order))
        ->assertForbidden();
});

it('lets an admin view any order', function () {
    $order = Order::factory()->create();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('orders.show', $order))
        ->assertOk();
});

it('lets the owner cancel a draft', function () {
    $order = Order::factory()->create();

    $this->actingAs($order->user)
        ->delete(route('orders.destroy', $order))
        ->assertRedirect(route('orders.quotes'));

    $this->assertSoftDeleted($order);
});

it('prevents the owner from deleting a submitted order', function () {
    $order = Order::factory()->submitted()->create();

    $this->actingAs($order->user)
        ->delete(route('orders.destroy', $order))
        ->assertForbidden();

    $this->assertNotSoftDeleted($order);
});

it('prevents a foreign user from cancelling an order (IDOR)', function () {
    $order = Order::factory()->create();
    $intruder = User::factory()->create();

    $this->actingAs($intruder)
        ->delete(route('orders.destroy', $order))
        ->assertForbidden();
});
