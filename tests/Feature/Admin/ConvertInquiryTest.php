<?php

use App\Enums\InquiryStatus;
use App\Enums\OrderStatus;
use App\Models\Inquiry;
use App\Models\Order;
use App\Models\User;

it('converts an inquiry into a draft order with the configured item', function () {
    $inquiry = Inquiry::factory()->withConfiguration()->create();
    $designer = User::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->post(route('admin.inquiries.convert', $inquiry), [
            'user_id' => $designer->id,
            'name' => 'Poptávka – Jana',
        ])
        ->assertRedirect(route('admin.inquiries.show', $inquiry));

    $inquiry->refresh();
    $order = $inquiry->order;

    expect($order)->not->toBeNull()
        ->and($order->status)->toBe(OrderStatus::Draft)
        ->and($order->user_id)->toBe($designer->id)
        ->and($order->name)->toBe('Poptávka – Jana')
        ->and($order->items)->toHaveCount(1)
        ->and($order->items->first()->type->value)->toBe('pillow')
        ->and($order->materials_note)->toContain($inquiry->phone)
        ->and($inquiry->status)->toBe(InquiryStatus::Contacted);
});

it('creates the order without an item when the snapshot no longer prices', function () {
    $inquiry = Inquiry::factory()->create([
        'configuration' => [
            'type' => 'curtain',
            'parameters' => [
                'width' => 200, 'height' => 150, 'material_width' => 300,
                'ribbon_code' => 'zrusena-stuha', 'ratio' => 2,
                'kanyr' => false, 'right_left' => 'right',
                'corners' => false, 'bottom' => false, 'narasit' => false, 'snails' => false,
            ],
            'count' => 1,
            'summary' => 'Záclona · 200 × 150 cm · 1 ks',
        ],
    ]);
    $designer = User::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->post(route('admin.inquiries.convert', $inquiry), [
            'user_id' => $designer->id,
            'name' => 'Poptávka bez položky',
        ])
        ->assertRedirect(route('admin.inquiries.show', $inquiry));

    $order = $inquiry->refresh()->order;

    expect($order)->not->toBeNull()
        ->and($order->items)->toHaveCount(0);
});

it('prevents converting an inquiry twice', function () {
    $inquiry = Inquiry::factory()->convertedToOrder()->create();
    $designer = User::factory()->create();

    $ordersBefore = Order::count();

    $this->actingAs(User::factory()->admin()->create())
        ->from(route('admin.inquiries.show', $inquiry))
        ->post(route('admin.inquiries.convert', $inquiry), [
            'user_id' => $designer->id,
            'name' => 'Duplicitní pokus',
        ])
        ->assertRedirect(route('admin.inquiries.show', $inquiry))
        ->assertSessionHasErrors('user_id');

    expect(Order::count())->toBe($ordersBefore);
});

it('rejects admins and inactive users as target designers', function () {
    $inquiry = Inquiry::factory()->create();
    $admin = User::factory()->admin()->create();
    $inactive = User::factory()->inactive()->create();

    $this->actingAs($admin)
        ->post(route('admin.inquiries.convert', $inquiry), ['user_id' => $admin->id, 'name' => 'X'])
        ->assertSessionHasErrors('user_id');

    $this->actingAs($admin)
        ->post(route('admin.inquiries.convert', $inquiry), ['user_id' => $inactive->id, 'name' => 'X'])
        ->assertSessionHasErrors('user_id');

    expect($inquiry->refresh()->order_id)->toBeNull();
});
