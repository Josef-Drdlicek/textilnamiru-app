# Jak pracovat na projektu v Cursoru

Návod pro majitele a jeho AI agenta. Cíl: každá úprava jde přes stejná pravidla, stejné testy
a stejný postup nasazení, ať ji píše člověk, nebo agent.

## 1. Jednorázová příprava (5 minut)

1. V Cursoru otevři složku projektu (`textilnamiru`), ne nadřazenou složku.
2. Stáhni aktuální stav: v terminálu Cursoru `git fetch && git checkout main && git pull`.
3. Nainstaluj a spusť:
   ```
   composer install
   npm install
   copy .env.example .env
   php artisan key:generate
   php artisan migrate --force
   php artisan db:seed --class=DemoSeeder
   npm run build
   php artisan serve
   ```
   Web běží na http://127.0.0.1:8000, přihlášení pro studia na /b2b
   (lokální demo účty: `dilna` / `demo-heslo-dilna`, `lucie` / `demo-heslo-lucie`).
4. Zkontroluj, že Cursor vidí pravidla: Settings → Rules → Project Rules musí ukazovat
   `00-projekt`, `backend-laravel`, `pricing`, `frontend-web`, `obsah-a-fotky`.
   Soubor `AGENTS.md` v kořeni čte Cursor automaticky.

## 2. Jak zadávat práci agentovi

Před každým úkolem vlož agentovi tento úvod (stačí jednou na začátku konverzace):

> Pracuješ v projektu Textil na míru. Řiď se `AGENTS.md` a pravidly v `.cursor/rules/`.
> Před změnou si přečti pravidlo pro danou část kódu a najdi existující vzor, který zkopíruješ.
> Nezakládej žádnou vedlejší aplikaci, nic neukládej do prohlížeče, ceny počítá jen server.
> Pracuj ve větvi, ne v `main`. Po změně spusť `vendor\bin\pest` a `npm run build`.
> Když si nejsi jistý obchodním pravidlem, zeptej se mě místo odhadu.
> Na konci mi napiš, co jsi změnil, které testy jsi přidal a co mám zkontrolovat v prohlížeči.

Pak zadej konkrétní úkol. Dobré zadání říká **co** a **kde to uživatel uvidí**, ne jak to naprogramovat:

- „Na stránce Stínicí technika změň úvodní odstavec na: …“
- „Přidej do galerie realizací fotku z hotelu v Brně, originál je v resources/images/gallery-brno.jpg.“
- „Do ceníku přidej položku Montáž stropu 350 Kč/hod a ať ji počítá kalkulátor stropu jako hodiny × sazba.“

Špatné zadání: „Udělej mi novou aplikaci na zakázky“ nebo „ulož to do prohlížeče“. Agent to má
zakázané a měl by se ohradit.

## 2b. Když vymýšlíš něco nového

Nová stránka, nový výrobek, nové pole, jiný vzorec: agent nesmí začít programovat rovnou.
Podle pravidla `tym-specialistu` nejdřív pustí **architekta**, který vrátí plán a otázky. Ty plán
schválíš (nebo doplníš odpovědi), pak teprve jedou **backend → frontend → tester → reviewer**.
Prompty všech rolí jsou v `.cursor/agents/`. Když Cursor subagenty neumí, otevři pro roli nový chat
a vlož obsah jejího souboru jako první zprávu, pak zadání a plán.

Chceš to spustit? Napiš: „Postupuj podle tym-specialistu. Zadání: …“

## 3. Postup pro každou změnu (agent ho zná, ty ho hlídáš)

1. `git checkout -b nazev-zmeny` (z aktuálního `main`).
2. Změna + test.
3. `vendor\bin\pest` zelené, `npm run build` bez chyby.
4. Proklikat v prohlížeči na telefonu i počítači (nebo zúžit okno na 390 px).
5. `git add -A && git commit -m "Česká věta, co se změnilo"`.
6. `git push -u origin nazev-zmeny` a na GitHubu otevřít pull request do `main`.
7. Merge do `main` = **nasazení na produkci** (Laravel Cloud buildí automaticky). Proto se
   do `main` nepushuje přímo.

## 4. Nejčastější úkoly

| Chci | Postup |
|---|---|
| Změnit text | Najdi soubor v tabulce v `.cursor/rules/obsah-a-fotky.mdc`, uprav hodnotu v poli. |
| Vyměnit nebo přidat fotku | Originál do `resources/images/` pod správným názvem, `npm run images`, commit. |
| Upravit cenu | V administraci (Ceník), ne v kódu. |
| Nový výrobek do kalkulačky | Checklist „nový typ výrobku“ v `.cursor/rules/backend-laravel.mdc`. |
| Nová stránka | Route `web.*`, metoda v `PageController`, Blade v `resources/views/web/`, položka v menu,
  patičce a sitemap, feature test v `tests/Feature/Web/PublicPagesTest.php`. |

## 5. Když agent něco rozbije

- Testy padají: nech agenta opravit kód, ne test. Pokud tvrdí, že test je špatně, chtěj vysvětlení.
- Nejde to zpět: `git checkout -- .` zahodí necommitnuté změny; `git log` a `git revert <hash>`
  vrátí commit. Nikdy `git push --force` do `main`.
- Produkce padá po merge: na Laravel Cloud vrať předchozí deploy a napiš vývojáři.

## 6. Co nechat vývojáři

Změny migrací už nasazených na produkci, změny přihlašování a oprávnění, platby, zálohy databáze,
nastavení Laravel Cloud a domény.
