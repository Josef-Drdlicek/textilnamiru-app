<?php

namespace App\Domain\Pricing;

use App\Domain\Pricing\Calculators\CurtainPriceCalculator;
use App\Domain\Pricing\Calculators\DrapePriceCalculator;
use App\Domain\Pricing\Parameters\CurtainParameters;
use App\Domain\Pricing\Parameters\DrapeParameters;
use App\Domain\Pricing\Parameters\ItemParameters;
use App\Enums\CurtainHem;
use App\Enums\RightLeft;
use App\Enums\TextileCategory;
use App\Models\TextileProduct;

/**
 * Cena výrobku ušitého z látky v ceníku textilu: látka (spotřeba × cena za metr)
 * + ušití podle kalkulátoru záclony nebo závěsu a ceníku šití.
 *
 * Opona (dva díly) = každý díl má polovinu šířky a šije se zvlášť.
 *
 * Spotřeba látky:
 * - záclona a závěs z dost široké látky (výška + 10 cm se vejde do role) se šije
 *   „po výšce role“: spotřeba = nabraná šířka podle řasení + 20 cm na kraje;
 * - závěs z úzké látky (např. 140 cm) se sešívá ze svislých pruhů:
 *   pruhů = nabraná šířka / šíře látky (nahoru), každý pruh = výška + 30 cm na lemy.
 */
final readonly class TextileProductPricer
{
    /** Přídavek na horní a spodní lem u svislého pruhu [cm]. */
    public const int PANEL_HEM_ALLOWANCE = 30;

    /** Rezerva, aby se výška vešla do šíře role bez sešívání [cm] (stejná jako v DrapePriceCalculator). */
    public const int ROLL_FIT_ALLOWANCE = 10;

    public function __construct(
        private PriceList $prices,
        private CurtainPriceCalculator $curtain,
        private DrapePriceCalculator $drape,
    ) {}

    public function price(TextileProduct $product, TextileCurtainSpec $spec): PriceResult
    {
        $pieces = $spec->pieces();
        $parameters = $this->pieceParameters($product, $spec);
        $sewing = $parameters instanceof DrapeParameters
            ? $this->drape->calculate($parameters, $this->prices)
            : $this->curtain->calculate($parameters, $this->prices);
        $suffix = $pieces > 1 ? " ({$pieces} díly)" : '';

        [$fabricMeters, $fabricLabel] = $this->fabric($product, $spec, $sewing->consumedMeters);

        $lines = [new PriceLine($fabricLabel, $fabricMeters * $product->price_per_meter)];

        foreach ($sewing->lines as $line) {
            $lines[] = new PriceLine($line->label.$suffix, $line->amount * $pieces);
        }

        // Záložku u záclony kalkulátor nezná; závěs ji má v ceně vždy.
        if ($product->category !== TextileCategory::Drape && $spec->hem === CurtainHem::Fold) {
            $lines[] = new PriceLine('Ušití záložky'.$suffix, $sewing->consumedMeters * $pieces * $this->prices->var('USITI_ZALOZKA'));
        }

        return PriceResult::fromLines($lines, consumedMeters: $fabricMeters);
    }

    /**
     * Parametry jednoho dílu pro kalkulátor (a pro uložení do poptávky).
     */
    public function pieceParameters(TextileProduct $product, TextileCurtainSpec $spec): ItemParameters
    {
        $width = $spec->width / $spec->pieces();

        if ($product->category === TextileCategory::Drape) {
            return new DrapeParameters(
                width: $width,
                height: $spec->height,
                materialWidth: (float) $product->roll_height,
                ribbonCode: $spec->ribbonCode,
                ratio: $spec->ratio,
                raport: null,
                folding: null,
                kanyr: false,
                kanyrHeight: null,
                rightLeft: RightLeft::Right,
                corners: $spec->hem === CurtainHem::Fold,
                bottom: $spec->hem === CurtainHem::Lead,
                narasit: false,
                snails: false,
            );
        }

        return new CurtainParameters(
            width: $width,
            height: $spec->height,
            materialWidth: (float) $product->roll_height,
            ribbonCode: $spec->ribbonCode,
            ratio: $spec->ratio,
            kanyr: false,
            kanyrHeight: null,
            rightLeft: RightLeft::Right,
            corners: $spec->hem === CurtainHem::Fold,
            bottom: $spec->hem === CurtainHem::Lead,
            narasit: false,
            snails: false,
        );
    }

    /**
     * Šije se závěs ze svislých pruhů (látka je užší než výška)?
     */
    public function usesPanels(TextileProduct $product, float $height): bool
    {
        return $product->category === TextileCategory::Drape
            && $product->roll_height < $height + self::ROLL_FIT_ALLOWANCE;
    }

    public function vatRate(): float
    {
        return $this->prices->vatRate();
    }

    public function withVat(float $price): float
    {
        return $this->prices->withVat($price);
    }

    /**
     * @return array{0: float, 1: string} spotřeba látky [m] a popisek řádku
     */
    private function fabric(TextileProduct $product, TextileCurtainSpec $spec, float $consumedPerPiece): array
    {
        if (! $this->usesPanels($product, $spec->height)) {
            $meters = $consumedPerPiece * $spec->pieces();

            return [$meters, sprintf('Látka %s (%s m)', $product->name, $this->formatMeters($meters))];
        }

        $panelsPerPiece = (int) ceil(($consumedPerPiece * 100) / $product->roll_height);
        $panels = $panelsPerPiece * $spec->pieces();
        $panelMeters = ($spec->height + self::PANEL_HEM_ALLOWANCE) / 100;
        $meters = $panels * $panelMeters;

        return [$meters, sprintf(
            'Látka %s (%d %s × %s m = %s m)',
            $product->name,
            $panels,
            $panels === 1 ? 'pruh' : ($panels < 5 ? 'pruhy' : 'pruhů'),
            $this->formatMeters($panelMeters),
            $this->formatMeters($meters),
        )];
    }

    private function formatMeters(float $meters): string
    {
        return rtrim(rtrim(number_format($meters, 2, ',', ' '), '0'), ',');
    }
}
