<x-layouts.app title="Ceník stropů">
    <div class="space-y-10">
        <div>
            <h1 class="text-2xl font-bold text-stone-900">Ceník stropů</h1>
            <p class="mt-1 text-sm text-stone-500">
                Fólie, lišty, LED a příslušenství vypínaných stropů. Ceny jsou v Kč za uvedenou jednotku.
            </p>
        </div>

        <section>
            <h2 class="text-lg font-semibold text-stone-900">Položky</h2>
            @include('admin.pricing._variables', ['variables' => $variables, 'category' => \App\Enums\PricingCategory::Ceiling])
        </section>
    </div>
</x-layouts.app>
