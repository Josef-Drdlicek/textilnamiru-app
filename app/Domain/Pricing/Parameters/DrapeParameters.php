<?php

namespace App\Domain\Pricing\Parameters;

use App\Enums\RightLeft;

final readonly class DrapeParameters implements ItemParameters
{
    public function __construct(
        public float $width,          // hotová šířka [cm]
        public float $height,         // hotová výška [cm]
        public float $materialWidth,  // šířka dodaného materiálu [cm]
        public string $ribbonCode,    // kód řasící stuhy (viz tabulka ribbons)
        public float $ratio,          // poměr řasení (u vlastní stuhy zadaný ručně)
        public ?float $raport,        // raport délky [cm]; do ceny nevstupuje (viz zadání §7.2)
        public ?float $folding,       // zalomení; násobí sazbu NADSITI_ZACLONA za metr šířky
        public bool $kanyr,
        public ?float $kanyrHeight,   // výška kanýru [cm]
        public RightLeft $rightLeft,
        public bool $corners,         // rohová olůvka (50g)
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
            raport: isset($data['raport']) ? (float) $data['raport'] : null,
            folding: isset($data['folding']) ? (float) $data['folding'] : null,
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
            'raport' => $this->raport,
            'folding' => $this->folding,
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
