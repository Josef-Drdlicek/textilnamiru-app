{{-- Vizuální kalkulačka stínicí techniky — bez napojení na výpočet. --}}
<x-layouts.web
    title="Stínící technika – kalkulačka"
    description="DOPLNIT OBSAH"
>
    <div class="border-b border-line bg-white">
        <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6">
            <a href="{{ route('web.kalkulacka') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700 focus-visible:rounded focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2">
                Zpět na výběr kalkulace
            </a>
        </div>
    </div>

    <x-web.kalkulacka-vizual h1="Stínící technika" />
</x-layouts.web>
