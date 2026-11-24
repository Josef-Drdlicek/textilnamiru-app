{{-- Mřížka látek z Ceníku textilu (stránky Záclony a Závěsy). --}}
@if ($products->isNotEmpty())
    <div class="mt-10 grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4">
        @foreach ($products as $product)
            <a
                href="{{ route($product->category->showRoute(), $product) }}"
                class="group flex flex-col border-2 border-line bg-white transition hover:-translate-y-0.5 hover:border-ink/40 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2"
            >
                <div class="aspect-square overflow-hidden">
                    <x-web.textile-image :product="$product" sizes="(min-width: 1024px) 25vw, 50vw" :width="600" :height="600" />
                </div>
                <div class="flex flex-1 flex-col border-t border-line px-4 py-4">
                    <h2 class="font-extrabold">{{ $product->name }}</h2>
                    <p class="mt-1 text-sm text-muted">{{ $product->material }}</p>
                    <p class="mt-3 text-sm font-semibold text-brand-600 transition group-hover:text-brand-700">Spočítat ušití <span aria-hidden="true">→</span></p>
                </div>
            </a>
        @endforeach
    </div>
@endif
