<?php

use App\Livewire\Configurators\PillowConfigurator;
use App\Models\BlindPriceBand;
use App\Models\Order;
use App\Models\PricingVariable;
use App\Models\Ribbon;
use App\Models\User;
use Livewire\Livewire;

it('shows the pricing page with all three sections', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.pricing.index'))
        ->assertOk()
        ->assertSee('Úkony a položky')
        ->assertSee('Řasící stuhy')
        ->assertSee('Cenová tabulka římských rolet')
        ->assertSee('PRIPRAVA');
});

it('updates pricing variables in bulk and affects new calculations', function () {
    $admin = User::factory()->admin()->create();

    $values = PricingVariable::query()->pluck('value', 'code')->all();
    $values['POLSTAR_ZAKLAD'] = 50; // z 10 na 50

    $this->actingAs($admin)
        ->put(route('admin.pricing.variables.update'), ['values' => $values])
        ->assertRedirect(route('admin.pricing.index'));

    expect(PricingVariable::query()->where('code', 'POLSTAR_ZAKLAD')->sole()->value)->toBe(50.0);

    // Nová kalkulace už počítá s novou cenou: 50 + 9×10 = 140.
    $order = Order::factory()->create();

    Livewire::actingAs($order->user)
        ->test(PillowConfigurator::class, ['order' => $order])
        ->set('material', 'bavlna')
        ->set('width', 45)
        ->set('height', 38)
        ->assertSet('unitPrice', 140.0);
});

it('rejects a negative price', function () {
    $admin = User::factory()->admin()->create();

    $values = PricingVariable::query()->pluck('value', 'code')->all();
    $values['PRIPRAVA'] = -5;

    $this->actingAs($admin)
        ->put(route('admin.pricing.variables.update'), ['values' => $values])
        ->assertSessionHasErrors();

    expect(PricingVariable::query()->where('code', 'PRIPRAVA')->sole()->value)->toBe(33.0);
});

it('parses czech comma-decimal ratios („1,5; 2")', function () {
    $admin = User::factory()->admin()->create();
    $ribbon = Ribbon::query()->where('code', 'tuzky85')->sole();

    $this->actingAs($admin)
        ->put(route('admin.pricing.ribbons.update', $ribbon), [
            'name' => 'Tužky 85mm',
            'price_per_meter' => 63,
            'allowed_ratios' => '1,5; 2, 2,5',
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.pricing.index'));

    expect($ribbon->refresh()->allowed_ratios)->toEqual([1.5, 2, 2.5]);
});

it('updates a ribbon with dot-decimal ratios', function () {
    $admin = User::factory()->admin()->create();
    $ribbon = Ribbon::query()->where('code', 'tuzky85')->sole();

    $this->actingAs($admin)
        ->put(route('admin.pricing.ribbons.update', $ribbon), [
            'name' => 'Tužky 85mm',
            'price_per_meter' => 70,
            'allowed_ratios' => '1.5, 2, 2.5',
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.pricing.index'));

    $ribbon->refresh();

    expect($ribbon->price_per_meter)->toBe(70.0)
        ->and($ribbon->allowed_ratios)->toEqual([1.5, 2, 2.5]);
});

it('updates the blind price table in bulk', function () {
    $admin = User::factory()->admin()->create();

    $prices = BlindPriceBand::query()->pluck('price', 'id')->all();
    $smallest = BlindPriceBand::query()->where('max_width', 80)->where('max_height', 100)->sole();
    $prices[$smallest->id] = 600;

    $this->actingAs($admin)
        ->put(route('admin.pricing.blind-prices.update'), ['prices' => $prices])
        ->assertRedirect(route('admin.pricing.index'));

    expect($smallest->refresh()->price)->toBe(600.0);
});

it('blocks non-admins from the pricing page', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.pricing.index'))
        ->assertForbidden();
});
