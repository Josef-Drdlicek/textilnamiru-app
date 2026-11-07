# Textil na míru — zakázková aplikace

Interní webová aplikace zakázkové šicí dílny. Odběratelé (interiérová studia)
si nakonfigurují výrobky na míru (záclona, závěs, polštář, sedák, římská roleta,
vypínaný strop), vidí **okamžitou cenu počítanou na serveru**, uloží si zakázku
jako cenovou nabídku nebo ji závazně objednají. Dílna zakázky přijímá, posouvá
stavovým workflow, tiskne výrobní listy, spravuje ceník a uživatele.

Rebuild původní aplikace `zakazka.textilnamiru.cz` podle reverzně-inženýrského
zadání `../ZADANI-textilnamiru.md`.

📖 **Návod pro uživatele (dílna + interiérová studia):** [docs/uzivatelska-prirucka.md](docs/uzivatelska-prirucka.md)

## Stack

- PHP 8.3, Laravel 13, Livewire 4 (živý přepočet cen), Tailwind CSS 4 + Vite
- MySQL/MariaDB v produkci, SQLite pro lokální vývoj a testy
- Pest (golden testy cenových vzorců, workflow, autorizace, Livewire, veřejný web)

## Architektura v kostce

| Oblast | Kde |
|---|---|
| Cenové vzorce (strategy per typ výrobku) | `app/Domain/Pricing/Calculators/` |
| Ceník (proměnné + stuhy) | `app/Domain/Pricing/PriceList.php`, DB tabulky `pricing_variables`, `ribbons` |
| Typované parametry položek (JSON + DTO) | `app/Domain/Pricing/Parameters/`, `app/Casts/ItemParametersCast.php` |
| SVG nákresy (náhled i trvalý nákres) | `app/Domain/Schema/Renderers/` |
| Stavové workflow + audit | `app/Enums/OrderStatus.php`, `app/Actions/TransitionOrderStatus.php`, tabulka `order_status_changes` |
| Konfigurátory (živý přepočet) | `app/Livewire/Configurators/` |
| Use-case akce | `app/Actions/` (SaveOrderItem, SubmitOrder, DeferOrder, …) |
| Výrobní list (tisk pro dílnu) | `app/Http/Controllers/Admin/OrderSheetController.php`, route `admin.orders.sheet` |
| Autorizace (anti-IDOR) | `app/Policies/` |

### Klíčové vlastnosti (opravy nálezů ze zadání §11)

- **Cena se počítá výhradně na serveru** — klient žádnou cenu neposílá; živý
  náhled v konfigurátoru je tatáž serverová kalkulace přes Livewire.
- **Zamrazení cen:** při uložení položky se ukládá cena, spotřeba i rozpad ceny;
  při závazném objednání se zamrazí celková cena a sazba DPH. Pozdější změny
  ceníku odeslané zakázky neovlivní.
- **Ceny úkonů a stuh jsou v DB** (editovatelné dílnou), ne v kódu.
- Stavy zakázky jako enum s povolenými přechody + audit (kdo/kdy).
- Změnové akce přes POST/PUT/DELETE s CSRF; vlastnictví hlídají Policies.
- Hesla bcrypt; účty zakládá výhradně dílna; deaktivace místo mazání.
- Desetinné rozměry se počítají přesně (oprava `parseInt` bugu).

### Vědomě replikované podivnosti (čekají na potvrzení dílnou, zadání §13)

- `raport`, spodní záložka a strana provedení do ceny nevstupují.
- Nepoužívané ceníkové položky (SIKMINA, …) jsou v ceníku ponechány.
- E-mailové notifikace zatím nejsou; event `App\Events\OrderSubmitted` je
  připravený — stačí přidat listener.

### Cenové vzorce (stanovil majitel dílny)

Kódy v závorkách jsou položky ceníku.

- **Záclona a závěs, kanýr:** plocha kanýru v m² (šířka × výška kanýru) × `OBNITKOVANI`.
- **Závěs, zalomení:** zalomení × šířka v m × `NADSITI_ZACLONA`. Sešití dílů
  se účtuje jen tehdy, když je zadaná šířka materiálu (větší než 0).
- **Polštář:** k základu a ceně dle rozměru se přičítá ušití `USITI_POLSTAR`
  (u „Složitý tvar" `USITI_POLSTAR_C`) a při zvoleném zipu příplatek
  `USITI_ZALOZKA`. Volba „Bez zipu" příplatek nemá.
- **Sedák** (`CushionPriceCalculator`): ušití `USITI_SEDAK` + příprava podle
  plochy (m² × `PRIPRAVA`) + molitan (m² × počet vrstev po 2 cm tloušťky ×
  `MOLITAN_2CM`) + rouno + úvazy `USITI_UVAZ`. Pozor: `ROUNO_025CM2` je cena
  za 100 jednotek po 0,25 cm², takže rouno pro sedák 120 × 80 cm stojí při
  sazbě 38 Kč 14 592 Kč. Výši určuje dílna sazbou v ceníku.
- **Římská roleta:** `KOMPLET_ROLETA` + šířka v m × `TUNYLKY_ROLETA` + výška
  v m × `PRIPRAVA` + počet tunýlků × `USITI_ZALOZKA`. Tabulka cenových pásem
  rolet (`blind_price_bands`) byla zrušena.
- **Vypínaný strop** (`StretchCeilingPriceCalculator`): plocha v m² ×
  `VYPINANY_PODHLED` + LED pásek v m × `LED_PASEK` + doprava v km × `DOPRAVA`.
  Povrch (matný, lesklý, …) cenu neovlivňuje.

### Ceník

Položky `pricing_variables` mají jednotku (`unit`) a příznak `is_system`.
Systémové položky zakládá seeder (včetně `VYPINANY_PODHLED` 1500 Kč/m²,
`LED_PASEK` 400 Kč/m, `DOPRAVA` 12 Kč/km) a jejich hodnoty upravené dílnou
nikdy nepřepisuje. Dílna si může přidat vlastní položky (kód se vytvoří
z názvu) a smazat smí jen tyto vlastní položky, systémové ne.

### Veřejný web

Veřejná kalkulačka je rozdělená podle kategorie: `/kalkulacka/zakazkove-sivani`
(šití) a `/kalkulacka/vypinane-stropy` (stropy). Stránky stínicí techniky zatím
nemají obsah, proto mají `noindex` a nejsou v sitemapě.

## Práce s AI agentem (Cursor)

Pravidla pro agenty jsou v `AGENTS.md` a `.cursor/rules/`, návod pro majitele v `docs/CURSOR-START.md`.
Fotky se připravují skriptem `npm run images` z originálů v `resources/images/`.

## Lokální vývoj

```bash
composer install
npm install
copy .env.example .env        # SQLite je výchozí
php artisan key:generate
php artisan migrate --seed    # ceník (36 systémových položek, 8 stuh)
php artisan db:seed --class=DemoSeeder   # demo účty + ukázková zakázka (jen local)
npm run dev                   # nebo: npm run build
php -S 127.0.0.1:8000 -t public server.php
```

> Pozn. pro Windows: pokud cesta k projektu obsahuje diakritiku, `php artisan
> serve` selže (PHP mrší UTF-8 cesty v argumentech podprocesů) — proto lokální
> router `server.php` výše. Na cestě bez diakritiky funguje i `php artisan serve`.

Demo účty (jen po `DemoSeeder`, pouze local): `dilna` / `demo-heslo-dilna`
(admin), `lucie` / `demo-heslo-lucie` (interiérové studio). Přihlášení je na
`/b2b` (`/login` trvale přesměruje tam). Po přihlášení jde admin na přehled
zakázek dílny, studio na `/orders`.

### Testy

```bash
vendor/bin/pest
```

> Pozn. pro Windows: používejte `vendor\bin\pest` přímo — `php artisan test`
> na cestě s diakritikou selhává (známý problém předávání argumentů PHPUnit).

## Nasazení na Laravel Cloud (auto-deploy z GitHubu)

Repo je připravené (trust proxies, health endpoint `/up`, idempotentní seed).
Jednorázové nastavení na [cloud.laravel.com](https://cloud.laravel.com):

1. **Sign in with GitHub** a povol přístup k repu `Josef-Drdlicek/textilnamiru-app`.
2. **New application** → vyber repo, větev `main`, region EU (Frankfurt).
3. V nastavení aplikace:
   - **Build commands:** výchozí composer install + přidej `npm ci && npm run build`
   - **Deploy commands:** `php artisan migrate --force --seed`
     (seed jen doplní chybějící položky ceníku, ceny upravené dílnou nepřepíše)
   - **Environment:** `APP_LOCALE=cs`, `APP_FAKER_LOCALE=cs_CZ`
4. **Database** → vytvoř databázi a připoj ji k aplikaci (DB_* proměnné se
   doplní samy; migrace jsou přenositelné, funguje Postgres i MySQL).
5. **Deploy.** Od té chvíle každý `git push` do `main` spustí build a nasazení.
6. První účet dílny: v Cloud UI → **Commands** →
   `php artisan app:create-admin dilna --name="Šicí dílna" --password=…`

Session i cache jdou do databáze (výchozí Laravel 13 konfigurace), takže
aplikace nefunguje jen na jedné instanci a nepotřebuje lokální disk.

## Nasazení (klasický hosting)

1. PHP 8.3+ s rozšířeními `pdo_mysql`, `mbstring`; `gd` netřeba (nákresy jsou SVG).
2. `.env`: `APP_ENV=production`, `APP_DEBUG=false`, `DB_CONNECTION=mysql` + přístupy, `APP_URL`.
3. `composer install --no-dev --optimize-autoloader && npm ci && npm run build`
4. `php artisan migrate --seed --force` (seed je idempotentní, ceny upravené dílnou nepřepíše)
5. `php artisan config:cache && php artisan route:cache && php artisan view:cache`
6. První účet dílny: `php artisan app:create-admin dilna --name="Šicí dílna"`
7. Web root míří do `public/`.

## Stavové workflow zakázky

```
Rozpracovaná (draft) ──závazně objednat──▶ Nová ──Přijmout──▶ Přijatá
   ──Fakturace──▶ K fakturaci ──Uzavřít──▶ Hotová (terminál)
```

Draft vidí jen studio, v aplikaci se mu zobrazuje jako **cenová nabídka**
(cena bez DPH i s DPH). Admin vidí zakázky až od stavu Nová. Na kroku materiálu
lze draft **odložit** (`orders.defer`): uloží se materiál a případný termín,
nastaví se `orders.deferred_at`, zakázka se ale neodešle. Mazání (soft delete):
studio smí smazat svůj draft a svou hotovou zakázku, dílna smaže zakázku
v libovolné fázi.
