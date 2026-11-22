<?php

namespace App\Domain\Suppliers\Laguna;

use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Čtení veřejného katalogu Laguna Textil (záclony, dekorační látky – viz LagunaCatalog).
 *
 * Výpis se načítá stejně jako na webu dodavatele: POST /cz/filter-url po stránkách
 * (24 položek), s CSRF tokenem a cookies z úvodní stránky kategorie.
 * Mezi dotazy je pauza, ať web dodavatele nezatěžujeme.
 */
class LagunaClient
{
    public const string BASE_URL = 'https://www.lagunatextil.com';

    private const int PAUSE_MS = 200;

    /** Náhled látky je malý. Větší soubor by při čtení do paměti shodil celý import. */
    private const int MAX_IMAGE_BYTES = 8 * 1024 * 1024;

    /** Sdílená schránka: dodavatel po každém výpisu otočí XSRF-TOKEN v cookie. */
    private CookieJar $cookies;

    private ?PendingRequest $http = null;

    private ?PendingRequest $catalog = null;

    private ?PendingRequest $files = null;

    private ?string $csrf = null;

    public function __construct()
    {
        $this->cookies = new CookieJar;
    }

    /**
     * @param  (callable(int $page, int $lastPage): void)|null  $progress
     * @return list<LagunaItem>
     */
    public function items(?int $maxPages = null, ?callable $progress = null, LagunaCatalog $catalog = LagunaCatalog::Curtains): array
    {
        $items = [];
        $page = 1;

        do {
            $response = $this->catalogClient()
                ->replaceHeaders($this->catalogHeaders($catalog))
                ->post('/cz/filter-url', [
                    'page' => $page,
                    'product_category_id' => $catalog->categoryId(),
                    'search' => '',
                    'colors' => [],
                    'is_new' => false,
                    'parameters' => [],
                ]);
            $this->rememberCookies($response);
            $response = $response->throw()->json();

            foreach ($response['data'] ?? [] as $row) {
                $items[] = new LagunaItem(
                    title: trim($row['title']),
                    imageUrl: $this->originalImageUrl($row['image_url'] ?? ''),
                    link: $row['link'],
                );
            }

            $lastPage = (int) ($response['meta']['last_page'] ?? $page);
            $progress && $progress($page, $lastPage);
            $page++;
            $this->pause();
        } while ($page <= $lastPage && ($maxPages === null || $page <= $maxPages));

        return $items;
    }

    public function detailHtml(string $link): string
    {
        $html = $this->client()->get($link)->throw()->body();
        $this->pause();

        return $html;
    }

    public function download(string $url): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'laguna');
        if ($tmp === false) {
            throw new RuntimeException('Nelze vytvořit dočasný soubor pro fotku.');
        }

        try {
            $this->fileClient()->sink($tmp)->get($url)->throw();
            $size = filesize($tmp);

            if ($size === false || $size > self::MAX_IMAGE_BYTES || $size === 0) {
                throw new RuntimeException('soubor je prázdný nebo větší než 8 MB');
            }

            $body = file_get_contents($tmp);
            if ($body === false) {
                throw new RuntimeException('soubor se nepodařilo přečíst');
            }

            return $body;
        } finally {
            if (is_file($tmp)) {
                unlink($tmp);
            }
            $this->pause();
        }
    }

    /**
     * Náhled přes fw.photos (…/600x600/https%3A%2F%2F…) => originální soubor u dodavatele.
     */
    public function originalImageUrl(string $url): string
    {
        if (preg_match('~/\d+x\d+/(https?%3A.+)$~', $url, $m)) {
            return rawurldecode($m[1]);
        }

        return $url;
    }

    private function client(): PendingRequest
    {
        return $this->http ??= $this->baseRequest()->timeout(30);
    }

    /** Výpis katalogu chce JSON. withHeaders hlavičky lepí, proto se token mění přes replaceHeaders. */
    private function catalogClient(): PendingRequest
    {
        return $this->catalog ??= $this->baseRequest()->timeout(30)->acceptJson();
    }

    /** Fotky jdou zvlášť: sink by se jinak přilepil i na výpis katalogu. */
    private function fileClient(): PendingRequest
    {
        return $this->files ??= $this->baseRequest()->timeout(60);
    }

    private function baseRequest(): PendingRequest
    {
        return Http::baseUrl(self::BASE_URL)
            ->withOptions(['cookies' => $this->cookies])
            ->withUserAgent('TextilNaMiru-katalog/1.0')
            ->retry(3, 1000);
    }

    /**
     * Výpis bere token z cookie, ne z meta tagu. Meta platí jen pro první dotaz;
     * další stránka se starým tokenem vrací 419.
     *
     * @return array{X-Requested-With: string, X-XSRF-TOKEN?: string, X-CSRF-TOKEN?: string}
     */
    private function catalogHeaders(LagunaCatalog $catalog): array
    {
        $meta = $this->csrf($catalog);

        $headers = ['X-Requested-With' => 'XMLHttpRequest'];
        $xsrf = $this->xsrfToken();

        if ($xsrf !== null) {
            $headers['X-XSRF-TOKEN'] = $xsrf;
        } else {
            $headers['X-CSRF-TOKEN'] = $meta;
        }

        return $headers;
    }

    private function csrf(LagunaCatalog $catalog): string
    {
        if ($this->csrf === null) {
            $page = $this->client()->get($catalog->path());
            $this->rememberCookies($page);
            $html = $page->throw()->body();

            if (! preg_match('/<meta name="csrf-token" content="([^"]+)"/', $html, $m)) {
                throw new RuntimeException('Na stránce dodavatele chybí CSRF token – změnil se web Laguna Textil?');
            }

            $this->csrf = $m[1];
        }

        return $this->csrf;
    }

    /**
     * Guzzle schránku z falešných odpovědí v testech neplní. Z živého webu
     * ji naplní sám, druhé uložení stejné cookie je neškodné.
     */
    private function rememberCookies(Response $response): void
    {
        foreach ($response->headers() as $name => $values) {
            if (strcasecmp($name, 'Set-Cookie') !== 0) {
                continue;
            }

            foreach ($values as $line) {
                $cookie = SetCookie::fromString($line);
                if ($cookie->getName() === '') {
                    continue;
                }
                if ($cookie->getDomain() === null || $cookie->getDomain() === '') {
                    $cookie->setDomain((string) parse_url(self::BASE_URL, PHP_URL_HOST));
                }
                $this->cookies->setCookie($cookie);
            }
        }
    }

    private function xsrfToken(): ?string
    {
        foreach ($this->cookies as $cookie) {
            if ($cookie->getName() === 'XSRF-TOKEN') {
                return urldecode($cookie->getValue());
            }
        }

        return null;
    }

    private function pause(): void
    {
        if (! app()->runningUnitTests()) {
            usleep(self::PAUSE_MS * 1000);
        }
    }
}
