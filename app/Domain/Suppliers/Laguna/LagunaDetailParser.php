<?php

namespace App\Domain\Suppliers\Laguna;

use DOMDocument;
use DOMXPath;

/**
 * Parametry z detailu produktu Laguna Textil (blok „.infos“: popisek + hodnota)
 * převedené do češtiny pro náš web.
 */
final class LagunaDetailParser
{
    /** Zkratky a maďarské/anglické názvy vláken => česky. Delší výrazy dřív než kratší. */
    private const array FIBRES = [
        'RE-PES' => 'recyklovaný polyester',
        'RE PES' => 'recyklovaný polyester',
        'RE-PET' => 'recyklovaný PET',
        'POLIÉSZTER' => 'polyester',
        'POIÉSZTER' => 'polyester',
        'POLYESTER' => 'polyester',
        'VISZKÓZ' => 'viskóza',
        'VISCOSE' => 'viskóza',
        'LINEN' => 'len',
        'PAMUT' => 'bavlna',
        'COTTON' => 'bavlna',
        'ACRYLIC' => 'akryl',
        'AKRIL' => 'akryl',
        'TENCEL' => 'tencel',
        'BAMBOO' => 'bambus',
        'LUREX' => 'lurex',
        'SILK' => 'hedvábí',
        'PES' => 'polyester',
        'PAN' => 'akryl',
        'ACR' => 'akryl',
        'VIS' => 'viskóza',
        'LEN' => 'len',
        'CO' => 'bavlna',
        'WO' => 'vlna',
        'LI' => 'len',
        'VI' => 'viskóza',
    ];

    /** Vlastnosti z pole „Special“. */
    private const array FEATURES = [
        'NEHOŘLAVÁ' => 'nehořlavá',
        'EXTRA SIZE' => 'extra šíře',
        'VITRÁŽKA' => 'vitrážka',
        'SNADNÉ ŽEHLENÍ' => 'snadné žehlení',
        'UV OCHRANA' => 'UV ochrana',
        'VYŠÍVANÁ' => 'vyšívaná',
        'RECYKLOVANÁ' => 'recyklovaná',
        'BLACK OUT' => 'zatemňující (blackout)',
        'DIM OUT' => 'částečně zatemňující (dim out)',
        'WATERPROOF' => 'voděodolná',
    ];

    /**
     * @return array{height: ?int, material: ?string, specs: list<array{0: string, 1: string}>}
     */
    public function parse(string $html): array
    {
        $fields = $this->fields($html);
        $specs = [];

        if ($weight = $fields['JEDNOTKOVÁ HMOTNOST'] ?? null) {
            $specs[] = ['Gramáž', str_replace('g/m2', 'g/m²', $weight)];
        }
        if ($repeat = $fields['RAPORT SVISLÝ'] ?? null) {
            $specs[] = ['Raport svislý', $repeat];
        }
        if ($repeat = $fields['RAPORT VODOROVNÝ'] ?? null) {
            $specs[] = ['Raport vodorovný', $repeat];
        }
        if ($lead = $fields['OLOVĚNÁ ŠŇŮRA'] ?? null) {
            $specs[] = ['Olověná šňůra', mb_strtoupper($lead) === 'ANO' ? 'ano, už z výroby' : 'ne'];
        }
        if ($special = $fields['SPECIAL'] ?? null) {
            $specs[] = ['Vlastnosti', $this->features($special)];
        }

        return [
            'height' => preg_match_all('/\d+/', $fields['ROZMĚR'] ?? '', $m) ? (int) end($m[0]) : null,
            'material' => isset($fields['MATERIÁL']) ? $this->material($fields['MATERIÁL']) : null,
            'specs' => $specs,
        ];
    }

    /**
     * „56%VISZKÓZ 44%POLIÉSZTER“ => „56 % viskóza, 44 % polyester“.
     */
    public function material(string $raw): string
    {
        // Nejdřív „100% PES (60% RE-PES 40% PES)“ – závorka je rozpis, stačí hlavní údaj.
        $raw = trim(preg_replace('/\(.*\)/', '', $raw));

        preg_match_all('/(\d+(?:[.,]\d+)?)\s*%\s*([^\d,]+)/u', $raw, $parts, PREG_SET_ORDER);

        if ($parts === []) {
            return $this->fibre($raw);
        }

        return implode(', ', array_map(
            fn (array $part) => $part[1].' % '.$this->fibre($part[2]),
            $parts,
        ));
    }

    private function fibre(string $name): string
    {
        $key = mb_strtoupper(trim($name, " ,\t"));

        // „POLIÉSZTER FR“ = nehořlavé vlákno.
        if (str_ends_with($key, ' FR')) {
            return $this->fibre(substr($key, 0, -3)).' (nehořlavý)';
        }

        return self::FIBRES[$key] ?? mb_strtolower(trim($name, " ,\t"));
    }

    private function features(string $raw): string
    {
        return implode(', ', array_map(
            fn (string $feature) => self::FEATURES[mb_strtoupper(trim($feature))] ?? mb_strtolower(trim($feature)),
            explode(',', $raw),
        ));
    }

    /**
     * @return array<string, string> POPISEK => hodnota (jen textové hodnoty)
     */
    private function fields(string $html): array
    {
        $dom = new DOMDocument;
        @$dom->loadHTML('<?xml encoding="utf-8"?>'.$html);
        $xpath = new DOMXPath($dom);
        $fields = [];

        foreach ($xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " infos ")]//label') as $label) {
            $value = $label->nextSibling;
            while ($value && $value->nodeType !== XML_ELEMENT_NODE) {
                $value = $value->nextSibling;
            }

            $text = $value ? trim(preg_replace('/\s+/u', ' ', $value->textContent)) : '';

            if ($text !== '') {
                $fields[mb_strtoupper(trim($label->textContent))] = $text;
            }
        }

        return $fields;
    }
}
