<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/**
 * Obsah „fotky“ pro fake HTTP: hlavička JPEG (importy přijímají jen skutečné obrázky)
 * a za ní značka, podle které test pozná, který soubor se uložil.
 */
function fakeJpeg(string $tag): string
{
    return "\xFF\xD8\xFF\xE0\x00\x10JFIF\x00\x01\x01\x00\x00\x01\x00\x01\x00\x00".$tag;
}
