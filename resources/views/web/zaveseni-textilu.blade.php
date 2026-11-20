{{-- Stránka zatím jen připravená; obsah majitel doplní později. --}}
<x-layouts.web
    title="Zavěšení textilu"
    description="Zavěšení záclon a závěsů, garnýže a kolejnice. Zaměříme a pověsíme tak, aby textil visel přesně."
>
    <section class="bg-paper">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:py-20">
            <p class="eyebrow">Montáž a zavěšení</p>
            <h1 class="text-section-title mt-2">Zavěšení textilu</h1>
            <p class="mt-3 max-w-2xl text-lg text-muted">
                Postaráme se, aby záclony a závěsy visely přesně tam, kde mají. Podrobnosti sem brzy doplníme — zatím nám napište, co potřebujete.
            </p>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('web.inquiry.create') }}" class="btn-primary">Nezávazná poptávka</a>
                <a href="{{ route('web.kalkulacka') }}" class="btn-secondary">Kalkulačka</a>
            </div>
        </div>
    </section>
</x-layouts.web>
