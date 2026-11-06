<?php

namespace App\Enums;

use App\Domain\Pricing\Calculators\BlindPriceCalculator;
use App\Domain\Pricing\Calculators\CurtainPriceCalculator;
use App\Domain\Pricing\Calculators\CushionPriceCalculator;
use App\Domain\Pricing\Calculators\DrapePriceCalculator;
use App\Domain\Pricing\Calculators\PillowPriceCalculator;
use App\Domain\Pricing\Parameters\BlindParameters;
use App\Domain\Pricing\Parameters\CurtainParameters;
use App\Domain\Pricing\Parameters\CushionParameters;
use App\Domain\Pricing\Parameters\DrapeParameters;
use App\Domain\Pricing\Parameters\ItemParameters;
use App\Domain\Pricing\Parameters\PillowParameters;
use App\Domain\Pricing\PriceCalculator;
use App\Domain\Schema\Renderers\BlindSchemaRenderer;
use App\Domain\Schema\Renderers\CurtainSchemaRenderer;
use App\Domain\Schema\Renderers\CushionSchemaRenderer;
use App\Domain\Schema\Renderers\DrapeSchemaRenderer;
use App\Domain\Schema\Renderers\PillowSchemaRenderer;
use App\Domain\Schema\SchemaRenderer;
use App\Livewire\Configurators\AbstractConfigurator;
use App\Livewire\Configurators\BlindConfigurator;
use App\Livewire\Configurators\CurtainConfigurator;
use App\Livewire\Configurators\CushionConfigurator;
use App\Livewire\Configurators\DrapeConfigurator;
use App\Livewire\Configurators\PillowConfigurator;

enum ItemType: string
{
    case Curtain = 'curtain';
    case Drape = 'drape';
    case Pillow = 'pillow';
    case Cushion = 'cushion';
    case Blind = 'blind';

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
        };
    }
}
