<?php

namespace App\Domain\Pricing\Parameters;

use App\Enums\CeilingSurface;

final readonly class StretchCeilingParameters implements ItemParameters
{
    public function __construct(
        public float $width,             // [cm]
        public float $length,            // [cm]
        public CeilingSurface $surface,  // do ceny nevstupuje
        public float $ledLength,         // LED pásek [m]; 0 = bez LED
        public float $distance,          // doprava [km]; 0 = bez dopravy
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            width: (float) $data['width'],
            length: (float) $data['length'],
            surface: CeilingSurface::from($data['surface']),
            ledLength: (float) ($data['led_length'] ?? 0),
            distance: (float) ($data['distance'] ?? 0),
        );
    }

    public function toArray(): array
    {
        return [
            'width' => $this->width,
            'length' => $this->length,
            'surface' => $this->surface->value,
            'led_length' => $this->ledLength,
            'distance' => $this->distance,
        ];
    }

    public function areaM2(): float
    {
        return ($this->width / 100) * ($this->length / 100);
    }
}
