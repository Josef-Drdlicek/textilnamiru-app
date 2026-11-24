{{-- Seznam rolet z Ceníku rolet (jen zveřejněné). Úvodní text je výchozí návrh, majitel si ho doladí. --}}
<x-layouts.web
    title="Rolety"
    description="Látkové, den-noc a zatemňující rolety na míru. Zaměříme, dodáme a namontujeme."
>
    <x-web.back-link :href="route('web.stinici-technika')" label="Zpět na stínicí techniku" />

    <section class="bg-paper">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:py-20">
            <p class="eyebrow">Stínicí technika</p>
            <h1 class="text-section-title mt-2">Rolety</h1>
            <p class="mt-3 max-w-2xl text-lg text-muted">
                Látkové, den-noc a zatemňující rolety na míru. Vyberte typ rolety, rozměry zaměříme a roletu namontujeme.
            </p>

            @if ($products->isNotEmpty())
                <div class="mt-10 grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4">
                    @foreach ($products as $product)
                        @php
                            $main = $product->mainVariant();
                            $image = $main?->images->first(fn ($i) => $i->image_path !== null);
                        @endphp
                        <a
                            href="{{ route('web.rolety.show', $product) }}"
                            class="group flex flex-col border-2 border-line bg-white transition hover:-translate-y-0.5 hover:border-ink/40 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2"
                        >
                            <div class="aspect-square overflow-hidden bg-surface-alt">
                                @if ($image)
                                    <img src="{{ $image->url() }}" alt="{{ $image->alt }}" loading="lazy" class="h-full w-full object-cover">
                                @endif
                            </div>
                            <div class="flex flex-1 flex-col border-t border-line px-4 py-4">
                                <h2 class="font-extrabold">{{ $product->name }}</h2>
                                @if ($main?->subtitle)
                                    <p class="mt-1 text-sm text-muted">{{ $main->subtitle }}</p>
                                @endif
                                @if ($product->variants->count() > 1)
                                    <p class="mt-1 text-xs text-muted">{{ $product->variants->count() }} provedení</p>
                                @endif
                                <p class="mt-3 text-sm font-semibold text-brand-600 transition group-hover:text-brand-700">Více o roletě <span aria-hidden="true">→</span></p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('web.inquiry.create', ['produkt' => 'shades']) }}" class="btn-primary">Nezávazná poptávka</a>
            </div>
        </div>
    </section>
</x-layouts.web>
