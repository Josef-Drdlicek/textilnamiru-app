<?php

namespace App\Enums;

enum ZipperSide: string
{
    case None = 'none';
    case Short = 'short';
    case Long = 'long';

    public function label(): string
    {
        return match ($this) {
            self::None => 'Bez zipu',
            self::Short => 'Kratší strana',
            self::Long => 'Delší strana',
        };
    }

    public function hasZipper(): bool
    {
        return $this !== self::None;
    }
}
