<x-layouts.web>
    @include('web.sections._hero')
    @include('web.sections._usp')

    {{-- Tři obory v jedné sekci; plný obsah je na podstránkách. --}}
    @include('web.sections._services')

    @include('web.sections._page-teaser', [
        'class' => 'border-y border-line bg-white',
        'eyebrow' => 'Orientační kalkulačka',
        'title' => 'Kolik to bude stát?',
        'text' => 'Vyberte obor, zadejte rozměry a hned vidíte orientační cenu. Přesnou nabídku včetně látky připravíme zdarma po zaměření.',
        'href' => route('web.kalkulacka'),
        'cta' => 'Otevřít kalkulačku',
    ])

    @include('web.sections._segments')

    @include('web.sections._page-teaser', [
        'class' => 'bg-paper',
        'eyebrow' => 'Reference',
        'title' => 'Naše realizace',
        'text' => 'Ukázky práce z domácností, hotelů i historických objektů.',
        'href' => route('web.realizace'),
        'cta' => 'Prohlédnout realizace',
    ])

    @include('web.sections._process')
    @include('web.sections._about')
</x-layouts.web>
