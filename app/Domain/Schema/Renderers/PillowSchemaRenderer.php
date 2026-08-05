<?php

namespace App\Domain\Schema\Renderers;

use App\Domain\Pricing\Parameters\ItemParameters;
use App\Domain\Pricing\Parameters\PillowParameters;
use App\Enums\ZipperSide;
use InvalidArgumentException;

class PillowSchemaRenderer extends BaseSchemaRenderer
{
    public function render(ItemParameters $parameters): string
    {
        if (! $parameters instanceof PillowParameters) {
            throw new InvalidArgumentException('Nákres polštáře vyžaduje PillowParameters.');
        }

        $scale = $this->scale($parameters->width, $parameters->height);
        $w = $parameters->width * $scale;
        $h = $parameters->height * $scale;
        $x = (self::CANVAS_WIDTH - $w) / 2;
        $y = (self::CANVAS_HEIGHT - $h) / 2;

        $outline = self::OUTLINE_COLOR;
        $detail = self::DETAIL_COLOR;

        $svg = $this->open();

        $svg .= sprintf(
            '<rect x="%.1f" y="%.1f" width="%.1f" height="%.1f" rx="12" fill="#fafaf9" stroke="%s" stroke-width="1.5"/>',
            $x, $y, $w, $h, $outline,
        );

        // Zip na kratší/delší straně — přerušovaná čára
        $zipOnWidth = ($parameters->zipper === ZipperSide::Long) === ($parameters->width >= $parameters->height);

        $svg .= $zipOnWidth
            ? sprintf(
                '<line x1="%.1f" y1="%.1f" x2="%.1f" y2="%.1f" stroke="%s" stroke-width="2" stroke-dasharray="5 4"/>',
                $x + 8, $y + $h - 6, $x + $w - 8, $y + $h - 6, $detail,
            )
            : sprintf(
                '<line x1="%.1f" y1="%.1f" x2="%.1f" y2="%.1f" stroke="%s" stroke-width="2" stroke-dasharray="5 4"/>',
                $x + $w - 6, $y + 8, $x + $w - 6, $y + $h - 8, $detail,
            );

        $svg .= $this->horizontalDimension($x, $x + $w, $y + $h + 18, $this->formatCm($parameters->width));
        $svg .= $this->verticalDimension($y, $y + $h, $x - 18, $this->formatCm($parameters->height));

        return $svg.$this->close();
    }
}
