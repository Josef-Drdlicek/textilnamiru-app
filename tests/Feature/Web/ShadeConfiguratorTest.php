<?php

use App\Livewire\Web\ShadeConfigurator;
use App\Models\ShadeFabric;
use App\Models\ShadeProduct;
use App\Models\ShadeVariant;
use Livewire\Livewire;

function pricedShade(): ShadeVariant
{
    $product = ShadeProduct::query()->create(['name' => 'Roleta Leontina', 'slug' => 'roleta-leontina', 'is_active' => true]);
    $product->variants()->create(['name' => 'Leontina', 'sort_order' => 0]);
    $variant = $product->variants()->create([
        'name' => 'Leontina Box (v kazetě)', 'sort_order' => 1,
        'min_width' => 40, 'max_width' => 150, 'min_height' => 40, 'max_height' => 200, 'step' => 10,
        'fabric_collections' => ['standard'],
    ]);
    $variant->prices()->createMany([
        ['price_category' => 'A', 'width' => 100, 'height' => 150, 'price' => 1000],
        ['price_category' => 'A', 'width' => 110, 'height' => 150, 'price' => 1100],
        ['price_category' => 'C', 'width' => 110, 'height' => 150, 'price' => 1500],
    ]);

    return $variant->refresh();
}

function shadeFabric(string $name, string $category, ?int $rollWidth = 200, string $collection = 'standard', bool $active = true): ShadeFabric
{
    return ShadeFabric::query()->create([
        'code' => $name, 'name' => $name, 'slug' => \Illuminate\Support\Str::slug($name),
        'collection' => $collection, 'price_category' => $category, 'roll_width' => $rollWidth, 'is_active' => $active,
        'specs' => [['label' => 'Gramáž', 'value' => '175 g/m2'], ['label' => 'Světelná propustnost', 'value' => 'průsvitné']],
    ]);
}

it('prices the blind from the size grid of the fabric price category', function () {
    $col = shadeFabric('Col 100', 'A');
    $juno = shadeFabric('Juno 2276', 'C');
    $juno->update(['image_path' => 'shades/pavon/latky/juno-2276.jpg']);

    Livewire::test(ShadeConfigurator::class, ['variant' => pricedShade(), 'variantIndex' => 1])
        ->assertSee('Zadejte rozměr, vyberte látku a cena se hned objeví')
        ->set('width', 101)
        ->set('height', 145)
        ->assertSet('totalPrice', null)
        ->call('openStep', 'fabric')
        ->set('fabricId', $juno->id)
        ->assertSet('totalPrice', 1500.0)
        ->assertSee('Juno 2276 · skupina C')
        ->assertSeeInOrder(['sendToInquiry', 'fabric-detail-', 'Juno 2276', '175 g'], false)
        ->assertDispatched('textile-color-changed')
        ->set('fabricId', $col->id)
        ->assertSet('totalPrice', 1100.0)
        ->assertSet('totalPriceVat', 1331.0)
        ->assertSee('1 331')
        ->assertSee('látka Col 100 – skupina A (ceník do 110 × 150 cm)')
        ->set('count', 2)
        ->assertSet('totalPriceVat', 2662.0);
});

it('rejects a size outside the variant range and clears the price', function () {
    $fabric = shadeFabric('Col 100', 'A');

    Livewire::test(ShadeConfigurator::class, ['variant' => pricedShade()])
        ->set('fabricId', $fabric->id)
        ->set('width', 100)
        ->set('height', 150)
        ->assertSet('totalPrice', 1000.0)
        ->set('width', 151)
        ->assertHasErrors('width')
        ->assertSet('totalPrice', null);
});

it('asks for an individual quote when the grid cell is still empty', function () {
    $fabric = shadeFabric('Col 100', 'B');

    Livewire::test(ShadeConfigurator::class, ['variant' => pricedShade()])
        ->set('fabricId', $fabric->id)
        ->set('width', 140)
        ->set('height', 190)
        ->assertSet('priceMissing', true)
        ->assertSee('naceníme individuálně');
});

it('tells the customer the price list is being prepared', function () {
    $variant = pricedShade();
    $variant->prices()->delete();

    Livewire::test(ShadeConfigurator::class, ['variant' => $variant])
        ->assertSee('Ceník tohoto provedení doplňujeme');
});

it('offers only active fabrics of the variant collections, grouped by price category', function () {
    shadeFabric('Col 100', 'A');
    shadeFabric('Juno 2276', 'C');
    shadeFabric('Skrytá 1', 'A', active: false);
    shadeFabric('Caria 0300', 'C', collection: 'magico');

    Livewire::test(ShadeConfigurator::class, ['variant' => pricedShade()])
        ->call('openStep', 'fabric')
        ->assertSee('Cenová skupina A')
        ->assertSee('Cenová skupina C')
        ->assertDontSee('Cenová skupina B')
        ->assertSeeInOrder(['Col 100', 'Juno 2276'])
        ->assertDontSee('Skrytá 1')
        ->assertDontSee('Caria 0300');
});

it('rejects a fabric that is narrower than the blind', function () {
    $narrow = shadeFabric('Úzká 1', 'A', rollWidth: 120);

    Livewire::test(ShadeConfigurator::class, ['variant' => pricedShade()])
        ->set('height', 150)
        ->set('width', 100)
        ->call('openStep', 'fabric')
        ->set('fabricId', $narrow->id)
        ->assertHasNoErrors('fabricId')
        ->set('width', 130)
        ->assertHasErrors('fabricId')
        ->assertSee('jen do šířky 120 cm')
        ->assertSet('totalPrice', null);
});

it('sends the blind configuration to the inquiry and keeps it with the submitted inquiry', function () {
    $fabric = shadeFabric('Col 100', 'A');

    Livewire::test(ShadeConfigurator::class, ['variant' => pricedShade(), 'variantIndex' => 1])
        ->call('sendToInquiry')
        ->assertHasErrors(['width', 'height', 'fabricId'])
        ->set('fabricId', $fabric->id)
        ->set('width', 100)
        ->set('height', 150)
        ->call('sendToInquiry')
        ->assertRedirect(route('web.inquiry.create'));

    $snapshot = session('inquiry.configuration');
    expect($snapshot)
        ->product->toBe('shades')
        ->summary->toBe('Roleta Leontina · Leontina Box (v kazetě) · 100 × 150 cm · Col 100 · skupina A · 1 ks')
        ->total_price_vat->toEqual(1210)
        ->edit_url->toBe(route('web.rolety.show', ['roleta-leontina', 'varianta' => 1]).'#vypocet-ceny')
        ->and($snapshot['parameters'])->toMatchArray(['variant' => 'Roleta Leontina – Leontina Box (v kazetě)', 'fabric' => 'Col 100 (skupina A)', 'width' => 100.0, 'height' => 150.0]);

    $this->get(route('web.inquiry.create'))
        ->assertOk()
        ->assertSee('Roleta Leontina · Leontina Box (v kazetě)')
        ->assertSee('1 210')
        ->assertSee('#vypocet-ceny', false)
        ->assertViewHas('preselectedProducts', ['shades']);
});

it('shows the price calculator on the blind page for the chosen variant', function () {
    pricedShade();

    $this->get(route('web.rolety.show', ['roleta-leontina', 'varianta' => 1]))
        ->assertOk()
        ->assertSee('Výpočet ceny')
        ->assertSee('id="vypocet-ceny"', false)
        ->assertSee('Šířka')
        ->assertSee('(40–150 cm)')
        ->assertSeeLivewire(ShadeConfigurator::class);

    // Neplatné ?varianta= spadne na první provedení.
    $this->get(route('web.rolety.show', ['roleta-leontina', 'varianta' => 9]))
        ->assertOk()
        ->assertSee('Ceník tohoto provedení doplňujeme');
});

it('quotes an empty price cell on request instead of using a bigger one', function () {
    $variant = pricedShade();
    $col = shadeFabric('Col 100', 'A');

    // 95 × 145 cm patří do políčka 100 × 150 (vyplněné), 105 × 145 do 110 × 150 (vyplněné),
    // 85 × 145 do 90 × 150, které je prázdné: cena na poptávku, ne cena větší rolety.
    $pricer = app(\App\Domain\Pricing\ShadePricer::class);
    expect($pricer->price($variant, $col, 95, 145)?->unitPrice)->toBe(1000.0)
        ->and($pricer->price($variant, $col, 105, 145)?->unitPrice)->toBe(1100.0)
        ->and($pricer->price($variant, $col, 85, 145))->toBeNull();
});

it('ignores prices left outside the grid after the step changes', function () {
    $variant = pricedShade();
    $col = shadeFabric('Col 100', 'A');
    $variant->prices()->create(['price_category' => 'A', 'width' => 45, 'height' => 150, 'price' => 1]);

    $pricer = app(\App\Domain\Pricing\ShadePricer::class);
    expect($pricer->lowestPrice($variant))->toBe(1000.0)
        ->and($pricer->price($variant, $col, 42, 145))->toBeNull();
});