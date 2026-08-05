<?php

use App\Domain\Pricing\Calculators\CurtainPriceCalculator;
use App\Domain\Pricing\Parameters\CurtainParameters;
use App\Domain\Pricing\PriceResult;
use App\Enums\RightLeft;
use Tests\Support\PriceLists;

function curtainParams(array $overrides = []): CurtainParameters
{
    return CurtainParameters::fromArray($overrides + [
        'width' => 200,
        'height' => 250,
        'material_width' => 300,
        'ribbon_code' => 'tuzky85',
        'ratio' => 2,
        'kanyr' => false,
        'kanyr_height' => null,
        'right_left' => RightLeft::Right->value,
        'corners' => false,
        'bottom' => false,
        'narasit' => false,
        'snails' => false,
    ]);
}

function priceCurtain(array $overrides = []): PriceResult
{
    return (new CurtainPriceCalculator)->calculate(curtainParams($overrides), PriceLists::standard());
}

// Základ: consumed = ((200×2)+20)/100 = 4,2 m
// 4,2×33 + 4,2×63 + 4,2×47 + 2×2,5×47 + 10 + 4,2×2,5×20 = 1055,60
it('calculates the base curtain price', function () {
    $result = priceCurtain();

    expect($result->unitPrice)->toEqualWithDelta(1055.60, 0.001)
        ->and($result->consumedMeters)->toEqualWithDelta(4.2, 0.0001);
});

it('adds corner weights (2 × OLUVKO_13G = 44)', function () {
    expect(priceCurtain(['corners' => true])->unitPrice)->toEqualWithDelta(1099.60, 0.001);
});

it('adds continuous bottom lead (4,2×50 + 4,2×42 = 386,40)', function () {
    expect(priceCurtain(['bottom' => true])->unitPrice)->toEqualWithDelta(1442.00, 0.001);
});

it('adds gathering (4,2×22 = 92,40)', function () {
    expect(priceCurtain(['narasit' => true])->unitPrice)->toEqualWithDelta(1148.00, 0.001);
});

it('adds snail hooks (ceil(4,2/0,08)=53 × 3 = 159)', function () {
    expect(priceCurtain(['snails' => true])->unitPrice)->toEqualWithDelta(1214.60, 0.001);
});

it('combines all surcharges', function () {
    $result = priceCurtain(['corners' => true, 'bottom' => true, 'narasit' => true, 'snails' => true]);

    expect($result->unitPrice)->toEqualWithDelta(1737.40, 0.001);
});

// Vlastní stuha (0 Kč/m), poměr 1,7: consumed = 1,9 m
// 1,9×33 + 0 + 1,9×47 + 2×1×47 + 10 + 1,9×1×20 = 294,00
it('supports a custom ribbon with a custom ratio', function () {
    $result = priceCurtain([
        'width' => 100, 'height' => 100, 'ribbon_code' => 'custom', 'ratio' => 1.7,
    ]);

    expect($result->unitPrice)->toEqualWithDelta(294.00, 0.001)
        ->and($result->consumedMeters)->toEqualWithDelta(1.9, 0.0001);
});

// Desetinné rozměry se počítají přesně (oprava parseInt bugu, zadání §11.8):
// w=137,5, h=210, ratio=1,85, siroky70 → consumed 2,74375 → 682,07
it('keeps decimal dimensions in the calculation', function () {
    $result = priceCurtain([
        'width' => 137.5, 'height' => 210, 'ribbon_code' => 'siroky70', 'ratio' => 1.85,
    ]);

    expect($result->unitPrice)->toEqualWithDelta(682.07, 0.001);
});

it('does not change the price for kanyr or side selection', function () {
    $base = priceCurtain();
    $withProduction = priceCurtain([
        'kanyr' => true, 'kanyr_height' => 30, 'right_left' => RightLeft::Mirror->value,
    ]);

    expect($withProduction->unitPrice)->toBe($base->unitPrice);
});

it('exposes a VAT price consistent with the original app rounding', function () {
    expect(priceCurtain()->unitPriceWithVat(21))->toEqualWithDelta(1277.28, 0.001);
});

it('provides a price breakdown that sums to the unit price', function () {
    $result = priceCurtain(['corners' => true, 'snails' => true]);

    $sum = array_sum(array_column($result->linesToArray(), 'amount'));

    expect($sum)->toEqualWithDelta($result->unitPrice, 0.05);
});
