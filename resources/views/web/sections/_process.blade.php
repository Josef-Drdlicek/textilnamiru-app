@php
    $steps = [
        ['title' => 'Poptávka', 'text' => 'Vyplňte nezávazný formulář nebo nás kontaktujte. Popište, co potřebujete.'],
        ['title' => 'Konzultace', 'text' => 'Probereme vaše požadavky, styl interiéru a možnosti materiálů.'],
        ['title' => 'Zaměření', 'text' => 'Přijedeme k vám nebo vám poskytneme návod pro vlastní zaměření.'],
        ['title' => 'Nabídka', 'text' => 'Obdržíte detailní cenovou kalkulaci s rozpisem všech položek.'],
        ['title' => 'Výroba', 'text' => 'Zkušené švadleny ušijí textil přesně podle vašich rozměrů.'],
        ['title' => 'Montáž', 'text' => 'Přijedeme a vše profesionálně nainstalujeme. Hotovo.'],
    ];
@endphp

<section id="proces" aria-labelledby="proces-nadpis" class="scroll-mt-20 border-y border-line bg-white">
    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:py-20">
        <p class="eyebrow">Jak to funguje</p>
        <h2 id="proces-nadpis" class="text-section-title mt-2">Od poptávky k hotovému interiéru</h2>

        <ol class="mt-10 grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
            @foreach ($steps as $step)
                <li class="reveal">
                    <span class="text-4xl font-extrabold tabular-nums text-brand-300" aria-hidden="true">
                        {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                    </span>
                    <h3 class="mt-2 text-lg font-extrabold">{{ $step['title'] }}</h3>
                    <p class="mt-1.5 leading-relaxed text-muted">{{ $step['text'] }}</p>
                </li>
            @endforeach
        </ol>

        <div class="mt-12">
            <a href="{{ route('web.inquiry.create') }}" class="btn-primary">Začít nezávaznou poptávkou</a>
        </div>
    </div>
</section>
