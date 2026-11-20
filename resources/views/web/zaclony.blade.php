{{-- Stránka zatím jen připravená; obsah majitel doplní později. --}}
<x-layouts.web
    title="Záclony"
    description="Záclony šité na míru ve vlastní dílně. Zaměříme, ušijeme a pověsíme."
>
    <x-web.back-link :href="route('web.zaclony-a-zavesy')" label="Zpět na záclony a závěsy" />

    <section class="bg-paper">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:py-20">
            <p class="eyebrow">Záclony a závěsy</p>
            <h1 class="text-section-title mt-2">Záclony</h1>
            <p class="mt-3 max-w-2xl text-lg text-muted">
                Záclony šijeme na míru ve vlastní dílně. Podrobnosti sem brzy doplníme — zatím nám napište, co potřebujete, nebo si spočítejte orientační cenu.
            </p>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('web.inquiry.create') }}" class="btn-primary">Nezávazná poptávka</a>
                <a href="{{ route('web.kalkulacka.sivani') }}" class="btn-secondary">Spočítat cenu ušití</a>
            </div>
        </div>
    </section>
</x-layouts.web>
