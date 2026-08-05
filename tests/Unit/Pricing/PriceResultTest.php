<?php

use App\Domain\Pricing\PriceLine;
use App\Domain\Pricing\PriceResult;

it('rounds the unit price to two decimals like the original app', function () {
    $result = PriceResult::fromLines([
        new PriceLine('A', 100.005),
        new PriceLine('B', 0.001),
    ]);

    expect($result->unitPrice)->toEqualWithDelta(100.01, 0.0001);
});

it('computes VAT via round(price × (100 + VAT)) / 100', function () {
    $result = PriceResult::fromLines([new PriceLine('A', 1055.60)]);

    expect($result->unitPriceWithVat(21))->toEqualWithDelta(1277.28, 0.0001);
});

it('multiplies the rounded unit price by the count', function () {
    $result = PriceResult::fromLines([new PriceLine('A', 1055.60)]);

    expect($result->totalPrice(3))->toEqualWithDelta(3166.80, 0.0001)
        ->and($result->totalPriceWithVat(21, 3))->toEqualWithDelta(3831.84, 0.0001);
});
