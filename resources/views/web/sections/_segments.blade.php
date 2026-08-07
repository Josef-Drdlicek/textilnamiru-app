@php
    $segments = [
        [
            'title' => 'Domácí prostory',
            'text' => 'Z vašeho domova bude vyzařovat klid, teplo a pohoda. Ať už toužíte po moderním či klasickém stylu.',
        ],
        [
            'title' => 'Firemní prostory',
            'text' => 'Moderní styl nebo formální elegance. Textil ušijeme tak, aby do vašeho interiéru skutečně patřil.',
        ],
        [
            'title' => 'Hotelové prostory',
            'text' => 'Čalounění, závěsy, záclony, potahy. Spolupracujeme s hotely Voroněž, Continental, International a dalšími.',
        ],
        [
            'title' => 'Společenské prostory',
            'text' => 'Divadla, kinosály, historické zámky, restaurace. S naším textilem pozvednete prostory o třídu výš.',
        ],
    ];
@endphp

<section id="sluzby" aria-labelledby="sluzby-nadpis" class="scroll-mt-20 bg-surface-alt">
    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:py-20">
        <p class="eyebrow">Pro koho šijeme</p>
        <h2 id="sluzby-nadpis" class="text-section-title mt-2">Od bytu po hotel</h2>

        <div class="mt-10 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($segments as $segment)
                <article class="reveal flex flex-col bg-white p-7">
                    <h3 class="text-lg font-extrabold">{{ $segment['title'] }}</h3>
                    <p class="mt-2 flex-1 leading-relaxed text-muted">{{ $segment['text'] }}</p>
                    <p class="mt-4">
                        <a href="{{ route('web.inquiry.create') }}" class="font-semibold text-brand-600 transition hover:text-brand-700">
                            Poptat realizaci <span aria-hidden="true">→</span>
                        </a>
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
