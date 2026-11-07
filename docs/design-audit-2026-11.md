# Audit designu a UX webu i aplikace (7. 11. 2026)

Podklad: screenshoty všech veřejných stránek na 390 / 1024 / 1280 / 1440 / 1920 px, měření obrázků v prohlížeči (puppeteer), přihlášená část pro studio i dílnu, a řádkový audit Blade/JS/CSS. Zásada: Lucienovy nápady zůstávají (slider se 3 snímky, rozdělené stránky, stránka podhledu s galerií, tlačítko B2B, obrázkové logo, pořadí menu). Opravuje se provedení.

Legenda: **V** = viditelné pro návštěvníka nebo rozbité, **S** = porušuje brandbook / UX zvyklost / výkon, **N** = kvalita kódu. Náročnost: S = do hodiny, M = půl dne, L = den a víc.

---

## 1. Obrázky (hlavní příčina „rozmazaného“ dojmu)

| Co | Stav | Fix | |
|---|---|---|---|
| Hero fotky `hero*.webp` | 2400×1350 px, ale jen **33 / 100 / 131 KB** → přetlačená komprese, na 1920 px rozmazané. Bez `srcset`, mobil stahuje 2400 px verzi. Hero na desktopu má poměr ~2 : 1, fotka 16 : 9 se ořezává; snímek 2 s `object-top` ukazuje jen holý strop. | Re-export z originálů v kvalitě ~80, tři šířky (960 / 1600 / 2400), `srcset` + `sizes="100vw"` v `x-web.img`. Snímek 2 ořez na `object-center` nebo vybrat fotku s obsahem. | V, M |
| Galerie podhledu `01–05.png` | PNG 260–560 KB (celkem 1,9 MB), 3 z 5 mají jen 502×420 px a roztahují se na ~600 px → rozmazané. Fotky mají **vypálený text** („PODSVÍCENÝ STROP“, oranžová stuha, „TEXTIL NA MÍRU“), který se bije s počítadlem a tečkami. | WebP/JPG 1200 px, q80 (~70 KB/ks), čisté fotky bez textu; popisek dát do `<figcaption>` pod fotku. Přes `x-web.img`. | V, S–M |
| Logo `logo.jpg` | 1024×1024 px, **201 KB** na 36 px ikonu, na každé stránce; bílé rohy řeší patička kruhovým ořezem inline stylem. | SVG, nebo PNG/WebP s průhledností 72 + 144 px (`srcset` 1x/2x), pod 10 KB. Zrušit inline ořez. | V, S |
| Katalog a realizace | Všech 8 dlaždic sortimentu i 6 fotek realizací jsou dosud šrafované placeholdery. Nesouvisí s Lucienem, ale kazí dojem „hotovo“. | Dodat fotky podle `docs/IMAGE-PROMPTS.md`. | V, mimo kód |
| Předčasné stahování | `web.js` hned po načtení stáhne všechny snímky hera včetně JPG fallbacků (~800 KB navíc). | Smazat hromadný `preloadSlide`, přednačítat jen následující snímek a jen `currentSrc`. | S, S |

## 2. Hero slider

- **CTA stejné pro všechny snímky (V, S).** U snímku „Vypínaný stropní podhled“ vede „Spočítat orientační cenu“ na kalkulačku šití. Fix: do `$slides` přidat `cta` (label + route) a renderovat per snímek; podhled → `web.kalkulacka.stropy`.
- **Text snímku 3 je kopie snímku 1, snímek 2 má jedno souvětí bez tečky (V, S).** Doplnit krátký text o stínění a druhou větu u podhledu.
- **Autoplay bez pauzy a bez ohledu na `prefers-reduced-motion` (V, M).** Po ručním kliknutí se znovu rozjede. Porušuje WCAG 2.2.2. Fix: reduced-motion → bez autoplay; po interakci vypnout; pauza při hover/fokusu od začátku; malé tlačítko pauza vedle teček.
- **Tečky 10×10 px (S).** Pod minimem 24 px dotykové plochy. Fix: `h-2.5 w-2.5` ponechat vizuálně, ale klikací plocha `p-2` nebo `min-h-6 min-w-6`.
- **`aria-hidden` se přepíná i na bloku s `<h1>` (S).** Na snímku 2 a 3 nemá stránka pro čtečku h1. Fix: textové kopie přepínat přes `inert`/třídu, h1 nechat přístupný.
- **Šipky na desktopu: „předchozí“ sedí pod bílou kartou, „další“ na opačné straně (N).** Dát obě vedle sebe vpravo dole nebo na svislý střed okrajů.
- **Duplicitní slider (N, M).** Hero (360 ř. ve `web.js`) a galerie podhledu (inline `<script>` v Blade) implementují totéž dvakrát, s jiným chováním (galerie má klávesy, hero ne). Fix: `resources/js/slider.js` s `createSlider(root, {autoplay, effect})`, obojí ho použije.

## 3. Hlavička a patička

- **Menu se na 1024–1279 px láme do dvou řádků (V, S).** Ověřeno: hlavička 89 px, položky „Vypínaný / podhled“, „O / nás“. Sedm položek + pill + logo s podtitulkem + B2B se nevejdou. Fix: hamburger až do `xl`, nebo zkrátit (podtitulek pryč, `lg:gap-4`, položky `text-sm`, v menu „Poptávka“).
- **Hlavička ztratila `mx-auto max-w-7xl` (V, S).** Na širokých monitorech nelícuje s obsahem a patičkou. Vrátit kontejner, inline `padding-top: 13.8px` → `py-3.5`.
- **Podtitulek „Dáváme interiérům nový rozměr“ 11 px, na mobilu uříznutý (S).** Skrýt do `sm`, nebo z hlavičky odstranit (claim je v hero a patičce).
- **B2B jako outline pill konkuruje primárnímu CTA (S).** Dvě tlačítka vedle sebe pro dvě různé skupiny; styl přes `!px-4 !py-2 !bg-ink`. Fix: B2B jako textová položka menu (na mobilu v rozbaleném menu), aktivní stav `aria-current`; pokud má zůstat pill, zavést `.btn-sm` v `app.css` místo `!important`.
- **Breakpoint nesoulad `md:p-0` vs. `lg:` menu (S).** Na tabletech 768–1023 px jsou položky v rozbaleném menu bez paddingu (klik pod 44 px). Fix: `md:` → `lg:`.
- **Patička „Rychlé odkazy“ nezná nové stránky (S).** Doplnit Vypínaný podhled (a stínění, až bude).
- **Kontakty natvrdo na 4 místech (N).** JSON-LD, patička, GDPR, poptávka. Fix: `config/company.php` plněný z `.env`.
- Drobnosti (N): aktivní položka bez `aria-current`; mobilní menu nezavírá Esc; `alt` loga duplikuje `aria-label` odkazu; `web/b2b.blade.php` je mrtvá kopie `auth/login`.

## 4. Stránky

### Home
- **Dva teasery jsou jen nadpis + tlačítko se stejným textem (V, S).** „Vypínaný podhled“ a „Stínicí technika“ nenesou informaci; pět teaserů za sebou je monotónní, dva sousední mají stejné pozadí. Fix (menší): doplnit jednu větu a odlišné CTA, střídat pozadí. Fix (lepší, M): sloučit první tři teasery do jedné sekce „Co pro vás uděláme“ se 3 kartami (fotka, název, věta, odkaz).
- **Rozbité kotvy po rozdělení stránek (V, S).** Poptávka „Upravit“ vede na `home#kalkulacka`, děkovačka na `home#realizace`; obě skončí nahoře na home. Fix: `web.kalkulacka.sivani`, `web.realizace`.

### Vypínaný podhled
- **Žádné CTA (V, S).** Hlavní novinka bez tlačítka na kalkulačku stropů ani poptávku. Fix: za úvod a na konec `btn-primary` → `web.kalkulacka.stropy`, `btn-secondary` → poptávka.
- **Terminologie 3× jinak (V, S).** Menu „Vypínaný podhled“, hero „Vypínaný stropní podhled“, h1 a title „Napínané stropy“, kalkulačka „Vypínané stropy“, výrobek „Vypínaný strop“. Uživatel klikne na jedno a přistane na jiném. Fix: Lucien zvolí hlavní termín (SEO silnější „napínané stropy“), druhý jen jako eyebrow.
- **Čtyři h2 ve velikosti h1 v úzkém sloupci (S).** `text-section-title` je pro nadpisy přes celou šířku. Fix: h2 → `text-2xl font-extrabold`, přidat eyebrow nad h1.
- **Inline `<style>` a `<script>` v Blade (N).** Obchází Vite, duplikuje slider. Přesunout do `app.css` / sdíleného slideru; `style="list-style…"` → `list-disc pl-5`.
- Generický `alt` „fotka 1 z 5“, `width/height` neodpovídají skutečným rozměrům (N).

### Stínicí technika
- **Veřejná stránka s textem „DOPLNIT OBSAH“ 4×, druhá položka menu a teaser na home (V, S).** `noindex` nebrání lidem kliknout. Fix: buď odkaz z menu i home dočasně vyndat, nebo stránku naplnit minimem: 2 věty, 3 dlaždice z katalogu (žaluzie, garnýže, japonské stěny) a CTA na poptávku. Druhá varianta je rychlá a stránka je rovnou použitelná.

### Kalkulačka
- **Třetí karta hubu vede na maketu plnou „DOPLNIT NÁZEV“ (V, S).** `components/web/kalkulacka-vizual.blade.php` (172 ř., inline JS). Fix: kartu skrýt nebo s badge „Připravujeme“ odkázat na poptávku; maketu smazat.
- **Karty hubu porušují brandbook (S).** `rounded-lg shadow-sm` na obrazové dlaždici; brandbook říká radius 0, stín jen při hoveru. Chybí eyebrow, úvodní věta a popis karet. Karty používají 2400px hero fotky jako 800px dlaždice.
- **Lišta „Zpět na výběr kalkulace“ 3× zkopírovaná (N).** Komponenta `back-link`. Title stránky šití je jen „Kalkulačka“ (stejně jako hub).

### Realizace
- **Chybí `<h1>` (V, S).** `_gallery` ignoruje `$asPage`. Jednořádkový fix.

### Ostatní
- GDPR: „Poslední aktualizace: {{ now() }}“ ukazuje vždy dnešek (N).
- Login: chybové hlášky bez `role="alert"`, `text-red-600` vs. `red-700` jinde (N).

## 5. CSS / Tailwind
- `!important` utility v hlavičce (`!px-4 !py-2 !bg-ink`) → `.btn-sm` a `aria-current` (S).
- Inline styly: hlavička, patička (ořez loga), podhled (list-style, `<style>`) (S).
- Hero CSS mimo `@layer`, trvalé `will-change: transform` na 6 prvcích (N).
- Chyby v `text-red-600/700` místo tokenu; brandbook má `#c62828` → `--color-error` (N).
- Arbitrary `object-[center]`/`object-[center_top]` = `object-center`/`object-top` (N).

## 6. Interní aplikace (studio a dílna)
Celkově v pořádku: prázdné stavy, confirm dialogy, chyby pod poli, `overflow-x-auto` na tabulkách. Skutečné problémy:
- **Hlavička na mobilu se láme („Textil na / míru“, „Odhlásit / se“) (V, S).** `flex` bez `flex-wrap` a bez hamburgeru. Fix: `flex-wrap`, nebo ikona menu pod `sm`.
- **Cenový panel konfigurátoru je na mobilu až pod tlačítkem „Přidat“ (S).** Studio při zadávání rozměrů nevidí cenu. Fix: na mobilu sticky lišta s cenou dole (nebo panel před formulářem). Panel navíc nemá `aria-live`.
- **Nápovědy a prázdné stavy `text-stone-400` = kontrast 2,6 : 1 (S).** Hromadně → `text-stone-500`.
- Tabulky 5–7 sloupců na telefonu scrollují bez indikace; sloupec Akce mimo obrazovku (N). Pro interní desktopovou aplikaci přijatelné; `whitespace-nowrap` + sticky první sloupec by pomohl.
- Chyba `values.*` v ceníku se ukáže pod celou tabulkou, ne u pole (N).

---

## Plán refaktoru v pořadí

### Fáze 1: viditelné chyby (celkem ~1 den)
1. Stínicí technika: naplnit minimem nebo vyndat z menu a home. (S)
2. Hub kalkulačky: skrýt kartu stínění, smazat maketu `kalkulacka-vizual`. (S)
3. CTA na stránku podhledu. (S)
4. `<h1>` na Realizace; opravit kotvy `#kalkulacka`/`#realizace`. (S)
5. Hlavička: vrátit `max-w-7xl`, `py-3.5`, `md:` → `lg:`, hamburger do `xl`, podtitulek skrýt. (S)
6. Logo: SVG/PNG s průhledností, zrušit inline ořez. (S)
7. Galerie podhledu: WebP bez vypáleného textu, správné rozměry, popisné alt, `figcaption`. (S–M, potřebuje originály fotek od Luciena)
8. Hero: re-export fotek q80 ve 3 šířkách + `srcset` v `x-web.img`; zrušit hromadný preload; CTA per snímek; texty snímků 2 a 3. (M)

### Fáze 2: UX a přístupnost (~1 den)
9. Autoplay: reduced-motion, vypnout po interakci, tlačítko pauza, h1 bez `aria-hidden`, větší dotyková plocha teček.
10. B2B jako položka menu, `.btn-sm` místo `!important`, `aria-current`, Esc zavírá menu.
11. Home: teasery s textem a odlišným CTA, střídání pozadí (nebo sloučení do jedné sekce se 3 kartami).
12. Terminologie podhled/napínané stropy sjednotit (po rozhodnutí Luciena).
13. Interní app: `flex-wrap` v hlavičce, sticky cena v konfigurátoru na mobilu, `text-stone-400` → `500`, `aria-live`.

### Fáze 3: kód (~1 den)
14. Sdílený `slider.js` pro hero i galerii; inline `<style>/<script>` z Blade pryč; hero CSS do `@layer`.
15. `config/company.php` pro kontakty; `back-link` komponenta; smazat `web/b2b.blade.php`, `og.webp`; `--color-error` token.
16. Hub kalkulačky podle brandbooku (radius 0, eyebrow, popisy karet, dlaždice 800 px).

## Otázky na Luciena
- Hlavní termín: „vypínaný podhled“, nebo „napínané stropy“?
- Má originály fotek stropů bez vypáleného textu (nebo zdroj, odkud jsou)? Originály hero fotek v plné kvalitě?
- Logo ve vektoru (AI/SVG/PDF)?
- Obsah pro stínicí techniku: alespoň 2 věty a seznam produktů. Do té doby stránku skrýt, nebo ukázat „připravujeme“?
- Má B2B zůstat jako výrazné tlačítko (studia přistupují přes web), nebo stačí odkaz v menu/patičce?
