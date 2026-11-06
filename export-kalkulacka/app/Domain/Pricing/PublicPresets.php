<?php

namespace App\Domain\Pricing;

use App\Domain\Pricing\Parameters\BlindParameters;
use App\Domain\Pricing\Parameters\CurtainParameters;
use App\Domain\Pricing\Parameters\CushionParameters;
use App\Domain\Pricing\Parameters\DrapeParameters;
use App\Domain\Pricing\Parameters\ItemParameters;
use App\Domain\Pricing\Parameters\PillowParameters;
use App\Enums\ItemType;
use App\Enums\RightLeft;
use App\Enums\ZipperSide;

/**
 * Referenční konfigurace pro veřejný web. Jediné místo, kde se laický vstup
 * z kalkulačky (rozměry, nařasení, výplň) doplňuje o odborné parametry,
 * které běžný zákazník nezná — díky tomu „cena od" v katalogu i orientační
 * cena v kalkulačce počítají vždy nad stejnými předpoklady.
 */
final class PublicPresets
{
    /** Kód výchozí řasící stuhy (povolené poměry 1.5 / 1.85 / 2 / 2.5). */
    public const string DEFAULT_RIBBON = 'siroky70';

    /** Šířka role materiálu předpokládaná u záclon [cm] (voály se šijí z výšky role). */
    public const float CURTAIN_MATERIAL_WIDTH = 300.0;

    /** Šířka role materiálu předpokládaná u závěsů [cm] (dekorační látky). */
    public const float DRAPE_MATERIAL_WIDTH = 140.0;

    /** Veřejné volby nařasení: klíč => [popisek, poměr]. */
    public const array RUFFLE_OPTIONS = [
        'light' => ['label' => 'Jemné (1 : 1,5)', 'ratio' => 1.5],
        'standard' => ['label' => 'Standardní (1 : 2)', 'ratio' => 2.0],
        'rich' => ['label' => 'Bohaté (1 : 2,5)', 'ratio' => 2.5],
    ];

    /**
     * Parametry pro orientační kalkulačku — laický vstup doplněný o defaulty.
     *
     * @param  array{width: float, height: float, ratio?: float, stuffing?: bool}  $input
     */
    public static function estimatorParameters(ItemType $type, array $input): ItemParameters
    {
        return match ($type) {
            ItemType::Curtain => new CurtainParameters(
                width: $input['width'],
                height: $input['height'],
                materialWidth: self::CURTAIN_MATERIAL_WIDTH,
                ribbonCode: self::DEFAULT_RIBBON,
                ratio: $input['ratio'] ?? 2.0,
                kanyr: false,
                kanyrHeight: null,
                rightLeft: RightLeft::Right,
                corners: false,
                bottom: false,
                narasit: false,
                snails: false,
            ),
            ItemType::Drape => new DrapeParameters(
                width: $input['width'],
                height: $input['height'],
                materialWidth: self::DRAPE_MATERIAL_WIDTH,
                ribbonCode: self::DEFAULT_RIBBON,
                ratio: $input['ratio'] ?? 2.0,
                raport: null,
                folding: null,
                kanyr: false,
                kanyrHeight: null,
                rightLeft: RightLeft::Right,
                corners: false,
                bottom: false,
                narasit: false,
                snails: false,
            ),
            ItemType::Pillow => new PillowParameters(
                width: $input['width'],
                height: $input['height'],
                stuffing: $input['stuffing'] ?? false,
                zipper: ZipperSide::Short,
            ),
            ItemType::Cushion => new CushionParameters(
                width: $input['width'],
                height: $input['height'],
                depth: 5.0,
                stuffing: $input['stuffing'] ?? false,
                zipper: ZipperSide::Short,
            ),
            ItemType::Blind => new BlindParameters(
                width: $input['width'],
                height: $input['height'],
                mechanism: false,
                tunnels: true,
                finishing: true,
                cells: null,
                cellHeightTop: null,
                cellHeight: null,
                cellHeightBottom: null,
            ),
        };
    }

    /**
     * Nejmenší rozumná konfigurace typu — základ katalogové „ceny od".
     */
    public static function referenceParameters(ItemType $type): ItemParameters
    {
        return self::estimatorParameters($type, match ($type) {
            ItemType::Curtain => ['width' => 100.0, 'height' => 120.0],
            ItemType::Drape => ['width' => 140.0, 'height' => 250.0],
            ItemType::Pillow => ['width' => 40.0, 'height' => 40.0],
            ItemType::Cushion => ['width' => 40.0, 'height' => 40.0],
            ItemType::Blind => ['width' => 60.0, 'height' => 120.0],
        });
    }
}
