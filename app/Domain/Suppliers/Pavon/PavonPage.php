<?php

namespace App\Domain\Suppliers\Pavon;

/**
 * Obsah produktové stránky Pavon převzatý na náš web.
 */
final readonly class PavonPage
{
    /**
     * @param  list<string>  $paragraphs
     * @param  list<array{type: string, text?: string, items?: list<string>}>  $technical
     * @param  list<array{url: string, alt: string}>  $images
     */
    public function __construct(
        public ?string $subtitle,
        public ?string $slogan,
        public array $paragraphs,
        public array $technical,
        public ?int $minWidth,
        public ?int $maxWidth,
        public ?int $minHeight,
        public ?int $maxHeight,
        public array $images,
    ) {}
}
