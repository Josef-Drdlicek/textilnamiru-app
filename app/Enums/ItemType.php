<?php

namespace App\Enums;

use App\Domain\Pricing\Calculators\BlindPriceCalculator;
use App\Domain\Pricing\Calculators\CurtainPriceCalculator;
use App\Domain\Pricing\Calculators\CushionPriceCalculator;
use App\Domain\Pricing\Calculators\DrapePriceCalculator;
use App\Domain\Pricing\Calculators\PillowPriceCalculator;
use App\Domain\Pricing\Calculators\StretchCeilingPriceCalculator;
use App\Domain\Pricing\Parameters\BlindParameters;
use App\Domain\Pricing\Parameters\CurtainParameters;
use App\Domain\Pricing\Parameters\CushionParameters;
use App\Domain\Pricing\Parameters\DrapeParameters;
use App\Domain\Pricing\Parameters\ItemParameters;
use App\Domain\Pricing\Parameters\PillowParameters;
use App\Domain\Pricing\Parameters\StretchCeilingParameters;
use App\Domain\Pricing\PriceCalculator;
use App\Domain\Schema\Renderers\BlindSchemaRenderer;
use App\Domain\Schema\Renderers\CurtainSchemaRenderer;
use App\Domain\Schema\Renderers\CushionSchemaRenderer;
use App\Domain\Schema\Renderers\DrapeSchemaRenderer;
use App\Domain\Schema\Renderers\PillowSchemaRenderer;
use App\Domain\Schema\Renderers\StretchCeilingSchemaRenderer;
use App\Domain\Schema\SchemaRenderer;
use App\Livewire\Configurators\AbstractConfigurator;
use App\Livewire\Configurators\BlindConfigurator;
use App\Livewire\Configurators\CurtainConfigurator;
use App\Livewire\Configurators\CushionConfigurator;
use App\Livewire\Configurators\DrapeConfigurator;
use App\Livewire\Configurators\PillowConfigurator;
use App\Livewire\Configurators\StretchCeilingConfigurator;

enum ItemType: string
{
    case Curtain = 'curtain';
    case Drape = 'drape';
    case Pillow = 'pillow';
    case Cushion = 'cushion';
    case Blind = 'blind';
    case StretchCeiling = 'stretch_ceiling';

    /**
     * @return class-string<PriceCalculator>
     */
    public function calculatorClass(): string
    {
        return match ($this) {
            self::Curtain => CurtainPriceCalculator::class,
            self::Drape => DrapePriceCalculator::class,
            self::Pillow => PillowPriceCalculator::class,
            self::Cushion => CushionPriceCalculator::class,
            self::Blind => BlindPriceCalculator::class,
            self::StretchCeiling => StretchCeilingPriceCalculator::class,
        };
    }

    /**
     * @return class-string<ItemParameters>
     */
    public function parametersClass(): string
    {
        return match ($this) {
            self::Curtain => CurtainParameters::class,
            self::Drape => DrapeParameters::class,
            self::Pillow => PillowParameters::class,
            self::Cushion => CushionParameters::class,
            self::Blind => BlindParameters::class,
            self::StretchCeiling => StretchCeilingParameters::class,
        };
    }

    /**
     * @return class-string<AbstractConfigurator>
     */
    public function configuratorClass(): string
    {
        return match ($this) {
            self::Curtain => CurtainConfigurator::class,
            self::Drape => DrapeConfigurator::class,
            self::Pillow => PillowConfigurator::class,
            self::Cushion => CushionConfigurator::class,
            self::Blind => BlindConfigurator::class,
            self::StretchCeiling => StretchCeilingConfigurator::class,
        };
    }

    /**
     * @return class-string<SchemaRenderer>
     */
    public function schemaRendererClass(): string
    {
        return match ($this) {
            self::Curtain => CurtainSchemaRenderer::class,
            self::Drape => DrapeSchemaRenderer::class,
            self::Pillow => PillowSchemaRenderer::class,
            self::Cushion => CushionSchemaRenderer::class,
            self::Blind => BlindSchemaRenderer::class,
            self::StretchCeiling => StretchCeilingSchemaRenderer::class,
        };
    }

    public function category(): ProductCategory
    {
        return match ($this) {
            self::StretchCeiling => ProductCategory::Ceiling,
            default => ProductCategory::Sewing,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Curtain => 'Záclona',
            self::Drape => 'Závěs',
            self::Pillow => 'Polštář',
            self::Cushion => 'Sedák',
            self::Blind => 'Římská roleta',
            self::StretchCeiling => 'Vypínaný strop',
        };
    }

    /**
     * Popisek druhého rozměru (u sedáku a stropu jde o délku, ne výšku).
     */
    public function heightLabel(): string
    {
        return match ($this) {
            self::Cushion, self::StretchCeiling => 'Délka',
            default => 'Výška',
        };
    }

    public function materialLabel(): string
    {
        return match ($this) {
            self::StretchCeiling => 'Fólie',
            default => 'Materiál (látka)',
        };
    }

    public function materialHint(): string
    {
        return match ($this) {
            self::StretchCeiling => 'Typ a barva fólie.',
            default => 'Název látky, kterou dodáte — dílna podle něj sčítá spotřebu.',
        };
    }

    public function materialPlaceholder(): string
    {
        return match ($this) {
            self::StretchCeiling => 'např. bílá matná',
            default => 'např. voal bílý',
        };
    }
}
