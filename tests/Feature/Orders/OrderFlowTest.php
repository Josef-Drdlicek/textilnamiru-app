<?php

use App\Enums\OrderStatus;
use App\Models\Order;
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

it('lists drafts and submitted orders on the welcome page', function () {
    $user = User::factory()->create();
    $draft = Order::factory()->for($user)->create(['name' => 'Rozpracovaná zakázka']);
    $submitted = Order::factory()->for($user)->submitted()->create(['name' => 'Odeslaná zakázka']);

    $this->actingAs($user)
        ->get(route('orders.welcome'))
        ->assertOk()
        ->assertSee('Rozpracovaná zakázka')
        ->assertSee('Odeslaná zakázka')
        ->assertSee($submitted->status->label());
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
        ->assertRedirect(route('orders.welcome'));

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
