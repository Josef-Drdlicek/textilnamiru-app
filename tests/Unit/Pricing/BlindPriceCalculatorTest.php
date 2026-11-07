<?php

use App\Domain\Pricing\Calculators\BlindPriceCalculator;
use App\Domain\Pricing\Parameters\BlindParameters;
use App\Domain\Pricing\PriceResult;
use Tests\Support\PriceLists;

function priceBlind(array $overrides = []): PriceResult
{
    $parameters = BlindParameters::fromArray($overrides + [
        'width' => 120,
        'height' => 80,
        'tunnels' => 4,
        'mechanism' => false,
        'cells' => null,
        'cell_height_top' => null,
        'cell_height' => null,
        'cell_height_bottom' => null,
    ]);

    return (new BlindPriceCalculator)->calculate($parameters, PriceLists::standard());
}

// 300 kompletace + 1,2 × 200 tunýlky + 0,8 × 33 příprava + 4 × 47 ušití = 754,40
it('prices a blind from finishing, width, height and tunnel count', function () {
    expect(priceBlind()->unitPrice)->toEqualWithDelta(754.40, 0.001);
});

it('charges every additional tunnel', function () {
    expect(priceBlind(['tunnels' => 5])->unitPrice - priceBlind()->unitPrice)->toEqualWithDelta(47.0, 0.001);
});

it('does not change the price for mechanism or cell layout', function () {
    $withProduction = priceBlind([
        'mechanism' => true, 'cells' => 4, 'cell_height_top' => 20, 'cell_height' => 30, 'cell_height_bottom' => 25,
    ]);

    expect($withProduction->unitPrice)->toBe(priceBlind()->unitPrice);
});

it('reports zero material consumption', function () {
    expect(priceBlind()->consumedMeters)->toBe(0.0);
});
