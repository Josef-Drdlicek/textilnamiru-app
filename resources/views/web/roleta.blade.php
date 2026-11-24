{{-- Detail rolety: varianty (?varianta=), fotky s přepínáním (textile-gallery.js), popis a technické informace. --}}
<x-layouts.web
    :title="$product->name"
    :description="\Illuminate\Support\Str::limit($variant->paragraphs()[0] ?? $product->name, 155)"
>
    <x-web.back-link :href="route('web.rolety')" label="Zpět na rolety" />

    <section class="bg-paper">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 lg:grid-cols-2 lg:gap-12 lg:py-14">
            <div data-textile-gallery>
                <div class="aspect-[4/3] overflow-hidden border border-line bg-white">
                    @if ($images->isNotEmpty())
                        <img data-textile-main src="{{ $images->first()->url() }}" alt="{{ $images->first()->alt }}" width="1200" height="900" loading="eager" fetchpriority="high" class="h-full w-full object-contain">
                    @endif
                </div>

                @if ($images->count() > 1)
                    <ul class="mt-3 grid grid-cols-5 gap-2 sm:grid-cols-6" aria-label="Fotky">
                        @foreach ($images as $image)
                            <li>
                                <button type="button" data-textile-color="foto-{{ $image->id }}" data-image="{{ $image->url() }}" class="textile-swatch" aria-label="Fotka {{ $loop->iteration }}: {{ $image->alt }}">
                                    <img src="{{ $image->url() }}" alt="" loading="lazy" class="aspect-square w-full object-cover">
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div>
                <p class="eyebrow">Rolety</p>
                <h1 class="text-section-title mt-2">{{ $product->name }}</h1>
                @if ($variant->subtitle)
                    <p class="mt-1 text-lg font-semibold">{{ $variant->subtitle }}</p>
                @endif
                @if ($variant->slogan)
                    <p class="mt-2 text-brand-600 italic">{{ $variant->slogan }}</p>
                @endif

                @if ($product->variants->count() > 1)
                    <nav class="mt-6" aria-label="Provedení">
                        <p class="text-sm font-semibold">Provedení</p>
                        <ul class="mt-2 flex flex-wrap gap-2">
                            @foreach ($product->variants as $index => $option)
                                @php $current = $option->is($variant); @endphp
                                <li>
                                    <a href="{{ route('web.rolety.show', [$product, 'varianta' => $index]) }}"
                                       class="inline-flex min-h-11 items-center border-2 px-4 text-sm font-semibold transition {{ $current ? 'border-brand-600 bg-brand-50' : 'border-line bg-white hover:border-ink/40' }}"
                                       @if ($current) aria-current="page" @endif>{{ $option->name }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </nav>
                @endif

                <div class="mt-6 space-y-4 leading-relaxed text-muted">
                    @foreach ($variant->paragraphs() as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                </div>

                <dl class="mt-6 divide-y divide-line border-y border-line text-sm">
                    <div class="flex gap-4 py-2.5">
                        <dt class="w-32 shrink-0 font-semibold">Šířka</dt>
                        <dd class="text-muted">{{ $variant->min_width }}–{{ $variant->max_width }} cm</dd>
                    </div>
                    <div class="flex gap-4 py-2.5">
                        <dt class="w-32 shrink-0 font-semibold">Výška</dt>
                        <dd class="text-muted">{{ $variant->min_height }}–{{ $variant->max_height }} cm</dd>
                    </div>
                </dl>

                @if ($priceFromVat !== null)
                    <p class="text-price-xl mt-6">od {{ number_format($priceFromVat, 0, ',', ' ') }}&nbsp;Kč</p>
                    <p class="text-sm text-muted">vč. DPH, cena podle rozměru okna</p>
                @else
                    <p class="mt-6 text-lg font-semibold">Cena podle rozměru okna</p>
                    <p class="mt-1 text-sm text-muted">Pošlete nám rozměry a připravíme nabídku.</p>
                @endif

                <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                    <a href="#vypocet-ceny" class="btn-primary">Spočítat cenu</a>
                    <a href="{{ route('web.inquiry.create', ['produkt' => 'shades']) }}" class="btn-secondary">Nezávazná poptávka</a>
                </div>
            </div>
        </div>
    </section>

    <section id="vypocet-ceny" class="scroll-mt-20 border-t border-line bg-white" aria-labelledby="vypocet-ceny-nadpis">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:py-16">
            <p class="eyebrow">{{ $product->name }} · {{ $variant->name }}</p>
            <h2 id="vypocet-ceny-nadpis" class="text-section-title mt-2">Výpočet ceny</h2>
            <p class="mt-3 max-w-2xl text-muted">Zadejte rozměr rolety, vyberte látku a hned uvidíte cenu. Jiné provedení vyberete nahoře.</p>

            <div class="mt-8">
                <livewire:web.shade-configurator :variant="$variant" :variant-index="$variantIndex" :key="'shade-'.$variant->id" />
            </div>
        </div>
    </section>

    @if ($variant->technical)
        <section class="border-t border-line bg-paper" aria-labelledby="technicke-informace">
            <div class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:py-16">
                <p class="eyebrow">{{ $variant->name }}</p>
                <h2 id="technicke-informace" class="text-section-title mt-2">Technické informace</h2>

                <div class="mt-6 space-y-3 text-muted">
                    @foreach ($variant->technical as $block)
                        @switch($block['type'] ?? '')
                            @case('heading')
                                <h3 class="pt-4 text-lg font-extrabold text-ink">{{ $block['text'] }}</h3>
                                @break
                            @case('list')
                                <ul class="list-disc space-y-1 pl-5">
                                    @foreach ($block['items'] as $item)
                                        <li @class(['ml-5 list-[circle]' => str_starts_with($item, '– ')])>{{ ltrim($item, '– ') }}</li>
                                    @endforeach
                                </ul>
                                @break
                            @case('text')
                                <p>{{ $block['text'] }}</p>
                                @break
                        @endswitch
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts.web>
