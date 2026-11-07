# Uživatelská příručka — Textil na míru

Průvodce aplikací pro **interiérová studia** (zadávání zakázek) a **dílnu** (zpracování
zakázek, ceník, uživatelé). Aplikace běží v prohlížeči, funguje i na mobilu.

---

## Přihlášení

Na přihlašovací stránce (adresa **/b2b**, stará adresa /login vás sem
přesměruje) zadejte **přihlašovací jméno** a **heslo**. Účty
zakládá výhradně dílna — pokud údaje nemáte, kontaktujte správce (telefon je
uvedený pod přihlašovacím formulářem).

Po přihlášení vás aplikace sama nasměruje: studio na *Moje zakázky*,
dílna na *Přehled zakázek*.

---

## Část pro interiérová studia

### Založení zakázky

1. Na stránce **Moje zakázky** napište název zakázky (např. „Novákovi –
   obývák") a klikněte na **Založit zakázku**.
2. Zakázka je teď *rozpracovaná* — vidíte ji jen vy, dílně se zobrazí až po
   závazném objednání. Rozpracovanou zakázku můžete kdykoli opustit a vrátit
   se k ní později (najdete ji v seznamu *Cenové nabídky*).

### Cenové nabídky

Každá rozpracovaná zakázka je zároveň cenová nabídka. V seznamu *Cenové
nabídky* vidíte její cenu bez DPH i s DPH, takže ji můžete rovnou předat
svému klientovi. Nabídku, kterou jste odložili (viz níže), poznáte podle
štítku *odložená*. Nabídku, která už není potřeba, smažete tlačítkem
**Smazat**.

### Přidání výrobku

1. V zakázce vyberte jednu ze šesti dlaždic: **Záclona, Závěs, Polštář, Sedák,
   Římská roleta, Vypínaný strop**.
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
- U polštáře zvolte stranu zipu, nebo **Bez zipu**. Zip se účtuje příplatkem,
  dražší je i ušití polštáře se **složitým tvarem**.
- U sedáku zadáváte šířku, délku a tloušťku. Molitan se počítá po vrstvách
  2 cm podle tloušťky, rouno podle plochy, úvazy jednou částkou.
- U **vypínaného stropu** zadáte šířku a délku místnosti, povrch a nepovinně
  délku LED pásku (m) a vzdálenost pro dopravu (km). Cena se skládá z plochy
  stropu, LED pásku a dopravy. Povrch cenu nemění.

### Závazné objednání

1. V zakázce klikněte na **Pokračovat k objednání**.
2. Zkontrolujte rekapitulaci položek, vypište **soupis dodaného materiálu**
   (typ, barva, metráž) a zvolte **datum požadované realizace**.
3. **Závazně objednat.** Od této chvíle je zakázka odeslaná dílně a **nelze ji
   měnit** — ceny jsou zamčené tak, jak byly při objednání. Pokud potřebujete
   změnu, domluvte se s dílnou telefonicky.

Stav zakázky sledujete v seznamu *Zakázky*: **Nová → Přijatá →
K fakturaci → Hotová**. Hotovou zakázku si můžete ze seznamu smazat.

### Odložení objednávky

Pokud ještě nechcete objednat závazně, klikněte na kroku s materiálem na
**Odložit objednávku**. Uloží se soupis materiálu a termín (termín můžete
nechat prázdný), zakázka se ale dílně neodešle. Zůstane mezi cenovými
nabídkami se štítkem *odložená* a kdykoli ji dokončíte.

### Profil

V **Profilu** si měníte heslo, telefon a adresu. Telefon a adresa se zobrazují
dílně u vašich zakázek — udržujte je aktuální. Přihlašovací jméno mění jen
správce.

---

## Část pro dílnu (admin)

### Přehled zakázek

Stránka **Zakázky** má čtyři sekce podle fáze: *Nové*, *Přijaté*,
*K fakturaci*, *Hotové*. Rozpracované zakázky (cenové nabídky) studií vidět nejsou — objeví
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
- Tlačítko **Výrobní list** otevře v novém okně stránku pro tisk: hlavička
  zakázky (studio, datum objednání, termín, dodaný materiál) a u každé
  položky parametry, poznámka a nákres. Vytisknete ji tlačítkem tisku.

### Ceník

Stránka **Ceník** má dvě části; **uložení změn ovlivní jen nové kalkulace,
už objednané zakázky zůstávají za původní ceny**:

1. **Úkony a položky** — systémové sazby (příprava, lemování, žehlení, DPH,
   vypínaný podhled za m², LED pásek za m, doprava za km…), každá s jednotkou.
   Přepište hodnoty a dejte *Uložit ceník*. Dole můžete přidat **novou
   položku** (název, jednotka, cena); kód se vytvoří z názvu. Smazat jde jen
   takto přidanou vlastní položku, systémové položky smazat nejde.
   Vámi upravené ceny zůstanou zachované i po aktualizaci aplikace.
2. **Řasící stuhy** — název, cena za metr a povolené poměry řasení (oddělené
   čárkou, např. „1.5, 2, 2.5"). Stuhu lze vypnout (*Aktivní*), pak ji
   studia nenabídnou.

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

**Studio chce změnit odeslanou zakázku.** Odeslané zakázky jsou záměrně
neměnné (zamčené ceny). Řešení: dílna zakázku smaže a studio ji zadá znovu,
nebo se změna domluví mimo aplikaci.

**Změnili jsme ceník — co staré zakázky?** Nic. Každá zakázka má ceny
zamrazené z okamžiku objednání. Nové kalkulace už jedou podle nového ceníku.

**Proč je sedák s rounem tak drahý?** Sazba „Rouno do sedáku 0,25cm2" se
násobí podle plochy sedáku. Při sazbě 38 Kč stojí rouno pro sedák 120 × 80 cm
14 592 Kč. Pokud to neodpovídá skutečnosti, upravte sazbu v *Ceníku*.

**Zapomenuté heslo.** Reset e-mailem není — heslo nastaví správce v sekci
*Uživatelé → Upravit*.
