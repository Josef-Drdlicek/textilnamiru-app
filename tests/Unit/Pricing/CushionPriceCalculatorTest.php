<?php

use App\Domain\Pricing\Calculators\CushionPriceCalculator;
use App\Domain\Pricing\Calculators\PillowPriceCalculator;
use App\Domain\Pricing\Parameters\CushionParameters;
use App\Domain\Pricing\Parameters\PillowParameters;
use App\Domain\Pricing\PriceResult;
use App\Enums\ZipperSide;
use Tests\Support\PriceLists;

function priceCushion(array $overrides = []): PriceResult
{
    $parameters = CushionParameters::fromArray($overrides + [
        'width' => 45,
        'height' => 38,
        'depth' => 60,
        'stuffing' => false,
        'zipper' => ZipperSide::Short->value,
    ]);

    return (new CushionPriceCalculator(new PillowPriceCalculator))->calculate($parameters, PriceLists::standard());
}

/**
 * GOLDEN TEST ZNÁMÉ PODIVNOSTI (zadání §7.4 / §13.1): sedák se v původní
 * aplikaci cení identicky jako polštář — hloubka i sedákové ceníkové položky
 * se ignorují. Tento test to vědomě zamyká; až dílna dodá správný vzorec,
 * test MÁ spadnout a bude nahrazen.
 */
it('prices a cushion exactly like a pillow of the same width and height', function (float $width, float $height, bool $stuffing) {
    $cushion = priceCushion(['width' => $width, 'height' => $height, 'stuffing' => $stuffing, 'depth' => 75]);

    $pillow = (new PillowPriceCalculator)->calculate(
        PillowParameters::fromArray([
            'width' => $width, 'height' => $height, 'stuffing' => $stuffing, 'zipper' => ZipperSide::Short->value,
        ]),
        PriceLists::standard(),
    );

    expect($cushion->unitPrice)->toBe($pillow->unitPrice);
})->with([
    [45.0, 38.0, false],
    [45.0, 38.0, true],
    [100.0, 100.0, true],
    [10.0, 10.0, false],
]);

it('ignores depth entirely', function () {
    expect(priceCushion(['depth' => 10])->unitPrice)
        ->toBe(priceCushion(['depth' => 200])->unitPrice);
});
