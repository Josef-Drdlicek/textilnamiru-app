<?php

namespace App\Enums;

enum RightLeft: string
{
    case Right = 'right';
    case Left = 'left';
    case Mirror = 'mirror';

    public function label(): string
    {
        return match ($this) {
            self::Right => 'Pravá',
            self::Left => 'Levá',
            self::Mirror => 'Zrcadlově',
        };
    }
}
