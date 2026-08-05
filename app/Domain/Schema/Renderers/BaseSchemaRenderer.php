<?php

namespace App\Domain\Schema\Renderers;

use App\Domain\Schema\SchemaRenderer;

abstract class BaseSchemaRenderer implements SchemaRenderer
{
    protected const int CANVAS_WIDTH = 460;

    protected const int CANVAS_HEIGHT = 340;

    /** Prostor po stranách na kótovací čáry a popisky. */
    protected const int MARGIN = 56;

    protected const string OUTLINE_COLOR = '#57534e';

    protected const string DETAIL_COLOR = '#a8a29e';

    protected const string DIMENSION_COLOR = '#0f766e';

    /**
     * Měřítko [px/cm], aby se výrobek vešel do kreslicí plochy.
     */
    protected function scale(float $widthCm, float $heightCm): float
    {
        $availableWidth = self::CANVAS_WIDTH - 2 * self::MARGIN;
        $availableHeight = self::CANVAS_HEIGHT - 2 * self::MARGIN;

        return min($availableWidth / max($widthCm, 1), $availableHeight / max($heightCm, 1));
    }

    protected function open(): string
    {
        $w = self::CANVAS_WIDTH;
        $h = self::CANVAS_HEIGHT;

        return <<<SVG
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {$w} {$h}" role="img"
                 font-family="Raleway, sans-serif" font-size="13">
            SVG;
    }

    protected function close(): string
    {
        return '</svg>';
    }

    /**
     * Vodorovná kóta s popiskem pod čárou.
     */
    protected function horizontalDimension(float $x1, float $x2, float $y, string $label): string
    {
        $c = self::DIMENSION_COLOR;
        $mid = ($x1 + $x2) / 2;
        $textY = $y + 18;

        return <<<SVG
            <g stroke="{$c}" fill="{$c}">
                <line x1="{$x1}" y1="{$y}" x2="{$x2}" y2="{$y}" stroke-width="1"/>
                <line x1="{$x1}" y1="{$y}" x2="{$x1}" y2="{$y}" stroke-width="1"/>
                <path d="M {$x1} {$y} l 8 -4 v 8 z" stroke="none"/>
                <path d="M {$x2} {$y} l -8 -4 v 8 z" stroke="none"/>
                <text x="{$mid}" y="{$textY}" text-anchor="middle" stroke="none">{$label}</text>
            </g>
            SVG;
    }

    /**
     * Svislá kóta s popiskem otočeným podél čáry.
     */
    protected function verticalDimension(float $y1, float $y2, float $x, string $label): string
    {
        $c = self::DIMENSION_COLOR;
        $mid = ($y1 + $y2) / 2;
        $textX = $x - 8;

        return <<<SVG
            <g stroke="{$c}" fill="{$c}">
                <line x1="{$x}" y1="{$y1}" x2="{$x}" y2="{$y2}" stroke-width="1"/>
                <path d="M {$x} {$y1} l -4 8 h 8 z" stroke="none"/>
                <path d="M {$x} {$y2} l -4 -8 h 8 z" stroke="none"/>
                <text x="{$textX}" y="{$mid}" text-anchor="middle" stroke="none"
                      transform="rotate(-90 {$textX} {$mid})">{$label}</text>
            </g>
            SVG;
    }

    protected function formatCm(float $value): string
    {
        $formatted = rtrim(rtrim(number_format($value, 1, ',', ' '), '0'), ',');

        return "{$formatted} cm";
    }
}
