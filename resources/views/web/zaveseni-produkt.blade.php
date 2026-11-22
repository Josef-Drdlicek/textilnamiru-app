{{-- Detail kolejnice / dekorační garnýže: varianty (?varianta=), fotky (textile-gallery.js), vlastnosti, díly a ceny podle délky. --}}
@php $decorative = $product->kind === \App\Enums\RailKind::Decorative; @endphp
<x-layouts.web
    :title="$product->name.($product->variants->count() > 1 ? ' – '.$variant->name : '')"
    :description="\Illuminate\Support\Str::limit(($product->subtitle ? $product->subtitle.'. ' : '').implode(', ', $features), 155)"
>
    <x-web.back-link :href="route('web.zaveseni-textilu')" label="Zpět na zavěšení textilu" />

    <section class="bg-paper">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 lg:grid-cols-2 lg:gap-12 lg:py-14">
            <div data-textile-gallery>
                <div class="flex aspect-[4/3] items-center justify-center overflow-hidden border border-line bg-white">
                    @if ($images->isNotEmpty())
                        <img data-textile-main src="{{ $images->first()->url() }}" alt="{{ $images->first()->alt }}" width="1200" height="900" loading="eager" fetchpriority="high" class="h-full w-full object-contain">
                    @endif
                </div>

                @if ($images->count() > 1)
                    <ul class="mt-3 grid grid-cols-5 gap-2 sm:grid-cols-6" aria-label="Fotky">
                        @foreach ($images as $image)
                            <li>
                                <button type="button" data-textile-color="foto-{{ $image->id }}" data-image="{{ $image->url() }}" class="textile-swatch" aria-label="Fotka {{ $loop->iteration }}: {{ $image->alt }}">
                                    <img src="{{ $image->url() }}" alt="" loading="lazy" class="aspect-square w-full bg-white object-contain">
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div>
                <p class="eyebrow">{{ $product->kind->label() }}</p>
                <h1 class="text-section-title mt-2">{{ $product->name }}</h1>
                @if ($product->subtitle)
                    <p class="mt-1 text-lg font-semibold">{{ $product->subtitle }}</p>
                @endif

                @if ($product->line)
                    <p class="mt-2 text-sm text-muted">Řada {{ $product->line }}</p>
                @endif

                @if ($product->variants->count() > 1)
                    <nav class="mt-6" aria-label="{{ $product->variantLabel() }}">
                        <p class="text-sm font-semibold">{{ $product->variantLabel() }}</p>
                        <ul class="mt-2 flex flex-wrap gap-2">
                            @foreach ($product->variants as $index => $option)
                                @php $current = $option->is($variant); @endphp
                                <li>
                                    <a href="{{ route('web.zaveseni.show', [$product, 'varianta' => $index]) }}"
                                       class="inline-flex min-h-11 flex-col justify-center border-2 px-4 py-1.5 text-sm font-semibold transition {{ $current ? 'border-brand-600 bg-brand-50' : 'border-line bg-white hover:border-ink/40' }}"
                                       @if ($current) aria-current="page" @endif>
                                        @if ($option->code)
                                            <span class="text-xs font-bold text-muted">{{ $option->code }}</span>
                                        @endif
                                        {{ $option->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </nav>
                @endif

                @if ($product->paragraphs())
                    <div class="mt-6 space-y-4 leading-relaxed text-muted">
                        @foreach ($product->paragraphs() as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @endforeach
                    </div>
                @endif

                @if ($features)
                    <ul class="mt-4 list-disc space-y-1.5 pl-5 leading-relaxed text-ink/85">
                        @foreach ($features as $feature)
                            <li>{{ $feature }}</li>
                        @endforeach
                    </ul>
                @endif

                <dl class="mt-6 divide-y divide-line border-y border-line text-sm">
                        @foreach ($specs as $row)
                            <div class="flex gap-4 py-2.5">
                                <dt class="w-32 shrink-0 font-semibold">{{ $row['label'] }}</dt>
                                <dd class="text-muted">{{ $row['value'] }}</dd>
                            </div>
                        @endforeach
                        <div class="flex gap-4 py-2.5">
                            <dt class="w-32 shrink-0 font-semibold">Zavěšení</dt>
                            <dd class="text-muted">{{ implode(', ', array_map(fn ($mounting) => mb_strtolower($mounting->label()), $product->mountingOptions())) }}</dd>
                        </div>
                </dl>

                @if ($priceFromVat !== null)
                    <p class="text-price-xl mt-6">od {{ number_format($priceFromVat, 0, ',', ' ') }}&nbsp;Kč</p>
                    <p class="text-sm text-muted">vč. DPH, cena podle délky</p>
                @else
                    <p class="mt-6 text-lg font-semibold">Cena podle délky</p>
                    <p class="mt-1 text-sm text-muted">Pošlete nám rozměry a připravíme nabídku.</p>
                @endif

                <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                    <a href="#vypocet-ceny" class="btn-primary">Spočítat cenu</a>
                    <a href="{{ route('web.inquiry.create', ['produkt' => 'rails']) }}" class="btn-secondary">Nezávazná poptávka</a>
                </div>
            </div>
        </div>
    </section>

    <section id="vypocet-ceny" class="scroll-mt-20 border-t border-line bg-white" aria-labelledby="vypocet-ceny-nadpis">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:py-16">
            <p class="eyebrow">{{ $product->name }} · {{ $variant->name }}</p>
            <h2 id="vypocet-ceny-nadpis" class="text-section-title mt-2">Výpočet ceny</h2>
            <p class="mt-3 max-w-2xl text-muted">Vyberte držák, zadejte délku a hned uvidíte cenu. {{ $product->kind === \App\Enums\RailKind::Decorative ? 'Jinou barvu' : 'Jinou sestavu' }} vyberete nahoře.</p>

            <div class="mt-8">
                <livewire:web.rail-configurator :variant="$variant" :variant-index="$variantIndex" :key="'rail-'.$variant->id" />
            </div>
        </div>
    </section>

    @foreach ($components as $group => $items)
        <section class="border-t border-line {{ $loop->odd ? 'bg-paper' : 'bg-white' }}" aria-labelledby="dily-{{ $loop->iteration }}">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6">
                <h2 id="dily-{{ $loop->iteration }}" class="text-2xl font-extrabold">{{ $group }}</h2>
                <ul class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                    @foreach ($items as $item)
                        <li class="flex flex-col border border-line bg-white">
                            <div class="flex aspect-square items-center justify-center p-3">
                                @if ($item['url'])
                                    <img src="{{ $item['url'] }}" alt="{{ $item['name'] }}" loading="lazy" class="max-h-full w-full object-contain">
                                @endif
                            </div>
                            <div class="border-t border-line px-3 py-2.5 text-sm">
                                @if ($item['code'])
                                    <p class="font-extrabold">{{ $item['code'] }}</p>
                                @endif
                                <p class="text-muted">{{ $item['name'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endforeach

    @if ($bandPrices->isNotEmpty())
        <section id="ceny-podle-delky" class="scroll-mt-20 border-t border-line bg-white" aria-labelledby="ceny-nadpis">
            <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:py-16">
                <p class="eyebrow">{{ $product->name }} · {{ $variant->name }}</p>
                <h2 id="ceny-nadpis" class="text-section-title mt-2">Ceny podle délky</h2>
                <p class="mt-3 text-muted">Cena za kus včetně DPH podle držáku. Délku zaokrouhlujeme nahoru na celé pásmo po 50 cm.</p>
                <table class="mt-6 w-full text-sm">
                    <thead class="border-b-2 border-ink text-left">
                        <tr>
                            <th class="py-2 font-semibold">Délka</th>
                            @foreach ($product->mountingOptions() as $mounting)
                                <th class="py-2 text-right font-semibold">{{ $mounting->label() }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($bandPrices as $row)
                            <tr>
                                <td class="py-2 tabular-nums">{{ $row['label'] }}</td>
                                @foreach ($product->mountingOptions() as $mounting)
                                    <td class="py-2 text-right font-semibold tabular-nums">
                                        {{ isset($row['prices'][$mounting->value]) ? number_format($row['prices'][$mounting->value], 0, ',', ' ').' Kč' : '—' }}
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif
</x-layouts.web>
