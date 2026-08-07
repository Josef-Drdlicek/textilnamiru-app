@props([
    'img',
    'alt',
    'title',
    'price' => null, // int Kč vč. DPH, nebo null = „Na poptávku"
    'href',
    'cta',
])

<article class="reveal group flex flex-col bg-white">
    <a href="{{ $href }}" class="block overflow-hidden" tabindex="-1" aria-hidden="true">
        <div class="aspect-[4/3] transition duration-300 group-hover:-translate-y-0.5 group-hover:shadow-md">
            <x-web.img :name="$img" :alt="$alt" width="800" height="600" />
        </div>
    </a>
    <div class="flex flex-1 flex-col px-1 pb-6 pt-4">
        <h3 class="text-lg font-extrabold">{{ $title }}</h3>
        @if ($price !== null)
            <p class="mt-1 text-xl font-extrabold tabular-nums">
                od {{ number_format($price, 0, ',', ' ') }}&nbsp;Kč
            </p>
            <p class="text-sm text-muted">za ušití vč. DPH, bez látky</p>
        @else
            <p class="mt-1.5">
                <span class="inline-block rounded-full bg-brand-100 px-3 py-1 text-sm font-semibold text-brand-700">Na poptávku</span>
            </p>
        @endif
        <p class="mt-3">
            <a href="{{ $href }}" class="font-semibold text-brand-600 transition hover:text-brand-700">
                {{ $cta }} <span aria-hidden="true">→</span>
            </a>
        </p>
    </div>
</article>
