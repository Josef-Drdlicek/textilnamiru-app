<x-layouts.web>
    @include('web.sections._hero')
    @include('web.sections._usp')

    {{-- Plný katalog, kalkulačka a galerie jsou na vlastních stránkách. --}}
    @include('web.sections._page-teaser', [
        'class' => 'border-b border-line bg-white',
        'title' => 'Vypínaný podhled',
        'href' => route('web.vypinany-podhled'),
        'cta' => 'Vypínaný podhled',
    ])
    @include('web.sections._page-teaser', [
        'class' => 'bg-paper',
        'title' => 'Stínicí technika',
        'href' => route('web.stinici-technika'),
        'cta' => 'Stínicí technika',
    ])
    @include('web.sections._page-teaser', [
        'class' => 'bg-paper',
        'eyebrow' => 'Katalog',
        'title' => 'Náš sortiment',
        'text' => 'Vše šijeme na míru podle vašich rozměrů. Ceny za ušití spočítáte rovnou v kalkulačce — látku vybereme společně při konzultaci.',
        'href' => route('web.sortiment'),
    ])
    @include('web.sections._page-teaser', [
        'class' => 'border-y border-line bg-white',
        'eyebrow' => 'Orientační kalkulačka',
        'title' => 'Kolik bude stát ušití?',
        'text' => 'Vyberte produkt, zadejte rozměry a hned vidíte orientační cenu za ušití. Přesnou nabídku včetně látky připravíme zdarma po zaměření.',
        'href' => route('web.kalkulacka'),
    ])

    @include('web.sections._segments')

    @include('web.sections._page-teaser', [
        'class' => 'bg-paper',
        'eyebrow' => 'Reference',
        'title' => 'Naše realizace',
        'text' => 'Ukázky práce z domácností, hotelů i historických objektů.',
        'href' => route('web.realizace'),
    ])

    @include('web.sections._process')
    @include('web.sections._about')
</x-layouts.web>
