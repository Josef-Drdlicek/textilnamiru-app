# Textil na míru — brandbook v2

*Platí pro veřejný web i interní aplikaci. Nahrazuje starý `textil-na-miru-web/BRANDBOOK.md`.*

## Příběh značky

Rodinná šicí dílna v Boskovicích. Tradice sahá k pradědečkovi **Františku Mlatečkovi**, krejčímu z 30. let 20. století — jeho dochovaný reklamní plakát je vizuální kotvou značky a zdrojem firemní barvy. V roce 1992 založili **Bohuslav a Marta Mlatečkovi** dílnu na bytový textil, dnes ji vede **Lucien Škárka**. Positioning: **„Zaměříme, ušijeme, pověsíme."**

## Barvy

Terracotta je odvozená z oranžového kruhu na plakátu ESKA. Logika použití je „ikeovská": **primární akce jsou tmavé (ink), terracotta je vzácný akcent** — čím míň jí je, tím víc vynikne.

| Token | Hex | Použití |
|---|---|---|
| `brand-50` | `#fbf3ee` | tónovaná pozadí, hover řádků |
| `brand-100` | `#f6e3d7` | chipy („Na poptávku"), badge |
| `brand-300` | `#e0a17c` | dekorativní čísla, linky |
| `brand-400` | `#d97a4a` | hover světlých akcentů |
| `brand-500` | `#c45c26` | **identitní barva** — ikony, velké tučné akcenty. Kontrast na bílé 4,3:1 → jen velký/tučný text a grafika |
| `brand-600` | `#a84b1f` | textové odkazy, terracotta tlačítka s bílým textem (AA, 6,0:1) |
| `brand-700` | `#8a3d1a` | hover/active `brand-600` |
| `brand-900` | `#4a2110` | tmavé detaily |
| `ink` | `#141B1E` | text, nadpisy, **primární CTA**, footer |
| `ink-800` | `#2a3439` | hover primárního CTA |
| `paper` | `#faf9f7` | základní pozadí webu (teplá off-white) |
| `surface-alt` | `#f2efe9` | střídající teplé sekce |
| `line` | `#e7e2da` | bordery |
| `muted` | `#5f5b55` | sekundární text (AA, 6,5:1) |

Sémantické barvy: success `#2e7d32`, error `#c62828`. Interní aplikace dál používá `teal`/`stone` utility — bez konfliktu.

## Typografie

**Jediný font: Figtree** (přes Vite/bunny, self-hosted). Geometrický humanistický sans blízký IKEA Noto — kulaté tečky, otevřené tvary, plná čeština, teplejší kresba než Inter.

| Role | Váha | Velikost |
|---|---|---|
| Běžný text | 400 | 1rem / 1.6 |
| Labely, navigace | 500 | 0.875–1rem |
| Podtitulky, tlačítka | 600 | 1–1.25rem |
| Nadpisy, ceny | **800** | `.text-display` `clamp(2.5rem, 5.5vw, 4.25rem)`, `.text-section-title` `clamp(1.75rem, 3vw, 2.5rem)`, `.text-price-xl` `clamp(2rem, 4vw, 3rem)` `tabular-nums` |

**Žádný serif.** Tradici nesou: historický plakát, letopočet „1992" a uppercase eyebrow labely (`.eyebrow` — 600, tracking 0.08em, `brand-600`). *(Pokud by klient serif vyžadoval: jediné povolené místo je citace v sekci O nás, Source Serif 4 italic.)*

## Tvary a stíny

IKEA kombinace „ostrá fotka × měkké CTA":

- **Obrazové dlaždice a karty: radius 0** (ostré rohy, katalog)
- **Inputy: 8px** (`rounded-lg`)
- **Tlačítka: pill** (`rounded-full`) — `.btn-primary` (ink), `.btn-secondary` (outline)
- Flat design — hierarchii dělají pozadí (`paper`/`surface-alt`/bílá) a bordery; stín jen sticky header a hover lift dlaždic (`-translate-y-0.5` + `shadow-md`)

## Ikonografie a logo

- Ikony: stroke `currentColor`, stroke-width 2, zaoblené konce, viewBox 48 — styl `x-item-type-icon`
- Logo na webu: **wordmark** „**Textil** na míru" (Figtree 800 + 400) + zjednodušený emblém šicího stroje; plný kruhový emblém s opisem = heritage varianta (footer, O nás)

## Obrazový styl

Fotorealistické interiéry: měkké denní světlo, teplé off-white stěny (`#faf9f7`), dubové podlahy, akcenty v terracottě a uhlové, přírodní len a bavlna s ostrým detailem tkaniny, oční úroveň, 35 mm, bez lidí, bez textu. Kompletní prompty: `docs/IMAGE-PROMPTS.md`.

## Tón komunikace

- **Vykání**, krátké věty, slovesa v 1. os. mn. č. („Zaměříme, ušijeme, pověsíme.")
- Čísla jako důkaz: 30+ let, 1000+ klientů, 4 země, 900 hotelových pokojů v Ammánu
- Žádné superlativy bez podkladu; cena vždy s kontextem („orientační cena za ušití, bez látky")

## Implementace

Tokeny: `resources/css/app.css` (`@theme` → utility `bg-brand-600`, `text-ink`, `bg-paper`, `border-line`, …). Komponentové třídy: `.btn-primary`, `.btn-secondary`, `.eyebrow`, `.text-display`, `.text-section-title`, `.text-price-xl`. Font: `vite.config.js` (`bunny('Figtree')`) + `@fonts` v layoutech.
