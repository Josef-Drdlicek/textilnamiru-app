<?php

use App\Domain\Pricing\Calculators\PillowPriceCalculator;
use App\Domain\Pricing\Parameters\PillowParameters;
use App\Domain\Pricing\PriceResult;
use App\Enums\ZipperSide;
use Tests\Support\PriceLists;

function pricePillow(array $overrides = []): PriceResult
{
    $parameters = PillowParameters::fromArray($overrides + [
        'width' => 45,
        'height' => 38,
        'stuffing' => false,
        'complex' => false,
        'zipper' => ZipperSide::None->value,
    ]);

    return (new PillowPriceCalculator)->calculate($parameters, PriceLists::standard());
}

// size = ceil(4,5) + ceil(3,8) = 9 → 10 + 9×10 + ušití 93 = 193
it('calculates the base pillow price from rounded-up 10cm steps plus sewing', function () {
    expect(pricePillow()->unitPrice)->toEqualWithDelta(193.00, 0.001);
});

it('charges the complex-shape sewing rate instead of the regular one', function () {
    expect(pricePillow(['complex' => true])->unitPrice)->toEqualWithDelta(275.00, 0.001);
});

// size×10 = 90 > 80 → +130 + ceil(10/10)×33 = +163
it('adds stuffing above the minimum size', function () {
    expect(pricePillow(['stuffing' => true])->unitPrice)->toEqualWithDelta(356.00, 0.001);
});

// size×10 = 80, podmínka je ostré ">" → jen minimum 130
it('charges only the stuffing minimum exactly at the boundary', function () {
    $result = pricePillow(['width' => 40, 'height' => 40, 'stuffing' => true]);

    expect($result->unitPrice)->toEqualWithDelta(313.00, 0.001);
});

it('rounds decimal dimensions up to the next 10cm step', function () {
    expect(pricePillow(['width' => 40.1])->unitPrice)
        ->toBe(pricePillow(['width' => 50])->unitPrice);
});

it('adds the zipper surcharge regardless of the zipper side', function () {
    expect(pricePillow(['zipper' => ZipperSide::Short->value])->unitPrice)->toEqualWithDelta(240.00, 0.001)
        ->and(pricePillow(['zipper' => ZipperSide::Long->value])->unitPrice)->toEqualWithDelta(240.00, 0.001);
});

it('reports zero material consumption', function () {
    expect(pricePillow()->consumedMeters)->toBe(0.0);
});
