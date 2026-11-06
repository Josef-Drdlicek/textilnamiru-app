<?php

use App\Domain\Pricing\ItemPricer;
use App\Domain\Pricing\PublicPresets;
use App\Enums\ItemType;
use App\Livewire\Web\PriceEstimator;
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

it('selecting a placeholder tile only changes the visual selection', function () {
    $component = Livewire::test(PriceEstimator::class, ['asPage' => true])
        ->assertSet('type', 'curtain')
        ->assertNotSet('totalPriceVat', null);

    $price = $component->get('totalPriceVat');

    $component->call('vybratNahradniDlazdici', 0)
        ->assertSet('vybranaDlazdice', 'extra-0')
        ->assertSet('type', 'curtain')
        ->assertSet('totalPriceVat', $price);
});

it('hides dimension fields for vypinany podhled but keeps them for other tiles', function () {
    $component = Livewire::test(PriceEstimator::class, ['asPage' => true])
        ->set('width', 180)
        ->assertSee('2. Rozměry')
        ->assertSee('Hotová výška');

    $component->call('vybratNahradniDlazdici', 0)
        ->assertSee('2. Rozměry')
        ->assertSee('Šířka')
        ->assertDontSee('Hotová výška')
        ->assertDontSee('Nařasení')
        ->assertSet('width', 180.0);

    $price = $component->get('totalPriceVat');

    $component->set('extra0Sirka', '999')
        ->set('extra0Vyska', '888')
        ->set('extra0Pocet', '7')
        ->set('extra0Vyplne', true)
        ->assertSet('totalPriceVat', $price)
        ->assertSet('width', 180.0);

    $component->set('vybranaDlazdice', 'curtain')
        ->assertSee('Hotová výška')
        ->assertSee('Počet kusů')
        ->assertSet('width', 180.0);
});

it('does not store a snapshot for invalid input', function () {
    Livewire::test(PriceEstimator::class)
        ->set('width', 5)
        ->call('sendToInquiry')
        ->assertHasErrors('width');

    expect(session('inquiry.configuration'))->toBeNull();
});
