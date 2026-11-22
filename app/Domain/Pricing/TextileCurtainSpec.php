<?php

namespace App\Domain\Pricing;

use App\Enums\CurtainHem;

/**
 * Zadání ušití záclony z látky v ceníku textilu (produktová stránka).
 */
final readonly class TextileCurtainSpec
{
    public function __construct(
        public float $width,        // hotová šířka na okně [cm]
        public float $height,       // hotová výška [cm]
        public CurtainHem $hem,
        public bool $split,         // opona: rozdělit na dva díly
        public string $ribbonCode,  // řasící páska (tabulka ribbons)
        public float $ratio,        // poměr řasení
    ) {}

    public function pieces(): int
    {
        return $this->split ? 2 : 1;
    }
}
