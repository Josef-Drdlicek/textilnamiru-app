{{-- Rozcestník kalkulaček — tři velké dlaždice, bez samotného výpočtu. --}}
<x-layouts.web
    title="Kalkulačka"
    description="Orientační kalkulačka zakázkového šití, vypínaných stropů a stínicí techniky."
>
    <section class="bg-paper">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:py-20">
            <h1 class="text-section-title">Kalkulačka</h1>

            <div class="mt-10 grid grid-cols-1 gap-6 md:grid-cols-3">
                <a
                    href="{{ route('web.kalkulacka.sivani') }}"
                    class="group flex flex-col overflow-hidden rounded-lg border-2 border-line bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-ink/40 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2"
                >
                    <div class="flex aspect-[4/3] items-center justify-center bg-white text-sm font-medium text-muted">
                        DOPLNIT OBRÁZEK
                    </div>
                    <div class="border-t border-line px-5 py-6">
                        <h2 class="text-xl font-extrabold">Zakázkové šití</h2>
                    </div>
                </a>

                <a
                    href="{{ route('web.kalkulacka.stropy') }}"
                    class="group flex flex-col overflow-hidden rounded-lg border-2 border-line bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-ink/40 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2"
                >
                    <div class="flex aspect-[4/3] items-center justify-center bg-white text-sm font-medium text-muted">
                        DOPLNIT OBRÁZEK
                    </div>
                    <div class="border-t border-line px-5 py-6">
                        <h2 class="text-xl font-extrabold">Vypínané stropy</h2>
                    </div>
                </a>

                <a
                    href="{{ route('web.kalkulacka.stinici') }}"
                    class="group flex flex-col overflow-hidden rounded-lg border-2 border-line bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-ink/40 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2"
                >
                    <div class="flex aspect-[4/3] items-center justify-center bg-white text-sm font-medium text-muted">
                        DOPLNIT OBRÁZEK
                    </div>
                    <div class="border-t border-line px-5 py-6">
                        <h2 class="text-xl font-extrabold">Stínící technika</h2>
                    </div>
                </a>
            </div>
        </div>
    </section>
</x-layouts.web>
