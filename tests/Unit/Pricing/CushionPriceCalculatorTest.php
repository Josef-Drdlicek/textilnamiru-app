<?php

use App\Domain\Pricing\Calculators\CushionPriceCalculator;
use App\Domain\Pricing\Parameters\CushionParameters;
use App\Domain\Pricing\PriceResult;
use App\Enums\ZipperSide;
use Tests\Support\PriceLists;

function priceCushion(array $overrides = []): PriceResult
{
    $parameters = CushionParameters::fromArray($overrides + [
        'width' => 40,
        'height' => 40,
        'depth' => 8,
        'foam' => false,
        'felt' => false,
        'ties' => false,
        'zipper' => ZipperSide::None->value,
    ]);

    return (new CushionPriceCalculator)->calculate($parameters, PriceLists::standard());
}

// ušití 105 + 0,16 m² × příprava 33
it('prices sewing and preparation by area', function () {
    expect(priceCushion()->unitPrice)->toEqualWithDelta(110.28, 0.001);
});

// 0,16 m² × 4 vrstvy (8 cm / 2 cm) × 225 = 144
it('adds one foam layer per started 2 cm of depth', function () {
    expect(priceCushion(['foam' => true])->unitPrice)->toEqualWithDelta(254.28, 0.001)
        ->and(priceCushion(['foam' => true, 'depth' => 7])->unitPrice)->toEqualWithDelta(254.28, 0.001)
        ->and(priceCushion(['foam' => true, 'depth' => 1])->unitPrice)->toEqualWithDelta(146.28, 0.001);
});

// Majitelův vzorec beze změny: 1 600 cm² / 0,25 = 6 400 jednotek / 100 × 38 = 2 432
it('prices felt per 100 units of 0.25 cm2 exactly as the owner defined it', function () {
    expect(priceCushion(['felt' => true])->unitPrice)->toEqualWithDelta(2542.28, 0.001)
        ->and(priceCushion(['width' => 120, 'height' => 80, 'felt' => true])->unitPrice)->toEqualWithDelta(14728.68, 0.001);
});

it('adds ties once per piece', function () {
    expect(priceCushion(['ties' => true])->unitPrice)->toEqualWithDelta(230.28, 0.001);
});

it('does not change the price for the zipper side', function () {
    expect(priceCushion(['zipper' => ZipperSide::Long->value])->unitPrice)->toBe(priceCushion()->unitPrice);
});
