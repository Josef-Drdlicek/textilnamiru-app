<?php

use App\Domain\Pricing\Calculators\DrapePriceCalculator;
use App\Domain\Pricing\Parameters\DrapeParameters;
use App\Domain\Pricing\PriceResult;
use App\Enums\RightLeft;
use Tests\Support\PriceLists;

function drapeParams(array $overrides = []): DrapeParameters
{
    return DrapeParameters::fromArray($overrides + [
        'width' => 200,
        'height' => 250,
        'material_width' => 140,
        'ribbon_code' => 'siroky70',
        'ratio' => 2,
        'raport' => null,
        'folding' => null,
        'kanyr' => false,
        'kanyr_height' => null,
        'right_left' => RightLeft::Right->value,
        'corners' => false,
        'bottom' => false,
        'narasit' => false,
        'snails' => false,
    ]);
}

function priceDrape(array $overrides = []): PriceResult
{
    return (new DrapePriceCalculator)->calculate(drapeParams($overrides), PriceLists::standard());
}

// consumed = 4,2; základ jako záclona + záložka 4,2×47 = 1202,60;
// materiál 140 < 260 → joins = floor(420/165) = 2 → +2×2,5×47 = 235 → 1437,60
it('calculates the drape price including seam joins for narrow material', function () {
    $result = priceDrape();

    expect($result->unitPrice)->toEqualWithDelta(1437.60, 0.001)
        ->and($result->consumedMeters)->toEqualWithDelta(4.2, 0.0001);
});

it('skips seam joins exactly at the boundary (material_width == height + 10)', function () {
    expect(priceDrape(['material_width' => 260])->unitPrice)->toEqualWithDelta(1202.60, 0.001);
});

it('adds a single join just below the boundary', function () {
    // joins = floor(420 / 284,9) = 1 → +1×2,5×47 = 117,50
    expect(priceDrape(['material_width' => 259.9])->unitPrice)->toEqualWithDelta(1320.10, 0.001);
});

it('uses the heavier 50g corner weights (2 × 67 = 134)', function () {
    expect(priceDrape(['corners' => true])->unitPrice)->toEqualWithDelta(1571.60, 0.001);
});

it('does not change the price for raport, folding or kanyr', function () {
    $base = priceDrape();
    $withProduction = priceDrape(['raport' => 64, 'folding' => 15, 'kanyr' => true, 'kanyr_height' => 25]);

    expect($withProduction->unitPrice)->toBe($base->unitPrice);
});
