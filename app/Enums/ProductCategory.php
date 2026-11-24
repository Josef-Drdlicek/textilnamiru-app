<?php

namespace App\Enums;

/**
 * Obor nabídky. Veřejná kalkulačka má pro každý obor vlastní stránku
 * a nabízí jen jeho výrobky; texty kalkulačky jsou proto tady.
 */
enum ProductCategory: string
{
    case Sewing = 'sewing';
    case Ceiling = 'ceiling';

    public function label(): string
    {
        return match ($this) {
            self::Sewing => 'Zakázkové šití',
            self::Ceiling => 'Vypínané stropy',
        };
    }

    /**
     * @return list<ItemType>
     */
    public function itemTypes(): array
    {
        return array_values(array_filter(
            ItemType::cases(),
            fn (ItemType $type) => $type->category() === $this,
        ));
    }

    /**
     * Provedení, která kalkulačka oboru nabízí jako dlaždice s fotkou
     * místo výběru výrobku; prázdné = dlaždice podle výrobků.
     *
     * @return list<CeilingVariant>
     */
    public function estimatorVariants(): array
    {
        return match ($this) {
            self::Sewing => [],
            self::Ceiling => CeilingVariant::cases(),
        };
    }

    public function estimatorTitle(): string
    {
        return match ($this) {
            self::Sewing => 'Kolik bude stát ušití?',
            self::Ceiling => 'Kolik bude stát vypínaný strop?',
        };
    }

    public function estimatorIntro(): string
    {
        return match ($this) {
            self::Sewing => 'Vyberte produkt, zadejte rozměry a hned vidíte orientační cenu za ušití. Přesnou nabídku včetně látky připravíme zdarma po zaměření.',
            self::Ceiling => 'Zadejte rozměry místnosti a hned vidíte orientační cenu stropu. Přesnou nabídku připravíme zdarma po zaměření.',
        };
    }

    public function estimatorQuestion(): string
    {
        return match ($this) {
            self::Sewing => '1. Co potřebujete ušít?',
            self::Ceiling => '1. Typ stropu',
        };
    }

    public function estimatorNote(): string
    {
        return match ($this) {
            self::Sewing => 'Orientační cena za ušití. Nezahrnuje látku, dopravu a montáž — přesnou nabídku připravíme zdarma po zaměření.',
            self::Ceiling => 'Orientační cena za plochu stropu. Nezahrnuje osvětlení a dopravu — přesnou nabídku připravíme zdarma po zaměření.',
        };
    }
}
