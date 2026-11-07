{{-- Rozcestník kalkulaček podle oboru. Obrázky sdílí s hero sliderem. --}}
@php
    $calculators = [
        ['route' => 'web.kalkulacka.sivani', 'title' => 'Zakázkové šití', 'img' => 'hero', 'alt' => 'Interiérový textil šitý na míru'],
        ['route' => 'web.kalkulacka.stropy', 'title' => 'Vypínané stropy', 'img' => 'hero-2', 'alt' => 'Vypínaný stropní podhled'],
        ['route' => 'web.kalkulacka.stinici', 'title' => 'Stínicí technika', 'img' => 'hero-3', 'alt' => 'Stínicí technika v interiéru'],
    ];
@endphp

<x-layouts.web
    title="Kalkulačka"
    description="Orientační kalkulačka zakázkového šití, vypínaných stropů a stínicí techniky."
>
    <section class="bg-paper">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:py-20">
            <h1 class="text-section-title">Kalkulačka</h1>

            <div class="mt-10 grid grid-cols-1 gap-6 md:grid-cols-3">
                @foreach ($calculators as $calculator)
                    <a
                        href="{{ route($calculator['route']) }}"
                        class="group flex flex-col overflow-hidden rounded-lg border-2 border-line bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-ink/40 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2"
                    >
                        <div class="aspect-[4/3] overflow-hidden">
                            <x-web.img :name="$calculator['img']" :alt="$calculator['alt']" width="800" height="600" />
                        </div>
                        <div class="border-t border-line px-5 py-6">
                            <h2 class="text-xl font-extrabold">{{ $calculator['title'] }}</h2>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
</x-layouts.web>
