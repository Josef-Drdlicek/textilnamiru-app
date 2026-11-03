{{-- Samostatná stránka kalkulačky — stejný formulář a výpočty jako na úvodu. --}}
<x-layouts.web
    title="Kalkulačka"
    description="Orientační kalkulačka ušití záclon, závěsů, polštářů, sedáků a římských rolet. Cena za ušití bez látky, s DPH."
>
    @include('web.sections._calculator', ['asPage' => true])
</x-layouts.web>
