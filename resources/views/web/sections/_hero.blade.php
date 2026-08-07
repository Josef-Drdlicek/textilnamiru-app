<section aria-labelledby="hero-nadpis" class="relative bg-surface-alt">
    <div class="aspect-[4/3] w-full sm:aspect-[16/9] lg:absolute lg:inset-0 lg:aspect-auto lg:h-full">
        <x-web.img
            name="hero"
            alt="Prosvětlený obývací pokoj s lněnými závěsy na míru"
            label="Fotografie interiéru se závěsy"
            width="2400"
            height="1350"
            :eager="true"
        />
    </div>

    <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:py-24">
        <div class="bg-white p-6 shadow-md sm:p-10 lg:max-w-xl">
            <p class="eyebrow">Rodinná dílna · od roku 1992</p>
            <h1 id="hero-nadpis" class="text-display mt-3">
                Interiérový textil šitý na&nbsp;míru
            </h1>
            <p class="mt-4 text-lg leading-relaxed text-muted">
                Zaměříme, ušijeme, pověsíme. Rodinná dílna s více než 30letou tradicí
                vytváří záclony, závěsy a stínicí techniku pro domácnosti, hotely i firmy.
            </p>
            <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                <a href="#kalkulacka" class="btn-primary">Spočítat orientační cenu</a>
                <a href="{{ route('web.inquiry.create') }}" class="btn-secondary">Nezávazná poptávka</a>
            </div>
        </div>
    </div>
</section>
