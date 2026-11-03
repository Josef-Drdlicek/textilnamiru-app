@php
    use App\Enums\ItemType;

    $calculable = [
        ['type' => ItemType::Curtain, 'img' => 'tile-curtain', 'title' => 'Záclony', 'alt' => 'Voálové záclony v kuchyni'],
        ['type' => ItemType::Drape, 'img' => 'tile-drape', 'title' => 'Závěsy', 'alt' => 'Zatemňovací závěsy v ložnici'],
        ['type' => ItemType::Pillow, 'img' => 'tile-pillow', 'title' => 'Polštáře', 'alt' => 'Dekorační polštáře ze lnu'],
        ['type' => ItemType::Cushion, 'img' => 'tile-cushion', 'title' => 'Sedáky', 'alt' => 'Sedáky na lavici u okna'],
        ['type' => ItemType::Blind, 'img' => 'tile-blind', 'title' => 'Římské rolety', 'alt' => 'Lněná římská roleta na vysokém okně'],
    ];

    $onRequest = [
        ['produkt' => 'rails', 'img' => 'tile-rails', 'title' => 'Garnýže a kolejnice', 'alt' => 'Detail garnýže s kroužky'],
        ['produkt' => 'shades', 'img' => 'tile-shades', 'title' => 'Žaluzie a stínění', 'alt' => 'Žaluzie na slunném okně'],
        ['produkt' => 'panels', 'img' => 'tile-panels', 'title' => 'Japonské stěny', 'alt' => 'Japonské posuvné stěny v interiéru'],
    ];

    $asPage = $asPage ?? false;
@endphp

<section id="sortiment" aria-labelledby="sortiment-nadpis" class="scroll-mt-20 bg-paper">
    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:py-20">
        <p class="eyebrow">Katalog</p>
        @if ($asPage)
            <h1 id="sortiment-nadpis" class="text-section-title mt-2">Náš sortiment</h1>
        @else
            <h2 id="sortiment-nadpis" class="text-section-title mt-2">Náš sortiment</h2>
        @endif
        <p class="mt-3 max-w-2xl text-lg text-muted">
            Vše šijeme na míru podle vašich rozměrů. Ceny za ušití spočítáte rovnou
            v&nbsp;kalkulačce — látku vybereme společně při konzultaci.
        </p>

        <div class="mt-10 grid grid-cols-2 gap-x-5 gap-y-8 md:grid-cols-3 xl:grid-cols-4">
            @foreach ($calculable as $tile)
                <x-web.product-tile
                    :img="$tile['img']"
                    :alt="$tile['alt']"
                    :title="$tile['title']"
                    :price="$pricesFrom[$tile['type']->value] ?? null"
                    :href="route('web.kalkulacka', ['typ' => $tile['type']->value])"
                    cta="Spočítat cenu"
                />
            @endforeach

            @foreach ($onRequest as $tile)
                <x-web.product-tile
                    :img="$tile['img']"
                    :alt="$tile['alt']"
                    :title="$tile['title']"
                    :href="route('web.inquiry.create', ['produkt' => $tile['produkt']])"
                    cta="Poptat"
                />
            @endforeach
        </div>
    </div>
</section>
