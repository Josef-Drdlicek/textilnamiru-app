<?php

namespace App\Enums;

/**
 * Způsob zavěšení kolejnice či garnýže – podle něj se liší cena.
 */
enum RailMounting: string
{
    case Ceiling = 'ceiling';
    case Wall = 'wall';

    public function label(): string
    {
        return match ($this) {
            self::Ceiling => 'Stropní držák',
            self::Wall => 'Stěnový držák',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Ceiling => 'Kolejnice se přišroubuje do stropu.',
            self::Wall => 'Kolejnice se uchytí na stěnu nad oknem.',
        };
    }
}
