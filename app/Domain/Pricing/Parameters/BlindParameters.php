<?php

namespace App\Domain\Pricing\Parameters;

final readonly class BlindParameters implements ItemParameters
{
    public function __construct(
        public float $width,            // [cm], 10–300
        public float $height,           // [cm], 10–300
        public bool $mechanism,         // vlastní mechanizmus; do ceny nevstupuje
        public bool $tunnels,           // odšité tunýlky
        public bool $finishing,         // kompletace
        public ?int $cells,             // počet polí; do ceny nevstupuje
        public ?float $cellHeightTop,   // [cm]; do ceny nevstupuje
        public ?float $cellHeight,      // [cm]; do ceny nevstupuje
        public ?float $cellHeightBottom, // [cm]; do ceny nevstupuje
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            width: (float) $data['width'],
            height: (float) $data['height'],
            mechanism: (bool) $data['mechanism'],
            tunnels: (bool) $data['tunnels'],
            finishing: (bool) $data['finishing'],
            cells: isset($data['cells']) ? (int) $data['cells'] : null,
            cellHeightTop: isset($data['cell_height_top']) ? (float) $data['cell_height_top'] : null,
            cellHeight: isset($data['cell_height']) ? (float) $data['cell_height'] : null,
            cellHeightBottom: isset($data['cell_height_bottom']) ? (float) $data['cell_height_bottom'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'width' => $this->width,
            'height' => $this->height,
            'mechanism' => $this->mechanism,
            'tunnels' => $this->tunnels,
            'finishing' => $this->finishing,
            'cells' => $this->cells,
            'cell_height_top' => $this->cellHeightTop,
            'cell_height' => $this->cellHeight,
            'cell_height_bottom' => $this->cellHeightBottom,
        ];
    }
}
