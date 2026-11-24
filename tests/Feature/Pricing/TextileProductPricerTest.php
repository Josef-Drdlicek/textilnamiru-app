<?php

use App\Domain\Pricing\Calculators\CurtainPriceCalculator;
use App\Domain\Pricing\Calculators\DrapePriceCalculator;
use App\Domain\Pricing\PriceResult;
use App\Domain\Pricing\TextileCurtainSpec;
use App\Domain\Pricing\TextileProductPricer;
use App\Enums\CurtainHem;
use App\Enums\TextileCategory;
use App\Models\TextileProduct;
use Tests\Support\PriceLists;

// Ve Feature kvůli Eloquent modelu (potřebuje zavedenou aplikaci); ceník je ale čistě v paměti.

function textilePricer(): TextileProductPricer
{
    return new TextileProductPricer(PriceLists::standard(), new CurtainPriceCalculator, new DrapePriceCalculator);
}

function priceTextileCurtain(CurtainHem $hem = CurtainHem::Lead, bool $split = false): PriceResult
{
    $product = new TextileProduct(['name' => 'Test', 'roll_height' => 300, 'price_per_meter' => 300]);

    return textilePricer()
        ->price($product, new TextileCurtainSpec(width: 200, height: 250, hem: $hem, split: $split, ribbonCode: 'siroky70', ratio: 2));
}

// Spotřeba (200 × 2 + 20) / 100 = 4,2 m. Látka 4,2 × 300 = 1 260.
// Šití: příprava 138,6 + stuha 214,2 + našití 197,4 + lemování 2 × 2,5 × 47 = 235 + kordovací 10
// + žehlení 4,2 × 2,5 × 20 = 210 + olovo 4,2 × 50 = 210 + všití 4,2 × 42 = 176,4 → 1 391,6.
it('prices fabric by consumed meters plus sewing from the curtain calculator', function () {
    $result = priceTextileCurtain();

    expect($result->consumedMeters)->toEqualWithDelta(4.2, 0.0001)
        ->and($result->unitPrice)->toEqualWithDelta(2651.6, 0.001)
        ->and($result->lines[0]->label)->toBe('Látka Test (4,2 m)');
});

// Opona: dva díly po 100 cm, každý (100 × 2 + 20) / 100 = 2,2 m → látka 4,4 × 300 = 1 320,
// šití dílu 845,6 × 2 = 1 691,2.
it('sews a split curtain as two pieces of half width', function () {
    $result = priceTextileCurtain(split: true);

    expect($result->consumedMeters)->toEqualWithDelta(4.4, 0.0001)
        ->and($result->unitPrice)->toEqualWithDelta(3011.2, 0.001)
        ->and($result->lines[1]->label)->toBe('Příprava (2 díly)');
});

// Záložka: místo průběžného olova (386,4) rohová olůvka 44 a ušití záložky 4,2 × 47 = 197,4.
it('replaces the lead with corner weights and a folded hem', function () {
    expect(priceTextileCurtain(CurtainHem::Fold)->unitPrice)->toEqualWithDelta(2506.6, 0.001);
});

function priceTextileDrape(int $rollWidth): PriceResult
{
    $product = new TextileProduct(['name' => 'Test', 'category' => TextileCategory::Drape, 'roll_height' => $rollWidth, 'price_per_meter' => 300]);

    return textilePricer()->price($product, new TextileCurtainSpec(width: 200, height: 250, hem: CurtainHem::Lead, split: false, ribbonCode: 'siroky70', ratio: 2));
}

// Široká dekorační látka (300 cm) se šije po šíři role: látka 4,2 m × 300 = 1 260.
// Šití závěsu: příprava 138,6 + stuha 214,2 + našití 197,4 + lemování 235 + kordovací 10
// + žehlení 210 + spodní záložka 4,2 × 47 = 197,4 → 1 202,6.
it('prices a drape from wide fabric along the roll', function () {
    $result = priceTextileDrape(300);

    expect($result->consumedMeters)->toEqualWithDelta(4.2, 0.0001)
        ->and($result->unitPrice)->toEqualWithDelta(2462.6, 0.001);
});

// Úzká látka (140 cm): nabraná šířka 420 cm / 140 = 3 pruhy × (250 + 30) cm = 8,4 m → 2 520,
// navíc sešití dílů z kalkulátoru závěsu: floor(420 / 165) = 2 × 2,5 × 47 = 235.
it('sews a drape from narrow fabric out of vertical panels', function () {
    $result = priceTextileDrape(140);

    expect($result->consumedMeters)->toEqualWithDelta(8.4, 0.0001)
        ->and($result->unitPrice)->toEqualWithDelta(2520 + 1202.6 + 235, 0.001)
        ->and($result->lines[0]->label)->toBe('Látka Test (3 pruhy × 2,8 m = 8,4 m)');
});
