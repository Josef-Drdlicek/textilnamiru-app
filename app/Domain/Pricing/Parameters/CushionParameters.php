<?php

namespace App\Domain\Pricing\Parameters;

use App\Enums\ZipperSide;

final readonly class CushionParameters implements ItemParameters
{
    public function __construct(
        public float $width,      // [cm], 10–100
        public float $height,     // [cm], 10–100
        public float $depth,      // hloubka [cm]; do ceny nevstupuje (viz zadání §7.4)
        public bool $stuffing,    // výplň
        public ZipperSide $zipper, // do ceny nevstupuje
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            width: (float) $data['width'],
            height: (float) $data['height'],
            depth: (float) $data['depth'],
            stuffing: (bool) $data['stuffing'],
            zipper: ZipperSide::from($data['zipper']),
        );
    }

    public function toArray(): array
    {
        return [
            'width' => $this->width,
            'height' => $this->height,
            'depth' => $this->depth,
            'stuffing' => $this->stuffing,
            'zipper' => $this->zipper->value,
        ];
    }
}
