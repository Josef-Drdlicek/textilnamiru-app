# Veřejná stránka Kalkulačka — popis kódu

Balíček je kopie kódu z projektu `APP` (Laravel 11). Originály v projektu se neměnily.

## Jak se stránka načítá

1. GET `/kalkulacka` (název routy `web.kalkulacka`) v `routes/web.php`.
2. `App\Http\Controllers\Web\PageController::kalkulacka()` vrátí view `web.kalkulacka`.
3. Šablona `resources/views/web/kalkulacka.blade.php` obalí obsah layoutem a vloží sekci:
   `@include('web.sections._calculator', ['asPage' => true])`.
4. `_calculator.blade.php` vykreslí nadpis a Livewire komponentu `<livewire:web.price-estimator />`.
5. `App\Livewire\Web\PriceEstimator` renderuje `resources/views/livewire/web/price-estimator.blade.php`.

Volitelný query parametr `?typ=` (`curtain`, `drape`, `pillow`, `cushion`, `blind`) předvybere typ v `PriceEstimator::mount()`.

Stejná sekce `_calculator` se používá i na úvodu; na samostatné stránce je `$asPage = true` (nadpis `h1`).

## Layout, CSS a JavaScript

- **Layout:** `<x-layouts.web>` → `resources/views/components/layouts/web.blade.php`  
  (hlavička, patička a společný vzhled **nejsou** v tomto balíčku).
- **Vlastní CSS kalkulačky:** žádné. Třídy jsou Tailwind z `resources/css/app.css` (načtený layoutem).
- **Vlastní JS kalkulačky:** žádné. Cena se počítá na serveru (Livewire), ne v prohlížeči.
- **`@vite` v layoutu:** `@vite(['resources/css/app.css', 'resources/js/web.js'])`.  
  `web.js` je JS veřejného webu (např. slider), ne logika kalkulačky.
- **Livewire** si přidává vlastní skripty/styly přes layout (DOPLNIT přesné místo v layoutu, pokud se má citovat).
- **Ikony typů:** SVG v `resources/views/components/item-type-icon.blade.php` (žádné bitmapy).
- **Obrázky kalkulačky:** žádné. Hero/OG fotky webu kalkulačka nepoužívá.

## Závislosti

- Laravel + Blade + Livewire (`PriceEstimator`).
- Enum `App\Enums\ItemType` (popisky a výběr typu; soubor je sdílený i se zakázkami).
- `App\Domain\Pricing\PublicPresets` — laický vstup (cm, nařasení, výplň) doplní o odborné defaulty (stuha, šířka role, zip, …).
- `App\Domain\Pricing\ItemPricer` + kalkulátory + ceník z databáze (**nesdílené JS**; viz níže).
- Proměnné Livewire: `type`, `width`, `height`, `count`, `ruffle`, `stuffing`; výstupy `unitPriceVat`, `totalPrice`, `totalPriceVat`, `priceLines`.
- Tlačítko „Poptat…“ ukládá snapshot do session `inquiry.configuration` a přesměruje na `web.inquiry.create` (stránka poptávky **není** v balíčku).

Soubor `js/core/pricing.js` ani `js/calculators/` v tomto projektu **neexistují**.

## Jak se počítá cena (stručně)

1. Zákazník zadá typ, šířku/výšku v cm, počet kusů, u záclon/závěsů nařasení, u polštáře/sedáku výplň.
2. `PublicPresets::estimatorParameters()` z toho udělá plné parametry šití (zbytek je default: např. záclona šířka role 300 cm, závěs 140 cm, stuha `siroky70`).
3. `ItemPricer` vybere PHP kalkulátor podle `ItemType` a spočítá cenu nad aktuálním ceníkem (`PriceList` z DB, sazba DPH z ceníku).
4. Na webu se ukáže součet **včetně DPH** za zadaný počet kusů a rozpis řádků **za 1 kus bez DPH**.
5. Cena je orientační za **ušití**, ne za látku, dopravu ani montáž. Chybějící položka v ceníku cenu nevykreslí (`DomainException`).

Katalogová „cena od“ na sortimentu používá stejné `PublicPresets::referenceParameters()` + `ItemPricer`, ale **není** součástí této stránky.

## Sdílená kalkulace se zakázkami (nekopírováno)

Stejný PHP engine jako konfigurátory zakázek. Do balíčku **nepatří**:

- `app/Domain/Pricing/ItemPricer.php`
- `app/Domain/Pricing/PriceList.php` (čte ceník z DB — reálná data se neexportují)
- `app/Domain/Pricing/PriceCalculator.php`, `PriceResult.php`, `PriceLine.php`
- `app/Domain/Pricing/Calculators/CurtainPriceCalculator.php`
- `app/Domain/Pricing/Calculators/DrapePriceCalculator.php`
- `app/Domain/Pricing/Calculators/PillowPriceCalculator.php`
- `app/Domain/Pricing/Calculators/CushionPriceCalculator.php`
- `app/Domain/Pricing/Calculators/BlindPriceCalculator.php`
- `app/Domain/Pricing/Parameters/*.php`
- `app/Domain/Pricing/Exceptions/*.php`
- Livewire konfigurátory zakázek: `app/Livewire/Configurators/`, `resources/views/livewire/configurators/`

`PublicPresets.php` a `ItemType.php` jsou v balíčku, protože je veřejná kalkulačka používá přímo (mapování vstupu a UI). Enum zároveň odkazuje na kalkulátory a konfigurátory zakázek.

## Co mohlo zůstat mimo

- Celý `routes/web.php` (v `kalkulacka-kod.txt` je jen výřez jedné routy).
- `resources/views/components/layouts/web.blade.php` a CSS/JS z Vite.
- `tests/Feature/Web/PriceEstimatorTest.php` a GET `/kalkulacka` v `tests/Feature/Web/PublicPagesTest.php`.
- Migrace / seedy ceníku, Eloquent modely položek ceníku (DOPLNIT přesné názvy tabulek, pokud je budete potřebovat).
- Stránka poptávky a session `inquiry.configuration`.
- `composer.json` / `package.json` (verze Livewire, Tailwind, Vite).
- Registrace Livewire (konvence `web.price-estimator` → `App\Livewire\Web\PriceEstimator`).
- Odkazy z katalogu na `/kalkulacka?typ=…` (dlaždice sortimentu, ne samotná kalkulačka).

Hesla, `.env` a data zákazníků se do balíčku nedávají.
