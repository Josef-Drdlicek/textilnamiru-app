<x-layouts.web
    title="Zakázkové šití – kalkulačka"
    description="Orientační kalkulačka ušití záclon, závěsů, polštářů, sedáků a římských rolet. Cena za ušití bez látky, s DPH."
>
    <x-web.back-link :href="route('web.kalkulacka')" label="Zpět na výběr kalkulace" />

    @include('web.sections._calculator', ['asPage' => true, 'category' => \App\Enums\ProductCategory::Sewing])
</x-layouts.web>
