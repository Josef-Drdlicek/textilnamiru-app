<?php

namespace App\Domain\Suppliers\Pavon;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Čtení veřejných stránek rolet Pavon a stahování fotek (s pauzou mezi dotazy).
 */
class PavonClient
{
    private const int PAUSE_MS = 200;

    /** Fotka nad tuto velikost se nestáhne (ochrana paměti importu). */
    private const int MAX_IMAGE_BYTES = 12 * 1024 * 1024;

    public function page(string $path): string
    {
        $html = $this->request()->timeout(30)->get($path)->throw()->body();
        $this->pause();

        return $html;
    }

    public function download(string $url): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'pavon');
        if ($tmp === false) {
            throw new RuntimeException('Nelze vytvořit dočasný soubor pro fotku.');
        }

        try {
            $this->request()->timeout(60)->sink($tmp)->get($url)->throw();
            $size = filesize($tmp);

            if ($size === false || $size === 0 || $size > self::MAX_IMAGE_BYTES) {
                throw new RuntimeException('soubor je prázdný nebo větší než 12 MB');
            }

            return (string) file_get_contents($tmp);
        } finally {
            is_file($tmp) && unlink($tmp);
            $this->pause();
        }
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl(PavonCatalog::BASE_URL)
            ->withUserAgent('TextilNaMiru-katalog/1.0')
            ->retry(3, 1000);
    }

    private function pause(): void
    {
        if (! app()->runningUnitTests()) {
            usleep(self::PAUSE_MS * 1000);
        }
    }
}
