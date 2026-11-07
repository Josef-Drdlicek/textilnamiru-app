<x-layouts.web
    title="Vypínané stropy – kalkulačka"
    description="Orientační kalkulačka vypínaného stropu podle rozměrů místnosti. Cena s DPH, přesnou nabídku připravíme po zaměření."
>
    <x-web.back-link :href="route('web.kalkulacka')" label="Zpět na výběr kalkulace" />

    @include('web.sections._calculator', ['asPage' => true, 'category' => \App\Enums\ProductCategory::Ceiling])
</x-layouts.web>
