<?php

use App\Enums\RightLeft;
use App\Livewire\Configurators\CurtainConfigurator;
use App\Livewire\Configurators\DrapeConfigurator;
use App\Models\Order;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

function curtainComponent(Order $order): Testable
{
    return Livewire::actingAs($order->user)
        ->test(CurtainConfigurator::class, ['order' => $order])
        ->set('material', 'voal bílý')
        ->set('width', 200)
        ->set('height', 250)
        ->set('materialWidth', 300)
        ->set('ribbonCode', 'tuzky85')
        ->set('ratio', '2');
}

it('matches the golden curtain price', function () {
    $order = Order::factory()->create();

    curtainComponent($order)->assertSet('unitPrice', 1055.60);
});

it('offers only the ratios allowed for the selected ribbon', function () {
    $order = Order::factory()->create();

    curtainComponent($order)
        ->set('ribbonCode', 'tuzky100')
        ->assertSet('ratio', '2')      // reset na první povolený poměr
        ->set('ratio', '3')            // 3 není pro tuzky100 povolená
        ->assertHasErrors('ratio');
});

it('uses the custom ratio for the custom ribbon', function () {
    $order = Order::factory()->create();

    curtainComponent($order)
        ->set('ribbonCode', 'custom')
        ->assertSet('ratio', null)
        ->set('ratioCustom', 1.7)
        ->set('width', 100)
        ->set('height', 100)
        ->assertSet('unitPrice', 294.00);
});

it('requires an even count for the mirror variant', function () {
    $order = Order::factory()->create();

    curtainComponent($order)
        ->set('rightLeft', RightLeft::Mirror->value)
        ->set('count', 3)
        ->assertHasErrors('count')
        ->set('count', 2)
        ->assertHasNoErrors('count');
});

it('stores consumed meters with the item', function () {
    $order = Order::factory()->create();

    curtainComponent($order)->call('save')->assertHasNoErrors();

    expect($order->items()->sole()->consumed_meters)->toBe(4.2);
});

it('calculates the drape with seam joins (golden value)', function () {
    $order = Order::factory()->create();

    Livewire::actingAs($order->user)
        ->test(DrapeConfigurator::class, ['order' => $order])
        ->set('material', 'závěsovina')
        ->set('width', 200)
        ->set('height', 250)
        ->set('materialWidth', 140)
        ->set('ribbonCode', 'siroky70')
        ->set('ratio', '2')
        ->assertSet('unitPrice', 1437.60);
});
