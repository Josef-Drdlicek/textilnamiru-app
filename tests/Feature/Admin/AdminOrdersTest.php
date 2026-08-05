<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;

function admin(): User
{
    return User::factory()->admin()->create();
}

it('shows submitted orders grouped into four sections and hides drafts', function () {
    Order::factory()->create(['name' => 'Rozpracovaný draft']);
    Order::factory()->submitted()->create(['name' => 'Nová zakázka A']);
    Order::factory()->submitted()->status(OrderStatus::Accepted)->create(['name' => 'Přijatá zakázka B']);

    $this->actingAs(admin())
        ->get(route('admin.orders.index'))
        ->assertOk()
        ->assertSee('Nové zakázky')
        ->assertSee('Přijaté zakázky')
        ->assertSee('Zakázky k fakturaci')
        ->assertSee('Hotové zakázky')
        ->assertSee('Nová zakázka A')
        ->assertSee('Přijatá zakázka B')
        ->assertDontSee('Rozpracovaný draft');
});

it('filters orders by submission date', function () {
    Order::factory()->submitted()->create(['name' => 'Stará zakázka', 'submitted_at' => now()->subMonths(2)]);
    Order::factory()->submitted()->create(['name' => 'Čerstvá zakázka', 'submitted_at' => now()]);

    $this->actingAs(admin())
        ->get(route('admin.orders.index', ['submitted_from' => now()->subWeek()->toDateString()]))
        ->assertOk()
        ->assertSee('Čerstvá zakázka')
        ->assertDontSee('Stará zakázka');
});

it('walks an order through the whole workflow with an audit trail', function () {
    $order = Order::factory()->submitted()->create();
    $boss = admin();

    foreach ([OrderStatus::Accepted, OrderStatus::ToInvoice, OrderStatus::Done] as $target) {
        $this->actingAs($boss)
            ->post(route('admin.orders.status', $order), ['status' => $target->value])
            ->assertSessionHasNoErrors();

        expect($order->refresh()->status)->toBe($target);
    }

    $trail = $order->statusChanges()->pluck('to_status');

    expect($trail->all())->toEqual([OrderStatus::Accepted, OrderStatus::ToInvoice, OrderStatus::Done])
        ->and($order->statusChanges()->first()->user_id)->toBe($boss->id);
});

it('rejects a skipped transition (Nová → Hotová)', function () {
    $order = Order::factory()->submitted()->create();

    $this->actingAs(admin())
        ->post(route('admin.orders.status', $order), ['status' => OrderStatus::Done->value])
        ->assertSessionHasErrors('status');

    expect($order->refresh()->status)->toBe(OrderStatus::New);
});

it('rejects any transition from the terminal state', function () {
    $order = Order::factory()->submitted()->status(OrderStatus::Done)->create();

    $this->actingAs(admin())
        ->post(route('admin.orders.status', $order), ['status' => OrderStatus::Accepted->value])
        ->assertSessionHasErrors('status');

    expect($order->refresh()->status)->toBe(OrderStatus::Done);
});

it('blocks non-admins from transitioning status', function () {
    $order = Order::factory()->submitted()->create();

    $this->actingAs($order->user)
        ->post(route('admin.orders.status', $order), ['status' => OrderStatus::Accepted->value])
        ->assertForbidden();
});

it('shows the order detail with customer contact, consumption and audit', function () {
    $order = Order::factory()->submitted()->create();
    $order->user->update(['phone' => '+420 601 602 603', 'address' => 'Studio Náměstí 1']);

    OrderItem::factory()->for($order)->create([
        'material' => 'Voal', 'consumed_meters' => 4.2, 'count' => 2,
    ]);
    OrderItem::factory()->for($order)->create([
        'material' => 'voal ', 'consumed_meters' => 1.9, 'count' => 1,
    ]);

    $this->actingAs(admin())
        ->get(route('admin.orders.show', $order))
        ->assertOk()
        ->assertSee('+420 601 602 603')
        ->assertSee('Studio Náměstí 1')
        ->assertSee('Voal')
        ->assertSee('10,30'); // 4,2×2 + 1,9 = 10,3 m — „Voal" a „voal " se sčítají
});

it('lets an admin delete an order in any phase', function () {
    $order = Order::factory()->submitted()->status(OrderStatus::Done)->create();

    $this->actingAs(admin())
        ->delete(route('admin.orders.destroy', $order))
        ->assertRedirect(route('admin.orders.index'));

    $this->assertSoftDeleted($order);
});
