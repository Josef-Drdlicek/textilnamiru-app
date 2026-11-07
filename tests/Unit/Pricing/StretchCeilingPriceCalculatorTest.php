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
