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

it('lists customer before order and shows prices without and with VAT', function () {
    $customer = User::factory()->create(['name' => 'Studio Lucie']);
    Order::factory()->submitted()->for($customer)->create([
        'name' => 'Závěsy obývák',
        'total_price' => 1000,
        'vat_rate' => 21,
    ]);

    $this->actingAs(admin())
        ->get(route('admin.orders.index'))
        ->assertOk()
        // Celé tagy, aby nesplývaly se stejným textem ve filtru nad tabulkou.
        ->assertSeeInOrder(['<th class="w-px whitespace-nowrap px-5 py-3">Zákazník</th>', '<th class="w-px whitespace-nowrap px-5 py-3">Zakázka</th>', 'Cena bez DPH</th>', 'Cena s DPH</th>'], false)
        ->assertSeeInOrder(['<td class="w-px whitespace-nowrap px-5 py-3">Studio Lucie</td>', 'Závěsy obývák', '1 000,00 Kč', '1 210,00 Kč'], false);
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

it('filters orders by customer', function () {
    $lucie = User::factory()->create(['name' => 'Lucie Designérka']);
    $eva = User::factory()->create(['name' => 'Eva Designérka']);

    Order::factory()->submitted()->for($lucie)->create(['name' => 'Zakázka Lucie']);
    Order::factory()->submitted()->for($eva)->create(['name' => 'Zakázka Evy']);

    $this->actingAs(admin())
        ->get(route('admin.orders.index', ['user_id' => $lucie->id]))
        ->assertOk()
        ->assertSee('Zakázka Lucie')
        ->assertDontSee('Zakázka Evy');
});

it('combines the customer filter with the date filter', function () {
    $lucie = User::factory()->create(['name' => 'Lucie Designérka']);

    Order::factory()->submitted()->for($lucie)->create(['name' => 'Stará Lucie', 'submitted_at' => now()->subMonths(2)]);
    Order::factory()->submitted()->for($lucie)->create(['name' => 'Čerstvá Lucie', 'submitted_at' => now()]);
    Order::factory()->submitted()->create(['name' => 'Cizí čerstvá', 'submitted_at' => now()]);

    $this->actingAs(admin())
        ->get(route('admin.orders.index', [
            'user_id' => $lucie->id,
            'submitted_from' => now()->subWeek()->toDateString(),
        ]))
        ->assertOk()
        ->assertSee('Čerstvá Lucie')
        ->assertDontSee('Stará Lucie')
        ->assertDontSee('Cizí čerstvá');
});

it('offers only customers with submitted orders in the filter', function () {
    $sOdeslanou = User::factory()->create(['name' => 'Klara Sodeslanou']);
    $jenDraft = User::factory()->create(['name' => 'Petra Jendraft']);
    User::factory()->create(['name' => 'Nikola Bezzakazky']);

    Order::factory()->submitted()->for($sOdeslanou)->create();
    Order::factory()->for($jenDraft)->create();

    $this->actingAs(admin())
        ->get(route('admin.orders.index'))
        ->assertOk()
        ->assertSee('Klara Sodeslanou')
        ->assertDontSee('Petra Jendraft')
        ->assertDontSee('Nikola Bezzakazky');
});

it('rejects a filter by a non-existent customer', function () {
    $this->actingAs(admin())
        ->get(route('admin.orders.index', ['user_id' => 999999]))
        ->assertSessionHasErrors('user_id');
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

it('renders a printable production sheet with parameters and the schema', function () {
    $order = Order::factory()->submitted()->create(['name' => 'Novákovi – obývák', 'materials_note' => 'voal bílý – 9 m']);
    OrderItem::factory()->for($order)->create(['notes' => 'Pozor na vzor']);

    $this->actingAs(admin())
        ->get(route('admin.orders.sheet', $order))
        ->assertOk()
        ->assertSee('Výrobní list')
        ->assertSee('Novákovi – obývák')
        ->assertSee('voal bílý – 9 m')
        ->assertSee('Šířka')
        ->assertSee('50 cm')
        ->assertSee('Pozor na vzor')
        ->assertSee('<svg', false);
});

it('blocks non-admins from the production sheet', function () {
    $order = Order::factory()->submitted()->create();

    $this->actingAs($order->user)
        ->get(route('admin.orders.sheet', $order))
        ->assertForbidden();
});
