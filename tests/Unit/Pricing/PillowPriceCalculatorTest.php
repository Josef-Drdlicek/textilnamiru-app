<?php

use App\Domain\Pricing\Calculators\PillowPriceCalculator;
use App\Domain\Pricing\Parameters\PillowParameters;
use App\Domain\Pricing\PriceResult;
use App\Enums\ZipperSide;
use Tests\Support\PriceLists;

function pillowParams(array $overrides = []): PillowParameters
{
    return PillowParameters::fromArray($overrides + [
        'width' => 45,
        'height' => 38,
        'stuffing' => false,
        'zipper' => ZipperSide::Short->value,
    ]);
}

function pricePillow(array $overrides = []): PriceResult
{
    return (new PillowPriceCalculator)->calculate(pillowParams($overrides), PriceLists::standard());
}

// size = ceil(4,5) + ceil(3,8) = 9 → 10 + 9×10 = 100
it('calculates the base pillow price from rounded-up 10cm steps', function () {
    expect(pricePillow()->unitPrice)->toEqualWithDelta(100.00, 0.001);
});

// size×10 = 90 > 80 → +130 + ceil(10/10)×33 = +163
it('adds stuffing above the minimum size', function () {
    expect(pricePillow(['stuffing' => true])->unitPrice)->toEqualWithDelta(263.00, 0.001);
});

// size×10 = 80, podmínka je ostré ">" → jen minimum 130
it('charges only the stuffing minimum exactly at the boundary', function () {
    $result = pricePillow(['width' => 40, 'height' => 40, 'stuffing' => true]);

    expect($result->unitPrice)->toEqualWithDelta(220.00, 0.001);
});

it('rounds decimal dimensions up to the next 10cm step', function () {
    // ceil(4,01) = 5 → stejná cena jako 50 cm
    expect(pricePillow(['width' => 40.1])->unitPrice)
        ->toBe(pricePillow(['width' => 50])->unitPrice);
});

it('does not change the price for the zipper side', function () {
    expect(pricePillow(['zipper' => ZipperSide::Long->value])->unitPrice)
        ->toBe(pricePillow()->unitPrice);
});

it('reports zero material consumption', function () {
    expect(pricePillow()->consumedMeters)->toBe(0.0);
});
