<?php

namespace App\Enums;

enum ZipperSide: string
{
    case Short = 'short';
    case Long = 'long';

    public function label(): string
    {
        return match ($this) {
            self::Short => 'Kratší strana',
            self::Long => 'Delší strana',
        };
    }
}
