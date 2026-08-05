<?php

namespace App\Domain\Schema\Renderers;

use App\Domain\Pricing\Parameters\CurtainParameters;
use App\Domain\Pricing\Parameters\ItemParameters;
use InvalidArgumentException;

class CurtainSchemaRenderer extends BaseSchemaRenderer
{
    public function render(ItemParameters $parameters): string
    {
        if (! $parameters instanceof CurtainParameters) {
            throw new InvalidArgumentException('Nákres záclony vyžaduje CurtainParameters.');
        }

        return $this->renderCurtainLike(
            width: $parameters->width,
            height: $parameters->height,
            kanyrHeight: $parameters->kanyr ? ($parameters->kanyrHeight ?? 0) : 0,
            bottomLead: $parameters->bottom,
        );
    }

    /**
     * Společná kresba pro záclonu i závěs: plocha se svislými řasami,
     * řasící stuha nahoře, volitelný kanýr a spodní olovo, kóty rozměrů.
     */
    protected function renderCurtainLike(float $width, float $height, float $kanyrHeight, bool $bottomLead): string
    {
        $scale = $this->scale($width, $height);
        $w = $width * $scale;
        $h = $height * $scale;
        $x = (self::CANVAS_WIDTH - $w) / 2;
        $y = (self::CANVAS_HEIGHT - $h) / 2;

        $outline = self::OUTLINE_COLOR;
        $detail = self::DETAIL_COLOR;

        $svg = $this->open();

        // Plocha výrobku
        $svg .= sprintf(
            '<rect x="%.1f" y="%.1f" width="%.1f" height="%.1f" fill="#fafaf9" stroke="%s" stroke-width="1.5"/>',
            $x, $y, $w, $h, $outline,
        );

        // Řasící stuha nahoře
        $svg .= sprintf(
            '<rect x="%.1f" y="%.1f" width="%.1f" height="6" fill="%s" stroke="none" opacity="0.6"/>',
            $x, $y, $w, $detail,
        );

        // Naznačení řasení — svislé vlnky
        $pleats = max(3, (int) floor($w / 28));
        for ($i = 1; $i < $pleats; $i++) {
            $px = $x + $i * ($w / $pleats);
            $svg .= sprintf(
                '<path d="M %.1f %.1f q 4 %.1f 0 %.1f q -4 %.1f 0 %.1f" fill="none" stroke="%s" stroke-width="1"/>',
                $px, $y + 6, $h * 0.25, $h * 0.5, $h * 0.25, $h * 0.5 - 6, $detail,
            );
        }

        // Kanýr — vodorovný pás v horní části
        if ($kanyrHeight > 0) {
            $kh = min($kanyrHeight * $scale, $h);
            $svg .= sprintf(
                '<line x1="%.1f" y1="%.1f" x2="%.1f" y2="%.1f" stroke="%s" stroke-width="1.5" stroke-dasharray="6 4"/>',
                $x, $y + $kh, $x + $w, $y + $kh, $outline,
            );
        }

        // Průběžné olovo dole
        if ($bottomLead) {
            $svg .= sprintf(
                '<line x1="%.1f" y1="%.1f" x2="%.1f" y2="%.1f" stroke="%s" stroke-width="4" stroke-linecap="round"/>',
                $x, $y + $h - 2, $x + $w, $y + $h - 2, $outline,
            );
        }

        $svg .= $this->horizontalDimension($x, $x + $w, $y + $h + 18, $this->formatCm($width));
        $svg .= $this->verticalDimension($y, $y + $h, $x - 18, $this->formatCm($height));

        return $svg.$this->close();
    }
}
