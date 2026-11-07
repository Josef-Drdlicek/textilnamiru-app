<?php

namespace App\Domain\Pricing\Parameters;

use App\Enums\ZipperSide;

final readonly class PillowParameters implements ItemParameters
{
    public function __construct(
        public float $width,       // [cm], 10–100
        public float $height,      // [cm], 10–100
        public bool $stuffing,     // výplň
        public bool $complex,      // složitý tvar = dražší ušití
        public ZipperSide $zipper, // se zipem se účtuje ušití záložky
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            width: (float) $data['width'],
            height: (float) $data['height'],
            stuffing: (bool) $data['stuffing'],
            complex: (bool) ($data['complex'] ?? false),
            zipper: ZipperSide::from($data['zipper']),
        );
    }

    public function toArray(): array
    {
        return [
            'width' => $this->width,
            'height' => $this->height,
            'stuffing' => $this->stuffing,
            'complex' => $this->complex,
            'zipper' => $this->zipper->value,
        ];
    }
}
