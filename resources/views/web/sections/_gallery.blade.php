@php
    $items = [
        ['img' => 'gallery-hotel', 'caption' => 'Hotel Voroněž, Brno', 'alt' => 'Hotelový pokoj s vrstvenými závěsy', 'large' => true],
        ['img' => 'gallery-castle', 'caption' => 'Zámek Emy Destinové', 'alt' => 'Zámecké závěsy s dobovým interiérem', 'large' => false],
        ['img' => 'gallery-bedroom', 'caption' => 'Ložnice, Boskovice', 'alt' => 'Ložnice se zatemňovacími závěsy', 'large' => false],
        ['img' => 'gallery-living', 'caption' => 'Obývací pokoj, Brno', 'alt' => 'Obývací pokoj se závěsy na míru', 'large' => true],
        ['img' => 'gallery-office', 'caption' => 'Radnice Praha 12', 'alt' => 'Kancelář s vertikálními žaluziemi', 'large' => false],
        ['img' => 'gallery-restaurant', 'caption' => 'Restaurace, Vídeň', 'alt' => 'Restaurace se závěsy v teplých tónech', 'large' => false],
    ];

    $stats = [
        ['value' => '30+', 'label' => 'let zkušeností'],
        ['value' => '1000+', 'label' => 'spokojených klientů'],
        ['value' => '4', 'label' => 'země'],
        ['value' => '900', 'label' => 'hotelových pokojů v Ammánu'],
    ];
@endphp

<section id="realizace" aria-labelledby="realizace-nadpis" class="scroll-mt-20 bg-paper">
    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:py-20">
        <p class="eyebrow">Reference</p>
        <h2 id="realizace-nadpis" class="text-section-title mt-2">Naše realizace</h2>
        <p class="mt-3 max-w-2xl text-lg text-muted">
            Ukázky práce z domácností, hotelů i historických objektů.
        </p>

        <div class="mt-10 grid grid-cols-2 gap-4 md:grid-cols-4">
            @foreach ($items as $item)
                <figure class="reveal {{ $item['large'] ? 'col-span-2 row-span-2' : '' }}">
                    <div class="{{ $item['large'] ? 'aspect-[4/3]' : 'aspect-[4/3]' }}">
                        <x-web.img
                            :name="$item['img']"
                            :alt="$item['alt']"
                            :label="$item['caption']"
                            :width="$item['large'] ? 1200 : 800"
                            :height="$item['large'] ? 900 : 600"
                        />
                    </div>
                    <figcaption class="mt-2 text-sm font-medium text-muted">{{ $item['caption'] }}</figcaption>
                </figure>
            @endforeach
        </div>

        <dl class="mt-14 grid grid-cols-2 gap-8 border-t border-line pt-10 lg:grid-cols-4">
            @foreach ($stats as $stat)
                <div class="reveal">
                    <dd class="text-price-xl text-brand-500">{{ $stat['value'] }}</dd>
                    <dt class="mt-1 font-medium text-muted">{{ $stat['label'] }}</dt>
                </div>
            @endforeach
        </dl>
    </div>
</section>
