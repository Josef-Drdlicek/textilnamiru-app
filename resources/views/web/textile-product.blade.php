{{-- Produktová stránka látky: nahoře fotka (podle barvy), popis a parametry, pod tím objednávka šití na míru.
     Fotky nahrává dílna v Ceníku textilu nebo je stáhne import dodavatele; jinak se ukáže placeholder. --}}
<x-layouts.web
    :title="$product->name"
    :description="\Illuminate\Support\Str::limit($product->description ?? $product->name, 155)"
>
    <x-web.back-link :href="route($product->category->indexRoute())" :label="'Zpět na '.mb_strtolower($product->category->pluralLabel())" />

    <section class="bg-paper">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 lg:grid-cols-2 lg:gap-12 lg:py-14">
            {{-- Galerie: hlavní fotka se mění podle vybrané barvy (náhledy níže i krok „Barva“ v objednávce šití). --}}
            <div data-textile-gallery>
                <div class="aspect-[4/3] overflow-hidden border border-line bg-white">
                    @if ($mainImage = $product->mainImageUrl())
                        <img data-textile-main src="{{ $mainImage }}" alt="{{ $product->name }}" width="1200" height="900" loading="eager" fetchpriority="high" class="h-full w-full object-cover">
                    @else
                        <x-web.textile-image :product="$product" sizes="(min-width: 1024px) 50vw, 100vw" eager />
                    @endif
                </div>

                @if ($colorsWithImage->isNotEmpty())
                    <ul class="mt-3 grid grid-cols-5 gap-2 sm:grid-cols-6 lg:grid-cols-7" aria-label="Barvy">
                        @foreach ($colorsWithImage as $color)
                            <li>
                                <button
                                    type="button"
                                    data-textile-color="{{ $color->name }}"
                                    data-image="{{ $color->imageUrl() }}"
                                    class="textile-swatch"
                                    aria-label="{{ $color->label() }}"
                                    title="{{ $color->label() }}"
                                >
                                    <img src="{{ $color->imageUrl() }}" alt="" loading="lazy" class="aspect-square w-full object-cover">
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div>
                <p class="eyebrow">{{ $product->category->label() }}</p>
                <h1 class="text-section-title mt-2">{{ $product->name }}</h1>

                @if ($product->description)
                    <p class="mt-4 text-lg leading-relaxed text-muted">{{ $product->description }}</p>
                @endif

                <dl class="mt-6 divide-y divide-line border-y border-line text-sm">
                    @foreach ($specs as $label => $value)
                        <div class="flex gap-4 py-2.5">
                            <dt class="w-32 shrink-0 font-semibold">{{ $label }}</dt>
                            <dd class="text-muted">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>

                @if ($product->hasPrice())
                    <div class="mt-6 flex items-baseline gap-3">
                        <p class="text-price-xl">{{ number_format($pricePerMeterVat, 0, ',', ' ') }}&nbsp;Kč/m</p>
                        <p class="text-sm text-muted">vč. DPH · {{ number_format($product->price_per_meter, 0, ',', ' ') }}&nbsp;Kč/m bez DPH</p>
                    </div>
                    <p class="mt-2 text-sm text-muted">Cena za běžný metr látky. Ušití na míru spočítáte níže.</p>
                @else
                    <p class="mt-6 text-lg font-semibold">Cena na poptávku</p>
                    <p class="mt-1 text-sm text-muted">Cenu látky i ušití vám rádi spočítáme. Projděte objednávku šití níže a pošlete nám poptávku.</p>
                @endif

                <a href="#objednavka-siti" class="btn-primary mt-6">Nechat ušít na míru</a>
            </div>
        </div>
    </section>

    <section id="objednavka-siti" class="scroll-mt-20 border-t border-line bg-white" aria-labelledby="objednavka-siti-nadpis">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:py-16">
            <p class="eyebrow">Šití na míru</p>
            <h2 id="objednavka-siti-nadpis" class="text-section-title mt-2">Objednávka šití na míru</h2>
            <p class="mt-3 max-w-2xl text-muted">
                Projděte kroky a hned uvidíte cenu hotového výrobku včetně látky a ušití.
            </p>

            <div class="mt-8">
                <livewire:web.textile-product-configurator :product="$product" :key="'textile-'.$product->id" />
            </div>
        </div>
    </section>
</x-layouts.web>
