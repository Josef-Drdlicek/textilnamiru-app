<?php

namespace App\Domain\Suppliers\Laguna;

/**
 * Jedna položka katalogu Laguna Textil = jedna barva jedné látky v jedné výšce,
 * např. „11101/180/03“: název 11101, výška 180 cm, barva 03.
 */
final readonly class LagunaItem
{
    public function __construct(
        public string $title,     // označení dodavatele, např. 11101/180/03 FR
        public string $imageUrl,  // originální fotka (bez náhledového proxy)
        public string $link,      // detail produktu u dodavatele
    ) {}

    /** Poznámky dodavatele za označením, které do našeho názvu nepatří („ó.n.“ = maďarská poznámka). */
    private const array IGNORED_SUFFIXES = ['ó.n.'];

    /**
     * Přípona za posledním segmentem („FR“ = nehořlavá, „VR“, „AV TCS“…), patří k názvu látky.
     * Mezera uvnitř názvu („LA PEDRERA/280/01“) příponou není.
     */
    public function suffix(): ?string
    {
        if (! preg_match('~^(.*/\S+)\s+([^/]+)$~u', trim($this->title), $m)) {
            return null;
        }

        $suffix = trim($m[2]);

        return in_array($suffix, self::IGNORED_SUFFIXES, true) ? null : $suffix;
    }

    /**
     * @return list<string> části označení bez přípony, např. [11101, 180, 03]
     */
    public function segments(): array
    {
        $code = preg_match('~^(.*/\S+)\s+[^/]+$~u', trim($this->title), $m) ? $m[1] : trim($this->title);

        // Jednopísmenné řady dodavatel píše nejednotně („v/597“ i „V/597“).
        return array_map(
            fn (string $segment) => mb_strlen($segment) === 1 ? mb_strtoupper($segment) : $segment,
            explode('/', $code),
        );
    }

    /**
     * Klíč skupiny = vše kromě posledního segmentu (+ přípona), např. „11101/180“ nebo „1080/315 FR“.
     */
    public function groupKey(): string
    {
        $segments = $this->segments();

        return implode('/', array_slice($segments, 0, -1)).($this->suffix() ? ' '.$this->suffix() : '');
    }

    public function lastSegment(): string
    {
        $segments = $this->segments();

        return end($segments);
    }
}
