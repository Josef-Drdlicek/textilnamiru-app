# Textil na míru — zakázková aplikace

Interní webová aplikace zakázkové šicí dílny. Odběratelé (interiéroví designéři)
si nakonfigurují výrobky na míru (záclona, závěs, polštář, sedák, římská roleta),
vidí **okamžitou cenu počítanou na serveru** a závazně objednají. Dílna zakázky
přijímá, posouvá stavovým workflow, spravuje ceník a uživatele.

Rebuild původní aplikace `zakazka.textilnamiru.cz` podle reverzně-inženýrského
zadání `../ZADANI-textilnamiru.md`.

📖 **Návod pro uživatele (dílna + designéři):** [docs/uzivatelska-prirucka.md](docs/uzivatelska-prirucka.md)

## Stack

- PHP 8.3, Laravel 13, Livewire 4 (živý přepočet cen), Tailwind CSS 4 + Vite
- MySQL/MariaDB v produkci, SQLite pro lokální vývoj a testy
- Pest (124 testů: golden testy cenových vzorců, workflow, autorizace, Livewire)

## Architektura v kostce

| Oblast | Kde |
|---|---|
| Cenové vzorce (strategy per typ výrobku) | `app/Domain/Pricing/Calculators/` |
| Ceník (proměnné + stuhy + tabulka rolet) | `app/Domain/Pricing/PriceList.php`, DB tabulky `pricing_variables`, `ribbons`, `blind_price_bands` |
| Typované parametry položek (JSON + DTO) | `app/Domain/Pricing/Parameters/`, `app/Casts/ItemParametersCast.php` |
| SVG nákresy (náhled i trvalý nákres) | `app/Domain/Schema/Renderers/` |
| Stavové workflow + audit | `app/Enums/OrderStatus.php`, `app/Actions/TransitionOrderStatus.php`, tabulka `order_status_changes` |
| Konfigurátory (živý přepočet) | `app/Livewire/Configurators/` |
| Use-case akce | `app/Actions/` (SaveOrderItem, SubmitOrder, …) |
| Autorizace (anti-IDOR) | `app/Policies/` |

### Klíčové vlastnosti (opravy nálezů ze zadání §11)

- **Cena se počítá výhradně na serveru** — klient žádnou cenu neposílá; živý
  náhled v konfigurátoru je tatáž serverová kalkulace přes Livewire.
- **Zamrazení cen:** při uložení položky se ukládá cena, spotřeba i rozpad ceny;
  při závazném objednání se zamrazí celková cena a sazba DPH. Pozdější změny
  ceníku odeslané zakázky neovlivní.
- **Ceny stuh a tabulka rolet jsou v DB** (editovatelné dílnou), ne v kódu.
- Stavy zakázky jako enum s povolenými přechody + audit (kdo/kdy).
- Změnové akce přes POST/PUT/DELETE s CSRF; vlastnictví hlídají Policies.
- Hesla bcrypt; účty zakládá výhradně dílna; deaktivace místo mazání.
- Desetinné rozměry se počítají přesně (oprava `parseInt` bugu).

### Vědomě replikované podivnosti (čekají na potvrzení dílnou, zadání §13)

- **Sedák se cení jako polštář** (ignoruje hloubku i sedákové položky ceníku)
  — `CushionPriceCalculator` deleguje na polštář; oprava = úprava jedné třídy,
  golden test tuto podivnost zamyká a při změně vědomě spadne.
- `raport`, `kanyr`, spodní záložka a strana provedení do ceny nevstupují.
- Nepoužívané ceníkové položky (OBNITKOVANI, SIKMINA, …) jsou v ceníku ponechány.
- E-mailové notifikace zatím nejsou; event `App\Events\OrderSubmitted` je
  připravený — stačí přidat listener.

## Lokální vývoj

```bash
composer install
npm install
copy .env.example .env        # SQLite je výchozí
php artisan key:generate
php artisan migrate --seed    # ceník (33 proměnných, 8 stuh, 30 pásem rolet)
php artisan db:seed --class=DemoSeeder   # demo účty + ukázková zakázka (jen local)
npm run dev                   # nebo: npm run build
php -S 127.0.0.1:8000 -t public server.php
```

> Pozn. pro Windows: pokud cesta k projektu obsahuje diakritiku, `php artisan
> serve` selže (PHP mrší UTF-8 cesty v argumentech podprocesů) — proto lokální
> router `server.php` výše. Na cestě bez diakritiky funguje i `php artisan serve`.

Demo účty (jen po `DemoSeeder`, pouze local): `dilna` / `demo-heslo-dilna`
(admin), `lucie` / `demo-heslo-lucie` (designérka).

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
     (seed jen doplní ceník — je idempotentní, existující data nemění)
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
4. `php artisan migrate --seed --force` (seed je idempotentní — jen doplní ceník)
5. `php artisan config:cache && php artisan route:cache && php artisan view:cache`
6. První účet dílny: `php artisan app:create-admin dilna --name="Šicí dílna"`
7. Web root míří do `public/`.

## Stavové workflow zakázky

```
Rozpracovaná (draft) ──závazně objednat──▶ Nová ──Přijmout──▶ Přijatá
   ──Fakturace──▶ K fakturaci ──Uzavřít──▶ Hotová (terminál)
```

Draft vidí jen designér; admin vidí zakázky až od stavu Nová. Zrušení: designér
smí zahodit svůj draft, dílna smaže (soft delete) zakázku v libovolné fázi.
