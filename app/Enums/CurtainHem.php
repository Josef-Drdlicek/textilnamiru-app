<?php

namespace App\Enums;

/**
 * Způsob zakončení spodního okraje (krok „Způsob šití“).
 */
enum CurtainHem: string
{
    case Lead = 'lead';
    case Fold = 'fold';

    public function label(): string
    {
        return match ($this) {
            self::Lead => 'Klasika s olůvkem',
            self::Fold => 'Se záložkou',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Lead => 'Do spodního okraje všijeme olověnou šňůrku, která látku rovně vypne.',
            self::Fold => 'Spodní okraj založíme a zašijeme, do rohů vložíme olůvka.',
        };
    }
}
