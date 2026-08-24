# Uživatelská příručka — Textil na míru

Průvodce aplikací pro **designéry** (zadávání zakázek) a **dílnu** (zpracování
zakázek, ceník, uživatelé). Aplikace běží v prohlížeči, funguje i na mobilu.

---

## Přihlášení

Na přihlašovací stránce zadejte **přihlašovací jméno** a **heslo**. Účty
zakládá výhradně dílna — pokud údaje nemáte, kontaktujte správce (telefon je
uvedený pod přihlašovacím formulářem).

Po přihlášení vás aplikace sama nasměruje: designér na *Moje zakázky*,
dílna na *Přehled zakázek*.

---

## Část pro designéry

### Založení zakázky

1. Na stránce **Moje zakázky** napište název zakázky (např. „Novákovi –
   obývák") a klikněte na **Založit zakázku**.
2. Zakázka je teď *rozpracovaná* — vidíte ji jen vy, dílně se zobrazí až po
   závazném objednání. Rozpracovanou zakázku můžete kdykoli opustit a vrátit
   se k ní později (najdete ji v seznamu *Rozpracované zakázky*).

### Přidání výrobku

1. V zakázce vyberte jednu z pěti dlaždic: **Záclona, Závěs, Polštář, Sedák,
   Římská roleta**.
2. Vyplňte rozměry a parametry. **Cena se přepočítává průběžně** — v pravém
   panelu vidíte cenu za kus, s DPH i celkem za všechny kusy. Kliknutím na
   *Z čeho se cena skládá* si rozbalíte podrobný rozpis.
3. Vedle ceny se kreslí **náhled s rozměry** — zkontrolujte, že odpovídá tomu,
   co chcete ušít.
4. Do pole **Materiál** napište název látky, kterou dodáte (např. „voal
   bílý") — dílna podle něj sčítá spotřebu. **Poznámka** je pro cokoliv
   dalšího, co má dílna vědět.
5. **Přidat do zakázky.** Položek můžete přidat kolik chcete; každou lze do
   objednání upravit (*Upravit*) nebo odebrat (*Odebrat*).

Tipy k formulářům:

- Dokud nejsou vyplněné všechny povinné údaje, cena se nezobrazuje — chybějící
  pole jsou označena červeně.
- U záclon a závěsů nabídne aplikace jen **poměry řasení povolené pro zvolenou
  stuhu**; u „Vlastní" stuhy zadáváte poměr ručně.
- Provedení **Zrcadlově** vyžaduje sudý počet kusů — aplikace to pohlídá.

### Závazné objednání

1. V zakázce klikněte na **Pokračovat k objednání**.
2. Zkontrolujte rekapitulaci položek, vypište **soupis dodaného materiálu**
   (typ, barva, metráž) a zvolte **datum požadované realizace**.
3. **Závazně objednat.** Od této chvíle je zakázka odeslaná dílně a **nelze ji
   měnit** — ceny jsou zamčené tak, jak byly při objednání. Pokud potřebujete
   změnu, domluvte se s dílnou telefonicky.

Stav zakázky sledujete v seznamu *Odeslané zakázky*: **Nová → Přijatá →
K fakturaci → Hotová**.

### Profil

V **Profilu** si měníte heslo, telefon a adresu. Telefon a adresa se zobrazují
dílně u vašich zakázek — udržujte je aktuální. Přihlašovací jméno mění jen
správce.

---

## Část pro dílnu (admin)

### Přehled zakázek

Stránka **Zakázky** má čtyři sekce podle fáze: *Nové*, *Přijaté*,
*K fakturaci*, *Hotové*. Rozpracované zakázky designérů vidět nejsou — objeví
se až po závazném objednání jako *Nové*. Filtrovat lze podle data objednání
a podle zákazníka (nabídka obsahuje jen ty, kdo už nějakou zakázku odeslali);
oba filtry lze kombinovat.

Zakázku posouváte tlačítky **Přijmout → Fakturace → Uzavřít** (v přehledu i
v detailu). Každý posun se zapisuje do historie — v detailu vidíte kdo a kdy.
**Smazat** lze zakázku v kterékoli fázi (po potvrzení).

### Detail zakázky

- **Kontakt na zákazníka** (telefon, adresa z jeho profilu) a termín realizace.
- **Cena** — zamrazená v okamžiku objednání; pozdější změny ceníku ji nemění.
- **Spotřeba látky** — automatický součet podle názvu materiálu (např.
  „voal: 12,4 m") přes všechny položky zakázky.
- **Soupis dodaného materiálu** — text od zákazníka.
- U každé položky: všechny parametry, poznámka, **rozpad ceny** po úkonech
  a **technický nákres** (kliknutím se otevře velký, dá se vytisknout).

### Ceník

Stránka **Ceník** má tři části; **uložení změn ovlivní jen nové kalkulace,
už objednané zakázky zůstávají za původní ceny**:

1. **Úkony a položky** — 33 sazeb (příprava, lemování, žehlení, DPH…).
   Přepište hodnoty a dejte *Uložit ceník*.
2. **Řasící stuhy** — název, cena za metr a povolené poměry řasení (oddělené
   čárkou, např. „1.5, 2, 2.5"). Stuhu lze vypnout (*Aktivní*), pak ji
   designéři nenabídnou.
3. **Cenová tabulka rolet** — mřížka cen podle pásem šířka × výška.

### Uživatelé

- **+ Nový uživatel** — vyplňte jméno, přihlašovací jméno a heslo (předejte
  ho uživateli bezpečnou cestou; heslo si pak může změnit v profilu).
- Zaškrtnutí **Účet dílny (admin)** dává plný přístup — používejte jen pro
  dílnu.
- Účty se **nemažou, ale deaktivují** — deaktivovaný uživatel se nepřihlásí,
  jeho zakázky ale zůstávají v evidenci. Deaktivaci lze kdykoli vrátit.
- Vlastní účet deaktivovat nejde (pojistka, abyste se nezamkli).

---

## Časté dotazy

**Designér chce změnit odeslanou zakázku.** Odeslané zakázky jsou záměrně
neměnné (zamčené ceny). Řešení: dílna zakázku smaže a designér ji zadá znovu,
nebo se změna domluví mimo aplikaci.

**Změnili jsme ceník — co staré zakázky?** Nic. Každá zakázka má ceny
zamrazené z okamžiku objednání. Nové kalkulace už jedou podle nového ceníku.

**Proč sedák stojí stejně jako polštář stejných rozměrů?** Aplikace záměrně
přebírá výpočet z původního systému (hloubka do ceny nevstupuje). Až dílna
potvrdí správný vzorec, bude upraven.

**Zapomenuté heslo.** Reset e-mailem není — heslo nastaví správce v sekci
*Uživatelé → Upravit*.
