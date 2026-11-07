<?php

namespace App\Domain\Pricing\Parameters;

use App\Enums\ZipperSide;

final readonly class CushionParameters implements ItemParameters
{
    public function __construct(
        public float $width,       // [cm], 10–200
        public float $height,      // délka [cm], 10–200
        public float $depth,       // tloušťka [cm]; určuje počet vrstev molitanu
        public bool $foam,         // molitan
        public bool $felt,         // rouno
        public bool $ties,         // úvazy
        public ZipperSide $zipper, // do ceny nevstupuje
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            width: (float) $data['width'],
            height: (float) $data['height'],
            depth: (float) $data['depth'],
            foam: (bool) ($data['foam'] ?? false),
            felt: (bool) ($data['felt'] ?? false),
            ties: (bool) ($data['ties'] ?? false),
            zipper: ZipperSide::from($data['zipper']),
        );
    }

    public function toArray(): array
    {
        return [
            'width' => $this->width,
            'height' => $this->height,
            'depth' => $this->depth,
            'foam' => $this->foam,
            'felt' => $this->felt,
            'ties' => $this->ties,
            'zipper' => $this->zipper->value,
        ];
    }
}
