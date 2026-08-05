<?php

namespace App\Domain\Pricing\Parameters;

use App\Enums\RightLeft;

final readonly class CurtainParameters implements ItemParameters
{
    public function __construct(
        public float $width,          // hotová šířka [cm]
        public float $height,         // hotová výška [cm]
        public float $materialWidth,  // šířka dodaného materiálu [cm]
        public string $ribbonCode,    // kód řasící stuhy (viz tabulka ribbons)
        public float $ratio,          // poměr řasení (u vlastní stuhy zadaný ručně)
        public bool $kanyr,
        public ?float $kanyrHeight,   // výška kanýru [cm]
        public RightLeft $rightLeft,
        public bool $corners,         // rohová olůvka
        public bool $bottom,          // všití průběžného olova
        public bool $narasit,
        public bool $snails,          // navlečení háčků (šnečků)
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            width: (float) $data['width'],
            height: (float) $data['height'],
            materialWidth: (float) $data['material_width'],
            ribbonCode: (string) $data['ribbon_code'],
            ratio: (float) $data['ratio'],
            kanyr: (bool) $data['kanyr'],
            kanyrHeight: isset($data['kanyr_height']) ? (float) $data['kanyr_height'] : null,
            rightLeft: RightLeft::from($data['right_left']),
            corners: (bool) $data['corners'],
            bottom: (bool) $data['bottom'],
            narasit: (bool) $data['narasit'],
            snails: (bool) $data['snails'],
        );
    }

    public function toArray(): array
    {
        return [
            'width' => $this->width,
            'height' => $this->height,
            'material_width' => $this->materialWidth,
            'ribbon_code' => $this->ribbonCode,
            'ratio' => $this->ratio,
            'kanyr' => $this->kanyr,
            'kanyr_height' => $this->kanyrHeight,
            'right_left' => $this->rightLeft->value,
            'corners' => $this->corners,
            'bottom' => $this->bottom,
            'narasit' => $this->narasit,
            'snails' => $this->snails,
        ];
    }
}
