{{-- Zavěšení textilu: zveřejněné kolejnice a dekorační garnýže z Ceníku kolejnic. Úvodní text je výchozí návrh. --}}
<x-layouts.web
    title="Zavěšení textilu"
    description="Kolejnice a dekorační garnýže pro záclony a závěsy. Poradíme s výběrem, zaměříme a namontujeme."
>
    <section class="bg-paper">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:py-20">
            <p class="eyebrow">Montáž a zavěšení</p>
            <h1 class="text-section-title mt-2">Zavěšení textilu</h1>
            <p class="mt-3 max-w-2xl text-lg text-muted">
                Postaráme se, aby záclony a závěsy visely přesně tam, kde mají. Vyberte si kolejnici nebo dekorační garnýž,
                délku zaměříme a vše namontujeme.
            </p>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('web.inquiry.create', ['produkt' => 'rails']) }}" class="btn-primary">Nezávazná poptávka</a>
                <a href="{{ route('web.kalkulacka') }}" class="btn-secondary">Kalkulačka</a>
            </div>

            @foreach (\App\Enums\RailKind::cases() as $kind)
                @if ($products = $groups->get($kind->value))
                    <h2 class="mt-14 text-2xl font-extrabold">{{ $kind->label() }}</h2>
                    <div class="mt-6 grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4">
                        @foreach ($products as $product)
                            @php $image = $product->coverImage(); @endphp
                            <a
                                href="{{ route('web.zaveseni.show', $product) }}"
                                class="group flex flex-col border-2 border-line bg-white transition hover:-translate-y-0.5 hover:border-ink/40 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2"
                            >
                                <div class="flex aspect-[4/3] items-center justify-center overflow-hidden bg-white p-2">
                                    @if ($image)
                                        <img src="{{ $image->url() }}" alt="{{ $image->alt }}" loading="lazy" class="max-h-full w-full object-contain">
                                    @endif
                                </div>
                                <div class="flex flex-1 flex-col border-t border-line px-4 py-4">
                                    @if ($product->line)
                                        <p class="text-xs font-bold tracking-wide text-muted uppercase">{{ $product->line }}</p>
                                    @endif
                                    <h3 class="font-extrabold">{{ $product->name }}</h3>
                                    @if ($product->subtitle)
                                        <p class="mt-1 text-sm text-muted">{{ $product->subtitle }}</p>
                                    @endif
                                    @if ($product->variants->count() > 1)
                                        <p class="mt-1 text-xs text-muted">{{ $product->variants->count() }} {{ $kind === \App\Enums\RailKind::Decorative ? 'barevných provedení' : 'sestav' }}</p>
                                    @endif
                                    <p class="mt-3 text-sm font-semibold text-brand-600 transition group-hover:text-brand-700">Více informací <span aria-hidden="true">→</span></p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            @endforeach
        </div>
    </section>
</x-layouts.web>
