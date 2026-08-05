<?php

namespace App\Domain\Schema\Renderers;

use App\Domain\Pricing\Parameters\CushionParameters;
use App\Domain\Pricing\Parameters\ItemParameters;
use InvalidArgumentException;

class CushionSchemaRenderer extends BaseSchemaRenderer
{
    public function render(ItemParameters $parameters): string
    {
        if (! $parameters instanceof CushionParameters) {
            throw new InvalidArgumentException('Nákres sedáku vyžaduje CushionParameters.');
        }

        // Izometrický náznak: půdorys šířka × hloubka, výška jako třetí rozměr.
        $depthOffset = 0.35; // zkosení hloubky
        $projectedWidth = $parameters->width + $parameters->depth * $depthOffset;
        $projectedHeight = $parameters->height + $parameters->depth * $depthOffset;

        $scale = $this->scale($projectedWidth, $projectedHeight);
        $w = $parameters->width * $scale;
        $h = $parameters->height * $scale;
        $d = $parameters->depth * $scale * $depthOffset;

        $x = (self::CANVAS_WIDTH - $w - $d) / 2;
        $y = (self::CANVAS_HEIGHT - $h - $d) / 2 + $d;

        $outline = self::OUTLINE_COLOR;
        $detail = self::DETAIL_COLOR;

        $svg = $this->open();

        // Horní plocha
        $svg .= sprintf(
            '<path d="M %.1f %.1f l %.1f %.1f h %.1f l %.1f %.1f z" fill="#f5f5f4" stroke="%s" stroke-width="1.5"/>',
            $x, $y, $d, -$d, $w, -$d, $d, $outline,
        );

        // Čelní plocha
        $svg .= sprintf(
            '<rect x="%.1f" y="%.1f" width="%.1f" height="%.1f" fill="#fafaf9" stroke="%s" stroke-width="1.5"/>',
            $x, $y, $w, $h, $outline,
        );

        // Boční plocha
        $svg .= sprintf(
            '<path d="M %.1f %.1f l %.1f %.1f v %.1f l %.1f %.1f z" fill="#e7e5e4" stroke="%s" stroke-width="1.5"/>',
            $x + $w, $y, $d, -$d, $h, -$d, $d, $outline,
        );

        $svg .= sprintf(
            '<line x1="%.1f" y1="%.1f" x2="%.1f" y2="%.1f" stroke="%s" stroke-width="1" stroke-dasharray="4 4"/>',
            $x, $y + $h / 2, $x + $w, $y + $h / 2, $detail,
        );

        $svg .= $this->horizontalDimension($x, $x + $w, $y + $h + 18, $this->formatCm($parameters->width));
        $svg .= $this->verticalDimension($y, $y + $h, $x - 18, $this->formatCm($parameters->height));

        // Kóta hloubky podél zkosené hrany
        $svg .= sprintf(
            '<g stroke="%1$s" fill="%1$s"><line x1="%2$.1f" y1="%3$.1f" x2="%4$.1f" y2="%5$.1f" stroke-width="1"/>'
            .'<text x="%6$.1f" y="%7$.1f" stroke="none" text-anchor="start">%8$s</text></g>',
            self::DIMENSION_COLOR,
            $x + $w + 6, $y + $h + 4,
            $x + $w + $d + 6, $y + $h - $d + 4,
            $x + $w + $d / 2 + 12, $y + $h - $d / 2 + 4,
            $this->formatCm($parameters->depth),
        );

        return $svg.$this->close();
    }
}
