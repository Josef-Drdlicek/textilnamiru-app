<?php

use App\Enums\RailKind;
use App\Livewire\Web\RailConfigurator;
use App\Models\RailProduct;
use App\Models\RailVariant;
use Livewire\Livewire;

function pricedRail(array $mountings = ['ceiling', 'wall']): RailVariant
{
    $product = RailProduct::query()->create([
        'kind' => RailKind::Rail, 'line' => 'T-Line', 'name' => 'T1 Torino', 'slug' => 't1-torino', 'is_active' => true, 'mountings' => $mountings,
    ]);
    $product->variants()->create(['name' => 'Jednokolejnička rovná', 'code' => 'T1-11', 'sort_order' => 0]);
    $variant = $product->variants()->create(['name' => 'Dvoukolejnička rovná', 'code' => 'T1-21', 'sort_order' => 1]);
    $variant->prices()->createMany([
        ['mounting' => 'ceiling', 'length_to' => 100, 'price' => 400],
        ['mounting' => 'ceiling', 'length_to' => 150, 'price' => 550],
        ['mounting' => 'wall', 'length_to' => 150, 'price' => 600],
    ]);

    return $variant->refresh();
}

it('prices the rail by mounting, length band and number of pieces', function () {
    Livewire::test(RailConfigurator::class, ['variant' => pricedRail(), 'variantIndex' => 1])
        ->assertSee('Stropní držák')
        ->assertSee('Stěnový držák')
        ->set('length', 120)
        ->assertSee('101–150 cm')
        ->assertSet('totalPrice', null)
        ->set('mounting', 'ceiling')
        ->assertSet('totalPrice', 550.0)
        ->assertSet('totalPriceVat', 665.5)
        ->assertSee('T1 Torino – Dvoukolejnička rovná, stropní držák (101–150 cm)')
        ->set('mounting', 'wall')
        ->assertSet('totalPrice', 600.0)
        ->set('count', 2)
        ->assertSet('totalPriceVat', 1452.0);
});

it('rejects a length over 6 m and asks for a quote when the band is empty', function () {
    Livewire::test(RailConfigurator::class, ['variant' => pricedRail()])
        ->set('mounting', 'wall')
        ->set('length', 601)
        ->assertHasErrors('length')
        ->assertSet('totalPrice', null)
        ->set('length', 80)
        ->assertHasNoErrors('length')
        ->assertSet('priceMissing', true)
        ->assertSee('naceníme individuálně');
});

it('preselects the only possible mounting and refuses other ones', function () {
    Livewire::test(RailConfigurator::class, ['variant' => pricedRail(['ceiling'])])
        ->assertSet('mounting', 'ceiling')
        ->assertDontSee('Stěnový držák')
        ->set('length', 90)
        ->assertSet('totalPrice', 400.0)
        ->set('mounting', 'wall')
        ->assertHasErrors('mounting')
        ->assertSet('totalPrice', null);
});

it('tells the customer the price list is being prepared', function () {
    $variant = pricedRail();
    $variant->prices()->delete();

    Livewire::test(RailConfigurator::class, ['variant' => $variant])
        ->assertSee('Ceník doplňujeme');
});

it('sends the rail configuration to the inquiry', function () {
    Livewire::test(RailConfigurator::class, ['variant' => pricedRail(), 'variantIndex' => 1])
        ->call('sendToInquiry')
        ->assertHasErrors(['length', 'mounting'])
        ->set('mounting', 'ceiling')
        ->set('length', 95.5)
        ->call('sendToInquiry')
        ->assertRedirect(route('web.inquiry.create'));

    expect(session('inquiry.configuration'))
        ->product->toBe('rails')
        ->summary->toBe('T1 Torino · Dvoukolejnička rovná · Stropní držák · 95,5 cm · 1 ks')
        ->total_price_vat->toEqual(484)
        ->edit_url->toBe(route('web.zaveseni.show', ['t1-torino', 'varianta' => 1]).'#vypocet-ceny')
        ->parameters->toBe(['variant' => 'T1 Torino – Dvoukolejnička rovná', 'mounting' => 'Stropní držák', 'length' => 95.5]);

    $this->get(route('web.inquiry.create'))
        ->assertOk()
        ->assertSee('T1 Torino · Dvoukolejnička rovná')
        ->assertViewHas('preselectedProducts', ['rails']);
});

it('shows the calculator on the product page for the chosen assembly', function () {
    pricedRail();

    $this->get(route('web.zaveseni.show', ['t1-torino', 'varianta' => 1]))
        ->assertOk()
        ->assertSee('Výpočet ceny')
        ->assertSee('Způsob zavěšení')
        ->assertSee('Délka kolejnice')
        ->assertSeeLivewire(RailConfigurator::class);
});
