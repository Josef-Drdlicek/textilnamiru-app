# Pokyny pro AI agenty (Cursor, Claude Code, Codex)

Tohle je jedna Laravel aplikace: veřejný web, kalkulačky, B2B zakázky interiérových studií
a administrace šicí dílny. Jediný zdroj pravdy jsou databáze a server, žádná data v prohlížeči.

Podrobná pravidla jsou v `.cursor/rules/`:

- `00-projekt.mdc` – co se nikdy nedělá, workflow, lokální běh (platí vždy)
- `backend-laravel.mdc` – vrstvy, migrace, routy, testy, checklist nového výrobku
- `pricing.mdc` – ceny a vzorce
- `frontend-web.mdc` – Blade, Tailwind, obrázky, přístupnost
- `obsah-a-fotky.mdc` – kde se mění texty, fotky a ceník
- `clean-code.mdc` – clean code, SOLID, žádné natvrdo psané hodnoty (platí vždy)
- `tym-specialistu.mdc` – nová funkce jde přes tým specialistů; role jsou v `.cursor/agents/`
  (architekt, backend, frontend-ux, tester, reviewer)

Než začneš: přečti `README.md` (architektura) a pravidlo odpovídající části kódu, na které pracuješ.

## Pět věcí, které agent nesmí

1. Založit vedlejší aplikaci nebo ukládat data do `localStorage`.
2. Počítat ceny mimo `app/Domain/Pricing`, nebo psát ceny, kontakty a hesla natvrdo.
3. Nechat v kódu `DOPLNIT`, inline `style`/`<script>` v Blade, `!important`.
4. Mazat či oslabovat testy; commitovat bez `vendor/bin/pest`.
5. Pushovat do `main` (= produkce). Větev + pull request.

## Když si nejsi jistý

Obchodní pravidlo (vzorec, workflow, terminologie) si nech potvrdit majitelem. Otevřené otázky
jsou v `docs/design-audit-2026-11.md` a `README.md`.
