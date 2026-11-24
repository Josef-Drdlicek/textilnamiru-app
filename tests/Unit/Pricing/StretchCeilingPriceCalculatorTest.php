<?php

use App\Domain\Pricing\Calculators\StretchCeilingPriceCalculator;
use App\Domain\Pricing\Parameters\StretchCeilingParameters;
use App\Domain\Pricing\PriceResult;
use App\Enums\CeilingSurface;
use Tests\Support\PriceLists;

function priceCeiling(array $overrides = []): PriceResult
{
    $parameters = StretchCeilingParameters::fromArray($overrides + [
        'width' => 300,
        'length' => 400,
        'surface' => CeilingSurface::Matte->value,
        'led_length' => 0,
        'distance' => 0,
    ]);

    return (new StretchCeilingPriceCalculator)->calculate($parameters, PriceLists::standard());
}

// 3 × 4 m = 12 m² × 1 500
it('prices the ceiling by area', function () {
    expect(priceCeiling()->unitPrice)->toEqualWithDelta(18000.0, 0.001)
        ->and(priceCeiling()->lines)->toHaveCount(1);
});

// + 14 m × 400 + 30 km × 12
it('adds LED strip per meter and transport per kilometer', function () {
    expect(priceCeiling(['led_length' => 14, 'distance' => 30])->unitPrice)->toEqualWithDelta(23960.0, 0.001);
});

it('does not change the price for the surface', function () {
    expect(priceCeiling(['surface' => CeilingSurface::Glossy->value])->unitPrice)->toBe(priceCeiling()->unitPrice);
});

// Obvod 2 × (3 + 4) = 14 bm: lišta 14 × 300 + LED 14 × 250 + zdroj 1 800 + přijímač 900 = 10 400
it('adds light strip, LED along the perimeter and one power supply with receiver', function () {
    $result = priceCeiling(['light_strip' => true]);

    expect($result->unitPrice)->toEqualWithDelta(18000.0 + 10400.0, 0.001)
        ->and(array_column($result->linesToArray(), 'label'))->toBe([
            'Vypínaný strop',
            'Lišta světelná (14 bm)',
            'LED COB 15 W (14 bm)',
            'Napájecí zdroj 400 W',
            'Přijímač',
        ]);
});

it('keeps old ceiling items without the light strip flag', function () {
    expect(priceCeiling()->lines)->toHaveCount(1);
});
