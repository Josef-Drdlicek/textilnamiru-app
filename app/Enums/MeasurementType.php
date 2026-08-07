<?php

namespace App\Enums;

/**
 * Způsob zaměření oken/prostoru u poptávky z webu.
 */
enum MeasurementType: string
{
    case Professional = 'professional';
    case Diy = 'diy';

    public function label(): string
    {
        return match ($this) {
            self::Professional => 'Odborné zaměření u vás',
            self::Diy => 'Zaměřím si svépomocí',
        };
    }
}
