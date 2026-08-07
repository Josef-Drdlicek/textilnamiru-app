@php
    $usps = [
        [
            'title' => 'Kalkulace zdarma',
            'text' => 'Před zahájením práce obdržíte detailní nabídku s rozpisem cen jednotlivých položek.',
            'icon' => 'M8 40V12a4 4 0 0 1 4-4h24a4 4 0 0 1 4 4v28l-6-4-5 4-5-4-5 4-5-4-6 4Z M16 18h16 M16 26h10',
        ],
        [
            'title' => 'Vše na míru',
            'text' => 'Žádné předem ušité modely. Každý produkt je unikát vytvořený podle vašich rozměrů.',
            'icon' => 'M6 34 34 6l8 8-28 28H6v-8Z M26 14l8 8 M12 28l2 2 M17 23l2 2 M22 18l2 2',
        ],
        [
            'title' => '30+ let zkušeností',
            'text' => 'Tisíce spokojených klientů od rodinných domů po hotely a historické zámky.',
            'icon' => 'M24 4a20 20 0 1 0 0 40 20 20 0 0 0 0-40Z M24 14v10l8 4',
        ],
        [
            'title' => 'Kvalitní materiály',
            'text' => 'Látky objednáváme výhradně od osvědčených evropských výrobců.',
            'icon' => 'M8 8h32v32H8Z M8 16h32 M8 24h32 M8 32h32 M16 8v32 M24 8v32 M32 8v32',
        ],
    ];
@endphp

<section aria-label="Proč si vybrat nás" class="border-b border-line bg-white">
    <div class="mx-auto grid max-w-7xl grid-cols-2 gap-x-6 gap-y-10 px-4 py-12 sm:px-6 lg:grid-cols-4 lg:py-16">
        @foreach ($usps as $usp)
            <div class="reveal">
                <svg class="h-11 w-11 text-brand-500" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="{{ $usp['icon'] }}"/>
                </svg>
                <h2 class="mt-4 text-lg font-extrabold">{{ $usp['title'] }}</h2>
                <p class="mt-2 leading-relaxed text-muted">{{ $usp['text'] }}</p>
            </div>
        @endforeach
    </div>
</section>
