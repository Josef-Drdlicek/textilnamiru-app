<?php

namespace App\Domain\Schema\Renderers;

use App\Domain\Pricing\Parameters\ItemParameters;
use App\Domain\Pricing\Parameters\StretchCeilingParameters;
use InvalidArgumentException;

/**
 * Půdorys stropu s kótami; LED pásek naznačený přerušovaně po obvodu.
 */
class StretchCeilingSchemaRenderer extends BaseSchemaRenderer
{
    public function render(ItemParameters $parameters): string
    {
        if (! $parameters instanceof StretchCeilingParameters) {
            throw new InvalidArgumentException('Nákres stropu vyžaduje StretchCeilingParameters.');
        }

        $scale = $this->scale($parameters->width, $parameters->length);
        $w = $parameters->width * $scale;
        $h = $parameters->length * $scale;
        $x = (self::CANVAS_WIDTH - $w) / 2;
        $y = (self::CANVAS_HEIGHT - $h) / 2;

        $svg = $this->open();

        $svg .= sprintf(
            '<rect x="%.1f" y="%.1f" width="%.1f" height="%.1f" fill="#fafaf9" stroke="%s" stroke-width="1.5"/>',
            $x, $y, $w, $h, self::OUTLINE_COLOR,
        );

        if ($parameters->ledLength > 0) {
            $svg .= sprintf(
                '<rect x="%.1f" y="%.1f" width="%.1f" height="%.1f" fill="none" stroke="%s" stroke-width="2" stroke-dasharray="5 4"/>',
                $x + 6, $y + 6, max($w - 12, 0), max($h - 12, 0), self::DETAIL_COLOR,
            );
        }

        $svg .= $this->horizontalDimension($x, $x + $w, $y + $h + 18, $this->formatCm($parameters->width));
        $svg .= $this->verticalDimension($y, $y + $h, $x - 18, $this->formatCm($parameters->length));

        return $svg.$this->close();
    }
}
