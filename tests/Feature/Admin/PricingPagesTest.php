<?php

use App\Enums\PricingCategory;
use App\Models\PricingVariable;
use App\Models\User;

it('shows the sewing price list under its new name with links to the other price lists', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.pricing.index'))
        ->assertOk()
        ->assertSeeInOrder(['Ceník šití', 'Ceník textilu', 'Ceník stropů', 'Ceník rolet']);
});

it('sends the old blind price list address to the new blinds price list', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.blind-pricing.index'))
        ->assertRedirect('/admin/shade-pricing');
});

it('lists ceiling items on the ceiling price list and keeps them off the sewing one', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.ceiling-pricing.index'))
        ->assertOk()
        ->assertSeeInOrder(['Fólie lesk', 'STROP_FOLIE_LESK', 'Lišta strop', 'LED COB 15 W', 'Dálkový ovladač'])
        ->assertSee('Kč/m²')
        ->assertSee('Kč/bm')
        ->assertSee('Kč/ks')
        ->assertDontSee('PRIPRAVA');

    $this->actingAs($admin)
        ->get(route('admin.pricing.index'))
        ->assertOk()
        ->assertSee('PRIPRAVA')
        ->assertDontSee('STROP_FOLIE_LESK');
});

it('saves ceiling prices and returns to the ceiling price list', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->put(route('admin.pricing.variables.update'), [
            'category' => 'ceiling',
            'values' => ['STROP_FOLIE_LESK' => 450],
        ])
        ->assertRedirect(route('admin.ceiling-pricing.index'));

    expect(PricingVariable::query()->where('code', 'STROP_FOLIE_LESK')->sole()->value)->toBe(450.0);
});

it('adds a custom row to the ceiling price list', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->post(route('admin.pricing.variables.store'), [
            'category' => 'ceiling',
            'name' => 'Montáž lišty',
            'unit' => 'bm',
            'value' => 80,
        ])
        ->assertRedirect(route('admin.ceiling-pricing.index'));

    expect(PricingVariable::query()->where('code', 'MONTAZ_LISTY')->sole()->category)->toBe(PricingCategory::Ceiling);
});

it('hides the placeholder price lists from studios', function (string $route) {
    $this->actingAs(User::factory()->create())
        ->get(route($route))
        ->assertForbidden();
})->with(['admin.textile-pricing.index', 'admin.ceiling-pricing.index', 'admin.shade-pricing.index', 'admin.shade-fabrics.index', 'admin.rail-pricing.index']);

it('shows the rail price list page in the admin menu under the textile price list', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.rail-pricing.index'))
        ->assertOk()
        ->assertSeeInOrder(['Ceník textilu', 'Ceník kolejnic', 'Ceník stropů'])
        ->assertSee('rails:import-detede');
});

it('shows the blinds fabric page in the admin menu under the blinds price list', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.shade-fabrics.index'))
        ->assertOk()
        ->assertSeeInOrder(['Ceník rolet', 'Textil pro rolety', 'Uživatelé'])
        ->assertSee('shades:import-pavon-fabrics');
});

it('keeps the ceiling lighting prices the calculator needs from being deleted', function () {
    $admin = User::factory()->admin()->create();

    foreach (\Database\Seeders\CeilingPricingVariableSeeder::SYSTEM_CODES as $code) {
        $variable = \App\Models\PricingVariable::query()->where('code', $code)->sole();

        $this->actingAs($admin)
            ->delete(route('admin.pricing.variables.destroy', $variable))
            ->assertForbidden();
    }

    $remote = \App\Models\PricingVariable::query()->where('code', 'STROP_DALKOVY_OVLADAC')->sole();
    $this->actingAs($admin)->delete(route('admin.pricing.variables.destroy', $remote));
    expect(\App\Models\PricingVariable::query()->whereKey($remote->id)->exists())->toBeFalse();
});