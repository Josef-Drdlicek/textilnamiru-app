<x-layouts.web title="Děkujeme za poptávku">
    <div class="mx-auto max-w-2xl px-4 py-20 text-center sm:px-6 lg:py-28">
        <svg class="mx-auto h-16 w-16 text-brand-500" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="24" cy="24" r="21"/>
            <path d="m15 24 6 6 12-12"/>
        </svg>
        <h1 class="text-section-title mt-6">Děkujeme za poptávku!</h1>
        <p class="mt-4 text-lg text-muted">
            Ozveme se vám do 2 pracovních dnů. Mezitím si můžete prohlédnout
            naše realizace nebo spočítat další orientační ceny.
        </p>
        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
            <a href="{{ route('web.realizace') }}" class="btn-primary">Prohlédnout realizace</a>
            <a href="{{ route('web.home') }}" class="btn-secondary">Zpět na úvod</a>
        </div>
    </div>
</x-layouts.web>
