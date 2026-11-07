{{-- Maketa bez výpočtu, dokud nebude ceník stínicí techniky; noindex a mimo sitemap. --}}
<x-layouts.web
    title="Stínicí technika – kalkulačka"
    description="Orientační kalkulačka stínicí techniky."
    robots="noindex, follow"
>
    <div class="border-b border-line bg-white">
        <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6">
            <a href="{{ route('web.kalkulacka') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700 focus-visible:rounded focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2">
                Zpět na výběr kalkulace
            </a>
        </div>
    </div>

    <x-web.kalkulacka-vizual h1="Stínicí technika" />
</x-layouts.web>
