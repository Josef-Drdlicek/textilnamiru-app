<?php

use App\Enums\OrderStatus;
use App\Livewire\Configurators\PillowConfigurator;
use App\Models\Order;
use App\Models\PricingVariable;
use App\Models\User;
use Livewire\Livewire;

function draftWithPillow(): Order
{
    $order = Order::factory()->create();

    Livewire::actingAs($order->user)
        ->test(PillowConfigurator::class, ['order' => $order])
        ->set('material', 'bavlna')
        ->set('width', 45)
        ->set('height', 38)
        ->set('count', 2)
        ->call('save');

    return $order->refresh();
}

it('shows the recap with materials and deadline form', function () {
    $order = draftWithPillow();

    $this->actingAs($order->user)
        ->get(route('orders.materials', $order))
        ->assertOk()
        ->assertSee('Závazné objednání')
        ->assertSee('Polštář');
});

it('submits the order: freezes totals, VAT and records the audit trail', function () {
    $order = draftWithPillow();

    $this->actingAs($order->user)
        ->post(route('orders.submit', $order), [
            'deadline' => now()->addMonth()->toDateString(),
            'materials_note' => 'bavlna režná – 3 m',
        ])
        ->assertRedirect(route('orders.show', $order));

    $order->refresh();

    expect($order)
        ->status->toBe(OrderStatus::New)
        ->total_price->toBe(386.0)
        ->vat_rate->toBe(21.0)
        ->materials_note->toBe('bavlna režná – 3 m')
        ->submitted_at->not->toBeNull();

    $change = $order->statusChanges()->sole();

    expect($change)
        ->from_status->toBe(OrderStatus::Draft)
        ->to_status->toBe(OrderStatus::New)
        ->user_id->toBe($order->user_id);
});

it('keeps frozen prices when the price list changes later', function () {
    $order = draftWithPillow();

    $this->actingAs($order->user)->post(route('orders.submit', $order), [
        'deadline' => now()->addMonth()->toDateString(),
    ]);

    // Dílna později zdraží — odeslané zakázky se nesmí pohnout.
    PricingVariable::query()->update(['value' => 999]);

    $order->refresh();

    expect($order->total_price)->toBe(386.0)
        ->and($order->items()->sole()->unit_price)->toBe(193.0);
});

it('requires a future deadline', function () {
    $order = draftWithPillow();

    $this->actingAs($order->user)
        ->post(route('orders.submit', $order), ['deadline' => now()->subDay()->toDateString()])
        ->assertSessionHasErrors('deadline');

    expect($order->refresh()->status)->toBe(OrderStatus::Draft);
});

it('refuses to submit an order without items', function () {
    $order = Order::factory()->create();

    $this->actingAs($order->user)
        ->post(route('orders.submit', $order), ['deadline' => now()->addMonth()->toDateString()])
        ->assertRedirect(route('orders.show', $order));

    expect($order->refresh()->status)->toBe(OrderStatus::Draft);
});

it('refuses to submit an already submitted order', function () {
    $order = Order::factory()->submitted()->create();

    $this->actingAs($order->user)
        ->post(route('orders.submit', $order), ['deadline' => now()->addMonth()->toDateString()])
        ->assertForbidden();
});

it('refuses submission by a foreign user (IDOR)', function () {
    $order = draftWithPillow();
    $intruder = User::factory()->create();

    $this->actingAs($intruder)
        ->post(route('orders.submit', $order), ['deadline' => now()->addMonth()->toDateString()])
        ->assertForbidden();
});
