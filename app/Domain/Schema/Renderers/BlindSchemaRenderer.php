<?php

namespace App\Domain\Schema\Renderers;

use App\Domain\Pricing\Parameters\BlindParameters;
use App\Domain\Pricing\Parameters\ItemParameters;
use InvalidArgumentException;

class BlindSchemaRenderer extends BaseSchemaRenderer
{
    public function render(ItemParameters $parameters): string
    {
        if (! $parameters instanceof BlindParameters) {
            throw new InvalidArgumentException('Nákres rolety vyžaduje BlindParameters.');
        }

        $scale = $this->scale($parameters->width, $parameters->height);
        $w = $parameters->width * $scale;
        $h = $parameters->height * $scale;
        $x = (self::CANVAS_WIDTH - $w) / 2;
        $y = (self::CANVAS_HEIGHT - $h) / 2;

        $outline = self::OUTLINE_COLOR;
        $detail = self::DETAIL_COLOR;

        $svg = $this->open();

        // Horní lišta
        $svg .= sprintf(
            '<rect x="%.1f" y="%.1f" width="%.1f" height="8" fill="%s" stroke="%s" stroke-width="1"/>',
            $x - 4, $y - 8, $w + 8, self::DETAIL_COLOR, $outline,
        );

        // Plocha rolety
        $svg .= sprintf(
            '<rect x="%.1f" y="%.1f" width="%.1f" height="%.1f" fill="#fafaf9" stroke="%s" stroke-width="1.5"/>',
            $x, $y, $w, $h, $outline,
        );

        // Pole rolety: buď zadané výšky polí, nebo rovnoměrné rozdělení
        $cellBoundaries = $this->cellBoundaries($parameters, $h, $scale);

        foreach ($cellBoundaries as $offset) {
            $svg .= sprintf(
                '<line x1="%.1f" y1="%.1f" x2="%.1f" y2="%.1f" stroke="%s" stroke-width="1.5"/>',
                $x, $y + $offset, $x + $w, $y + $offset, $detail,
            );
        }

        $svg .= $this->horizontalDimension($x, $x + $w, $y + $h + 18, $this->formatCm($parameters->width));
        $svg .= $this->verticalDimension($y, $y + $h, $x - 18, $this->formatCm($parameters->height));

        return $svg.$this->close();
    }

    /**
     * Svislé pozice předělů polí [px od horního okraje].
     *
     * @return list<float>
     */
    private function cellBoundaries(BlindParameters $parameters, float $heightPx, float $scale): array
    {
        $cells = $parameters->cells ?? 0;

        if ($cells < 2) {
            return [];
        }

        // Zadané výšky polí: horní + (n−2)× střední; poslední pole dopočte zbytek.
        if ($parameters->cellHeightTop !== null && $parameters->cellHeight !== null) {
            $boundaries = [];
            $offset = $parameters->cellHeightTop * $scale;

            for ($i = 1; $i < $cells && $offset < $heightPx; $i++) {
                $boundaries[] = $offset;
                $offset += $parameters->cellHeight * $scale;
            }

            return $boundaries;
        }

        return array_map(
            fn (int $i) => $i * $heightPx / $cells,
            range(1, $cells - 1),
        );
    }
}
