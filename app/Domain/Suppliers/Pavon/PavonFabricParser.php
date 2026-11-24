<?php

namespace App\Domain\Suppliers\Pavon;

use App\Enums\ShadePriceCategory;
use DOMDocument;
use DOMElement;
use DOMXPath;

/**
 * Čte stránku kolekce z katalogu textilií Pavon (např. /textilie-standard).
 * Každá látka je <article class="product-item"> se skrytým blokem dat:
 * .title, .img (cesta k fotce v plném rozlišení) a tabulka .detail_params.
 * Výprodej = štítek „Výprodej“ nebo „Výprodej“ v cenové skupině.
 */
class PavonFabricParser
{
    /** Řádky tabulky, které do vlastností látky nepatří (máme je jinde, nebo jsou jen pro dodavatele). */
    private const array SKIPPED = ['Objednací číslo', 'Cenová skupina', 'Dostupnost', 'Použití'];

    /**
     * @return list<PavonFabric>
     */
    public function parse(string $html): array
    {
        $document = new DOMDocument;
        @$document->loadHTML('<?xml encoding="utf-8"?>'.$html, LIBXML_NOERROR | LIBXML_NOWARNING);
        $xpath = new DOMXPath($document);

        $fabrics = [];

        foreach ($xpath->query('//article[contains(concat(" ", normalize-space(@class), " "), " product-item ")]') as $article) {
            $fabric = $this->fabric($xpath, $article);

            if ($fabric !== null) {
                $fabrics[] = $fabric;
            }
        }

        return $fabrics;
    }

    private function fabric(DOMXPath $xpath, DOMElement $article): ?PavonFabric
    {
        $title = $this->text($xpath, './/div[contains(@class, "hidden")]/div[contains(@class, "title")]', $article);

        if ($title === '') {
            return null;
        }

        $rows = [];
        foreach ($xpath->query('.//table[contains(@class, "detail_params")]//tr', $article) as $row) {
            $label = rtrim($this->text($xpath, './th', $row), ': ');
            $value = $this->text($xpath, './td', $row);

            if ($label !== '' && $value !== '') {
                $rows[$label] = $value;
            }
        }

        $group = $rows['Cenová skupina'] ?? null;
        $badge = $this->text($xpath, './/span[contains(@class, "product-icos")]', $article);
        $image = $this->text($xpath, './/div[contains(@class, "hidden")]/div[contains(@class, "img")]', $article);

        $specs = [];
        foreach ($rows as $label => $value) {
            if (! in_array($label, self::SKIPPED, true)) {
                $specs[] = ['label' => $label, 'value' => $value];
            }
        }

        return new PavonFabric(
            code: $rows['Objednací číslo'] ?? $title,
            name: $title,
            priceCategory: ShadePriceCategory::fromSupplier($group),
            onSale: str_contains(mb_strtolower($badge.' '.$group), 'výprodej'),
            rollWidth: $this->rollWidth($rows['Šíře látky'] ?? null),
            specs: $specs,
            imageUrl: $image !== '' ? PavonCatalog::BASE_URL.'/'.ltrim($image, '/') : null,
        );
    }

    /**
     * „200 cm“ → 200; „200 cm, 240 cm“ → 240 (nejširší role).
     */
    private function rollWidth(?string $value): ?int
    {
        preg_match_all('/(\d+)\s*cm/u', (string) $value, $matches);

        return $matches[1] ? max(array_map('intval', $matches[1])) : null;
    }

    private function text(DOMXPath $xpath, string $query, DOMElement $context): string
    {
        $node = $xpath->query($query, $context)->item(0);

        return $node ? trim(preg_replace('/\s+/u', ' ', str_replace("\u{00A0}", ' ', $node->textContent))) : '';
    }
}
