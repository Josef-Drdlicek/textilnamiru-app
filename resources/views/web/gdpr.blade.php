<x-layouts.web title="Ochrana osobních údajů" description="Informace o zpracování osobních údajů — Textil na míru, Boskovice.">
    <div class="mx-auto max-w-3xl px-4 py-14 sm:px-6 lg:py-20">
        <p class="eyebrow">Právní informace</p>
        <h1 class="text-section-title mt-2">Ochrana osobních údajů</h1>

        <div class="mt-8 space-y-8 leading-relaxed text-ink/85">
            <section>
                <h2 class="text-lg font-extrabold">Kdo údaje zpracovává</h2>
                <p class="mt-2">
                    Správcem osobních údajů je {{ config('company.name') }}, {{ config('company.street') }},
                    {{ config('company.zip') }} {{ config('company.city') }} (dále „my"). Kontakt:
                    <a href="mailto:{{ config('company.email') }}" class="font-medium text-brand-600 underline underline-offset-2">{{ config('company.email') }}</a>,
                    tel. {{ config('company.phone_display') }}.
                </p>
            </section>

            <section>
                <h2 class="text-lg font-extrabold">Jaké údaje a proč</h2>
                <p class="mt-2">
                    Z poptávkového formuláře zpracováváme jméno a příjmení, e-mail, telefon,
                    město a obsah vaší zprávy včetně případné konfigurace z kalkulačky.
                    Údaje používáme výhradně k vyřízení vaší poptávky — tedy abychom vás
                    mohli kontaktovat, připravit nabídku a domluvit zaměření.
                </p>
            </section>

            <section>
                <h2 class="text-lg font-extrabold">Jak dlouho údaje držíme</h2>
                <p class="mt-2">
                    Poptávky uchováváme po dobu jednání o zakázce, nejdéle 2 roky od posledního
                    kontaktu. Pokud z poptávky vznikne zakázka, uchováváme údaje po dobu
                    vyžadovanou účetními a daňovými předpisy.
                </p>
            </section>

            <section>
                <h2 class="text-lg font-extrabold">Komu údaje předáváme</h2>
                <p class="mt-2">
                    Nikomu je neprodáváme ani nepředáváme pro marketing třetích stran.
                    Údaje jsou uloženy u našeho poskytovatele hostingu v Evropské unii.
                </p>
            </section>

            <section>
                <h2 class="text-lg font-extrabold">Vaše práva</h2>
                <p class="mt-2">
                    Máte právo na přístup ke svým údajům, jejich opravu či výmaz, omezení
                    zpracování a právo vznést námitku. Stačí napsat na
                    <a href="mailto:{{ config('company.email') }}" class="font-medium text-brand-600 underline underline-offset-2">{{ config('company.email') }}</a>.
                    Se stížností se můžete obrátit na Úřad pro ochranu osobních údajů (uoou.gov.cz).
                </p>
            </section>

            <section>
                <h2 class="text-lg font-extrabold">Cookies</h2>
                <p class="mt-2">
                    Web používá pouze technická cookies nezbytná pro fungování formulářů
                    (relace, ochrana proti zneužití). Žádná analytická ani marketingová
                    cookies nenasazujeme.
                </p>
            </section>
        </div>

        <p class="mt-10 text-sm text-muted">Poslední aktualizace: {{ \Illuminate\Support\Carbon::parse(config('company.privacy_updated_at'))->format('j. n. Y') }}</p>
    </div>
</x-layouts.web>
