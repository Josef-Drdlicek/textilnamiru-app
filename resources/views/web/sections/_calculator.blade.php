{{-- Kalkulačka jednoho oboru; na samostatné stránce je nadpis h1. --}}
@php
    $asPage = $asPage ?? false;
    $category = $category ?? \App\Enums\ProductCategory::Sewing;
@endphp

<section id="kalkulacka" aria-labelledby="kalkulacka-nadpis" class="scroll-mt-20 border-y border-line bg-white">
    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:py-20">
        <p class="eyebrow">Orientační kalkulačka</p>
        @if ($asPage)
            <h1 id="kalkulacka-nadpis" class="text-section-title mt-2">{{ $category->estimatorTitle() }}</h1>
        @else
            <h2 id="kalkulacka-nadpis" class="text-section-title mt-2">{{ $category->estimatorTitle() }}</h2>
        @endif
        <p class="mt-3 max-w-2xl text-lg text-muted">{{ $category->estimatorIntro() }}</p>

        <div class="mt-10">
            <livewire:web.price-estimator :category="$category->value" :key="'estimator-'.$category->value" />
        </div>
    </div>
</section>
