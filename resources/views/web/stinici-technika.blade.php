{{-- Texty jsou výchozí návrh, majitel si je doladí. Dlaždice sdílí fotky s katalogem (tile-*). --}}
@php
    $products = [
        ['produkt' => 'shades', 'img' => 'tile-shades', 'title' => 'Žaluzie a rolety', 'alt' => 'Žaluzie na slunném okně', 'text' => 'Horizontální i vertikální žaluzie, látkové a den-noc rolety. Vybereme materiál podle světla v místnosti.'],
        ['produkt' => 'panels', 'img' => 'tile-panels', 'title' => 'Japonské stěny', 'alt' => 'Japonské posuvné stěny v interiéru', 'text' => 'Posuvné panely pro velká okna a dělení prostoru. Panely šijeme ve vlastní dílně, kolejnice dodáme a namontujeme.'],
        ['produkt' => 'rails', 'img' => 'tile-rails', 'title' => 'Garnýže a kolejnice', 'alt' => 'Detail garnýže s kroužky', 'text' => 'Stropní i stěnové kolejnice, garnýže a elektrické pohony. Zaměříme tak, aby závěsy visely přesně.'],
    ];

    $steps = [
        ['title' => 'Poptávka', 'text' => 'Napište, co chcete stínit, a připojte rozměry oken nebo fotku.'],
        ['title' => 'Zaměření', 'text' => 'Přijedeme, změříme okna a doporučíme vhodný typ stínění a materiál.'],
        ['title' => 'Nabídka', 'text' => 'Dostanete cenu s rozpisem položek. Stínění sladíme se záclonami a závěsy.'],
        ['title' => 'Montáž', 'text' => 'Dodáme a namontujeme. Výrobu a montáž zajišťujeme vlastními lidmi.'],
    ];
@endphp

<x-layouts.web
    title="Stínicí technika"
    description="Žaluzie, látkové rolety, japonské stěny a garnýže na míru. Zaměříme, dodáme a namontujeme, aby stínění ladilo s textilem."
>
    <section class="bg-paper">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:py-20">
            <p class="eyebrow">Stínění pro každou situaci</p>
            <h1 class="text-section-title mt-2">Stínicí technika</h1>
            <p class="mt-3 max-w-2xl text-lg text-muted">
                Kromě šití dodáváme i to, co okno stíní: žaluzie, látkové rolety, japonské stěny a garnýže.
                Všechno zaměříme a namontujeme my, takže stínění ladí se záclonami i závěsy.
            </p>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('web.inquiry.create', ['produkt' => 'shades']) }}" class="btn-primary">Nezávazná poptávka</a>
                <a href="{{ route('web.kalkulacka') }}" class="btn-secondary">Kalkulačka</a>
            </div>

            <div class="mt-12 grid grid-cols-1 gap-x-5 gap-y-8 sm:grid-cols-3">
                @foreach ($products as $product)
                    <article class="reveal flex flex-col bg-white">
                        <div class="aspect-[4/3]">
                            <x-web.img :name="$product['img']" :alt="$product['alt']" width="800" height="600" />
                        </div>
                        <div class="flex flex-1 flex-col px-1 pb-6 pt-4">
                            <h2 class="text-lg font-extrabold">{{ $product['title'] }}</h2>
                            <p class="mt-2 flex-1 text-muted">{{ $product['text'] }}</p>
                            <p class="mt-3">
                                <a href="{{ route('web.inquiry.create', ['produkt' => $product['produkt']]) }}" class="font-semibold text-brand-600 transition hover:text-brand-700">
                                    Poptat <span aria-hidden="true">→</span>
                                </a>
                            </p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="border-y border-line bg-white" aria-labelledby="stineni-postup">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:py-20">
            <p class="eyebrow">Jak to probíhá</p>
            <h2 id="stineni-postup" class="text-section-title mt-2">Od poptávky k namontovanému stínění</h2>
            <ol class="mt-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($steps as $step)
                    <li class="reveal">
                        <p class="text-price-xl text-brand-500">{{ $loop->iteration }}</p>
                        <h3 class="mt-2 text-lg font-extrabold">{{ $step['title'] }}</h3>
                        <p class="mt-2 text-muted">{{ $step['text'] }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>
</x-layouts.web>
