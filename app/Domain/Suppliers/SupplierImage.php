<?php

namespace App\Domain\Suppliers;

use finfo;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Uloží fotku stažené od dodavatele. Fotky servírujeme z vlastní domény, proto
 * typ souboru určuje obsah, ne přípona v URL: SVG nebo HTML (chybová stránka,
 * napadený web dodavatele) by se jinak spustilo jako skript na našem webu.
 */
final class SupplierImage
{
    /** Povolené typy => přípona souboru. */
    private const array TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    public const int MAX_BYTES = 12 * 1024 * 1024;

    /**
     * @param  string  $pathWithoutExtension  např. „shades/pavon/latky/col-100“
     * @return string uložená cesta včetně přípony podle skutečného typu
     *
     * @throws RuntimeException když soubor není obrázek povoleného typu
     */
    public static function store(string $pathWithoutExtension, string $body): string
    {
        if ($body === '' || strlen($body) > self::MAX_BYTES) {
            throw new RuntimeException('soubor je prázdný nebo větší než 12 MB');
        }

        $type = (new finfo(FILEINFO_MIME_TYPE))->buffer($body);
        $extension = self::TYPES[$type] ?? throw new RuntimeException("soubor není fotka ({$type})");

        $path = "{$pathWithoutExtension}.{$extension}";
        Storage::disk()->put($path, $body);

        return $path;
    }
}
