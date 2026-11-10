<?php

use App\Enums\PricingCategory;
use App\Models\PricingVariable;
use App\Models\User;

it('shows the sewing price list under its new name with links to the other price lists', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.pricing.index'))
        ->assertOk()
        ->assertSeeInOrder(['Ceník šití', 'Ceník stropů', 'Ceník rolet']);
});

it('shows the placeholder page for the blind price list to the admin', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.blind-pricing.index'))
        ->assertOk()
        ->assertSee('Ceník rolet')
        ->assertSee('Tento ceník připravujeme');
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
})->with(['admin.ceiling-pricing.index', 'admin.blind-pricing.index']);
