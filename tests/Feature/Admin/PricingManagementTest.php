<?php

use App\Livewire\Configurators\PillowConfigurator;
use App\Models\Order;
use App\Models\PricingVariable;
use App\Models\Ribbon;
use App\Models\User;
use Database\Seeders\PricingVariableSeeder;
use Livewire\Livewire;

it('shows the pricing page with variables, units and ribbons', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.pricing.index'))
        ->assertOk()
        ->assertSee('Úkony a položky')
        ->assertSee('Řasící stuhy')
        ->assertSee('PRIPRAVA')
        ->assertSee('VYPINANY_PODHLED')
        ->assertSee('Kč/m²');
});

it('updates pricing variables in bulk and affects new calculations', function () {
    $admin = User::factory()->admin()->create();

    $values = PricingVariable::query()->pluck('value', 'code')->all();
    $values['POLSTAR_ZAKLAD'] = 50; // z 10 na 50

    $this->actingAs($admin)
        ->put(route('admin.pricing.variables.update'), ['values' => $values])
        ->assertRedirect(route('admin.pricing.index'));

    expect(PricingVariable::query()->where('code', 'POLSTAR_ZAKLAD')->sole()->value)->toBe(50.0);

    // Nová kalkulace už počítá s novou cenou: 50 + 9×10 + ušití 93 = 233.
    $order = Order::factory()->create();

    Livewire::actingAs($order->user)
        ->test(PillowConfigurator::class, ['order' => $order])
        ->set('material', 'bavlna')
        ->set('width', 45)
        ->set('height', 38)
        ->assertSet('unitPrice', 233.0);
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

it('adds a custom price list row with a generated code', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->post(route('admin.pricing.variables.store'), ['name' => 'Montáž stropu', 'unit' => 'm²', 'value' => 250])
        ->assertRedirect(route('admin.pricing.index'));

    $variable = PricingVariable::query()->where('code', 'MONTAZ_STROPU')->sole();

    expect($variable->value)->toBe(250.0)
        ->and($variable->unit)->toBe('m²')
        ->and($variable->is_system)->toBeFalse();
});

it('suffixes the generated code when it is already taken', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->post(route('admin.pricing.variables.store'), ['name' => 'Doprava', 'value' => 20]);

    expect(PricingVariable::query()->where('code', 'DOPRAVA_2')->exists())->toBeTrue();
});

it('deletes a custom row but never a system one', function () {
    $admin = User::factory()->admin()->create();
    $custom = PricingVariable::create(['code' => 'VLASTNI', 'name' => 'Vlastní', 'value' => 1, 'is_system' => false]);
    $system = PricingVariable::query()->where('code', 'PRIPRAVA')->sole();

    $this->actingAs($admin)
        ->delete(route('admin.pricing.variables.destroy', $custom))
        ->assertRedirect(route('admin.pricing.index'));

    $this->actingAs($admin)
        ->delete(route('admin.pricing.variables.destroy', $system))
        ->assertForbidden();

    expect(PricingVariable::find($custom->id))->toBeNull()
        ->and(PricingVariable::find($system->id))->not->toBeNull();
});

it('keeps values edited by the workshop when the seeder runs again', function () {
    PricingVariable::query()->where('code', 'PRIPRAVA')->update(['value' => 99]);

    (new PricingVariableSeeder)->run();

    expect(PricingVariable::query()->where('code', 'PRIPRAVA')->value('value'))->toBe(99.0);
});

it('blocks non-admins from the pricing page', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.pricing.index'))
        ->assertForbidden();
});
