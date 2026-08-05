<?php

use App\Domain\Pricing\Calculators\BlindPriceCalculator;
use App\Domain\Pricing\Exceptions\NoBlindPriceBand;
use App\Domain\Pricing\Parameters\BlindParameters;
use App\Domain\Pricing\PriceResult;
use Tests\Support\PriceLists;

function priceBlind(array $overrides = []): PriceResult
{
    $parameters = BlindParameters::fromArray($overrides + [
        'width' => 80,
        'height' => 100,
        'mechanism' => false,
        'tunnels' => false,
        'finishing' => false,
        'cells' => null,
        'cell_height_top' => null,
        'cell_height' => null,
        'cell_height_bottom' => null,
    ]);

    return (new BlindPriceCalculator)->calculate($parameters, PriceLists::standard());
}

it('looks up the price band by width and height', function (float $width, float $height, float $expected) {
    expect(priceBlind(['width' => $width, 'height' => $height])->unitPrice)->toBe($expected);
})->with([
    'smallest band' => [80, 100, 552.0],
    'width just over a band boundary' => [81, 100, 672.0],
    'height just over a band boundary' => [80, 101, 672.0],
    'middle of the table' => [250, 101, 1068.0],
    'largest band' => [300, 300, 1658.0],
    'small blind falls into the smallest band' => [10, 10, 552.0],
]);

it('adds surcharges for tunnels and finishing', function () {
    expect(priceBlind(['tunnels' => true])->unitPrice)->toBe(752.0)
        ->and(priceBlind(['finishing' => true])->unitPrice)->toBe(852.0)
        ->and(priceBlind(['tunnels' => true, 'finishing' => true])->unitPrice)->toBe(1052.0);
});

it('does not change the price for mechanism or cell layout', function () {
    $withProduction = priceBlind([
        'mechanism' => true, 'cells' => 4, 'cell_height_top' => 20, 'cell_height' => 30, 'cell_height_bottom' => 25,
    ]);

    expect($withProduction->unitPrice)->toBe(priceBlind()->unitPrice);
});

it('throws when the size exceeds the price table', function () {
    priceBlind(['width' => 301]);
})->throws(NoBlindPriceBand::class);

it('reports zero material consumption', function () {
    expect(priceBlind()->consumedMeters)->toBe(0.0);
});
