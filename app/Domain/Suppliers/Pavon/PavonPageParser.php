<?php

namespace App\Domain\Suppliers\Pavon;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

/**
 * Čte produktovou stránku Pavon: záložka „Základní informace“ (podnadpis, slogan,
 * odstavce), „Technické informace“ (nadpisy, odrážky, texty), rozměry a fotky
 * (horní galerie a záložka Galerie, v plném rozlišení).
 */
final class PavonPageParser
{
    public function parse(string $html): PavonPage
    {
        $dom = new DOMDocument;
        @$dom->loadHTML('<?xml encoding="utf-8"?>'.$html);
        $xpath = new DOMXPath($dom);

        $basic = $xpath->query('//*[@id="zakladni-informace"]')->item(0);
        $technical = $xpath->query('//*[@id="technicke-informace"]//*[contains(@class, "indiv_popis")]')->item(0)
            ?? $xpath->query('//*[@id="technicke-informace"]')->item(0);

        $blocks = $technical instanceof DOMElement ? $this->technicalBlocks($technical) : [];
        $listText = implode("\n", array_merge(...array_map(fn (array $b) => $b['items'] ?? [$b['text'] ?? ''], $blocks ?: [[]])));

        return new PavonPage(
            subtitle: $this->text($xpath->query('.//h2', $basic)->item(0)),
            slogan: $this->slogan($xpath, $basic),
            paragraphs: $this->paragraphs($xpath, $basic),
            technical: $blocks,
            minWidth: $this->dimension($listText, 'minimální šířka'),
            maxWidth: $this->dimension($listText, 'maximální šířka'),
            minHeight: $this->dimension($listText, 'minimální (?:výška|délka)'),
            maxHeight: $this->dimension($listText, 'maximální (?:výška|délka)'),
            images: $this->images($xpath),
        );
    }

    private function slogan(DOMXPath $xpath, ?DOMNode $basic): ?string
    {
        $slogan = $this->text($xpath->query('.//em', $basic)->item(0));

        return $slogan !== null ? trim($slogan, " »«\u{00A0}") : null;
    }

    /**
     * Odstavce popisu bez sloganu, ceny „od“ a odkazů na katalog dodavatele.
     *
     * @return list<string>
     */
    private function paragraphs(DOMXPath $xpath, ?DOMNode $basic): array
    {
        $paragraphs = [];

        foreach ($xpath->query('.//*[contains(@class, "product-detail-text")]/p | .//*[contains(@class, "product-detail-text")]/div', $basic) as $node) {
            $text = $this->text($node);

            if ($text === null || mb_strlen($text) < 30 || preg_match('/Ceny již od|KATALOG|»/u', $text)) {
                continue;
            }

            $paragraphs[] = $text;
        }

        return $paragraphs;
    }

    /**
     * @return list<array{type: string, text?: string, items?: list<string>}>
     */
    private function technicalBlocks(DOMElement $root): array
    {
        $blocks = [];

        foreach ($root->childNodes as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($node->tagName);

            if (in_array($tag, ['h3', 'h4'], true)) {
                if ($text = $this->text($node)) {
                    $blocks[] = ['type' => 'heading', 'text' => rtrim($text, ':')];
                }
            } elseif (in_array($tag, ['ul', 'ol'], true)) {
                $items = $this->listItems($node);
                if ($items !== []) {
                    $blocks[] = ['type' => 'list', 'items' => $items];
                }
            } elseif ($tag === 'table') {
                // Umístění rolety: „A) na rám okna“, „B) na stěnu“… (obrázky vynecháme).
                $cells = [];
                foreach ($node->getElementsByTagName('td') as $td) {
                    if ($text = $this->text($td)) {
                        $cells[] = $text;
                    }
                }
                if ($cells !== []) {
                    $blocks[] = ['type' => 'list', 'items' => $cells];
                }
            } elseif (in_array($tag, ['p', 'div'], true)) {
                if (($text = $this->text($node)) && mb_strlen($text) > 1) {
                    $blocks[] = ['type' => 'text', 'text' => $text];
                }
            }
        }

        return $blocks;
    }

    /**
     * Odrážky včetně vnořených (vnořené dostanou pomlčku).
     *
     * @return list<string>
     */
    private function listItems(DOMElement $list, string $prefix = ''): array
    {
        $items = [];

        foreach ($list->childNodes as $child) {
            if (! $child instanceof DOMElement) {
                continue;
            }

            if (in_array(strtolower($child->tagName), ['ul', 'ol'], true)) {
                array_push($items, ...$this->listItems($child, '– '));

                continue;
            }

            // Text položky bez vnořeného seznamu.
            $clone = $child->cloneNode(true);
            foreach (iterator_to_array($clone->getElementsByTagName('ul')) as $nested) {
                $nested->parentNode->removeChild($nested);
            }
            if ($text = $this->text($clone)) {
                $items[] = $prefix.$text;
            }

            foreach ($child->getElementsByTagName('ul') as $nested) {
                if ($nested->parentNode === $child) {
                    array_push($items, ...$this->listItems($nested, '– '));
                }
            }
        }

        return $items;
    }

    /**
     * @return list<array{url: string, alt: string}>
     */
    private function images(DOMXPath $xpath): array
    {
        $images = [];
        $nodes = $xpath->query('//img[contains(concat(" ", normalize-space(@class), " "), " w100 ")] | //*[@id="galerie"]//img');

        foreach ($nodes as $img) {
            $src = $img->getAttribute('src') ?: $img->getAttribute('data-src');
            if (! str_contains($src, '/upload/')) {
                continue;
            }

            $url = $this->absolute(preg_replace('/_(?:s|m|v)(\.(?:jpe?g|png|webp))$/i', '$1', $src));
            $images[$url] ??= ['url' => $url, 'alt' => trim($img->getAttribute('alt'))];
        }

        return array_values($images);
    }

    private function dimension(string $text, string $label): ?int
    {
        return preg_match('/'.$label.'[^:]*:\s*(\d+)\s*cm/iu', $text, $m) ? (int) $m[1] : null;
    }

    private function absolute(string $url): string
    {
        if (str_starts_with($url, 'http')) {
            return $url;
        }

        return PavonCatalog::BASE_URL.'/'.ltrim(preg_replace('~^(\.\./|\./)+~', '', $url), '/');
    }

    private function text(?DOMNode $node): ?string
    {
        if ($node === null) {
            return null;
        }

        $text = trim(preg_replace('/[\s\x{00A0}]+/u', ' ', $node->textContent));

        return $text === '' ? null : $text;
    }
}
