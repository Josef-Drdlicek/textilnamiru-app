<?php

use App\Domain\Pricing\ItemPricer;
use App\Domain\Pricing\PublicPresets;
use App\Enums\ItemType;
use App\Livewire\Web\PriceEstimator;
use App\Models\PricingVariable;
use Livewire\Livewire;

it('prefills reference dimensions and shows a price immediately', function () {
    Livewire::test(PriceEstimator::class)
        ->assertSet('type', 'curtain')
        ->assertSet('width', 100.0)
        ->assertSet('height', 120.0)
        ->assertNotSet('totalPriceVat', null);
});

it('calculates the same price as the pricing engine over public presets', function () {
    $component = Livewire::test(PriceEstimator::class)
        ->set('type', 'pillow')
        ->set('width', 50)
        ->set('height', 50)
        ->set('stuffing', true)
        ->set('count', 2);

    $pricer = app(ItemPricer::class);
    $expected = $pricer->price(
        ItemType::Pillow,
        PublicPresets::estimatorParameters(ItemType::Pillow, [
            'width' => 50.0, 'height' => 50.0, 'ratio' => 2.0, 'stuffing' => true,
        ]),
    );

    $component->assertSet('totalPriceVat', $expected->totalPriceWithVat($pricer->vatRate(), 2));
});

it('hides the price while input is invalid', function () {
    Livewire::test(PriceEstimator::class)
        ->set('width', 5)
        ->assertHasErrors('width')
        ->assertSet('totalPriceVat', null);
});

it('enforces per-type dimension bounds', function () {
    Livewire::test(PriceEstimator::class)
        ->set('type', 'pillow')
        ->set('width', 150)
        ->assertHasErrors('width')
        ->assertSet('totalPriceVat', null);
});

it('re-prefills dimensions when the product type changes', function () {
    Livewire::test(PriceEstimator::class)
        ->set('width', 5)
        ->assertHasErrors('width')
        ->set('type', 'blind')
        ->assertHasNoErrors()
        ->assertSet('width', 60.0)
        ->assertSet('height', 120.0)
        ->assertNotSet('totalPriceVat', null);
});

it('stores a server-built snapshot in the session and redirects to the inquiry form', function () {
    Livewire::test(PriceEstimator::class)
        ->set('type', 'curtain')
        ->set('width', 200)
        ->set('height', 150)
        ->set('count', 2)
        ->set('ruffle', 'rich')
        ->call('sendToInquiry')
        ->assertRedirect(route('web.inquiry.create'));

    $snapshot = session('inquiry.configuration');

    expect($snapshot)->not->toBeNull()
        ->and($snapshot['type'])->toBe('curtain')
        ->and($snapshot['count'])->toBe(2)
        ->and($snapshot['parameters']['ratio'])->toBe(2.5)
        ->and($snapshot['parameters']['ribbon_code'])->toBe(PublicPresets::DEFAULT_RIBBON)
        ->and($snapshot['total_price_vat'])->toBeGreaterThan(0)
        ->and($snapshot['summary'])->toContain('Záclona');
});

it('prices a stretch ceiling by area in the ceiling category', function () {
    // 3 × 4 m = 12 m² × 1 500 Kč = 18 000 Kč bez DPH, 21 780 Kč s DPH
    Livewire::test(PriceEstimator::class, ['category' => 'ceiling'])
        ->assertSet('type', 'stretch_ceiling')
        ->assertSet('width', 300.0)
        ->assertSet('height', 400.0)
        ->assertSet('totalPrice', 18000.0)
        ->assertSet('totalPriceVat', 21780.0)
        ->assertSee('Délka')
        ->assertDontSee('Nařasení');
});

it('offers ceiling variants as tiles and keeps the area price', function () {
    // Provedení do ceny zatím nevstupuje: 12 m² × 1 500 Kč = 18 000 Kč bez DPH
    Livewire::test(PriceEstimator::class, ['category' => 'ceiling'])
        ->assertSet('variant', 'monochrome')
        ->assertSee('Jednobarevný')
        ->assertSee('Jednobarevný se světelnou lištou')
        ->assertSee('Translucentní')
        ->set('variant', 'translucent')
        ->assertHasNoErrors()
        ->assertSet('totalPrice', 18000.0);
});

it('writes the chosen ceiling variant into the inquiry snapshot', function () {
    Livewire::test(PriceEstimator::class, ['category' => 'ceiling'])
        ->set('variant', 'translucent')
        ->call('sendToInquiry');

    $snapshot = session('inquiry.configuration');

    expect($snapshot['summary'])->toContain('Translucentní')
        ->and($snapshot['parameters']['surface'])->toBe('backlit');
});

it('rejects an unknown ceiling variant', function () {
    Livewire::test(PriceEstimator::class, ['category' => 'ceiling'])
        ->set('variant', 'neon')
        ->assertSet('totalPriceVat', null);
});

it('numbers the ceiling steps with specification before dimensions', function () {
    Livewire::test(PriceEstimator::class, ['category' => 'ceiling'])
        ->assertSeeInOrder(['1. Typ stropu', '2. Specifikace', '3. Rozměry', '4. Orientační cena']);

    Livewire::test(PriceEstimator::class)
        ->assertSeeInOrder(['1. Co potřebujete ušít?', '2. Rozměry', '3. Orientační cena'])
        ->assertDontSee('Specifikace');
});

it('asks monochrome ceilings for finish and colour', function (string $variant) {
    Livewire::test(PriceEstimator::class, ['category' => 'ceiling'])
        ->set('variant', $variant)
        ->assertSee('Druh')
        ->assertSee('Struktura')
        ->assertSee('Barva')
        ->assertSee('Černá')
        ->assertDontSee('Světlo po obvodu')
        ->set('finish', 'glossy')
        ->set('color', 'black')
        ->call('sendToInquiry');

    expect(session('inquiry.configuration.summary'))->toContain('lesk, černá')
        ->and(session('inquiry.configuration.parameters.surface'))->toBe('glossy');
})->with(['monochrome', 'light_strip']);

it('asks translucent ceilings only for the lighting', function () {
    Livewire::test(PriceEstimator::class, ['category' => 'ceiling'])
        ->set('variant', 'translucent')
        ->assertSee('Světlo po obvodu')
        ->assertSee('Světlo přes celý strop')
        ->assertDontSee('Struktura')
        ->set('lighting', 'full')
        ->call('sendToInquiry');

    expect(session('inquiry.configuration.summary'))->toContain('světlo přes celý strop')
        ->and(session('inquiry.configuration.parameters.surface'))->toBe('backlit');
});

it('rejects an unknown finish', function () {
    Livewire::test(PriceEstimator::class, ['category' => 'ceiling'])
        ->set('finish', 'neon')
        ->assertSet('totalPriceVat', null);
});

it('prices the light strip ceiling with strip, LED, power supply and receiver per piece', function () {
    foreach (['STROP_LISTA_SVETELNA' => 300, 'STROP_LED_COB_15W' => 250, 'STROP_ZDROJ_SLIM_400W' => 1800, 'STROP_PRIJIMAC' => 900] as $code => $value) {
        PricingVariable::query()->where('code', $code)->update(['value' => $value]);
    }

    // Referenční 3 × 4 m: 18 000 + obvod 14 bm × (300 + 250) + 1 800 + 900 = 28 400 Kč za kus
    Livewire::test(PriceEstimator::class, ['category' => 'ceiling'])
        ->set('variant', 'light_strip')
        ->set('count', 2)
        ->assertSet('totalPrice', 56800.0)
        ->assertSee('Lišta světelná (14 bm)')
        ->assertSee('LED COB 15 W (14 bm)')
        ->assertSee('Napájecí zdroj 400 W')
        ->assertSee('Přijímač')
        ->set('variant', 'monochrome')
        ->assertSet('totalPrice', 36000.0)
        ->assertDontSee('Lišta světelná');
});

it('offers only the products of its own category', function () {
    Livewire::test(PriceEstimator::class, ['category' => 'ceiling'])
        ->assertDontSee('Záclona')
        ->set('type', 'curtain')
        ->assertSet('totalPriceVat', null);
});

it('does not store a snapshot for invalid input', function () {
    Livewire::test(PriceEstimator::class)
        ->set('width', 5)
        ->call('sendToInquiry')
        ->assertHasErrors('width');

    expect(session('inquiry.configuration'))->toBeNull();
});
