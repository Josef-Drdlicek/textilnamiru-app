@php $svg = $this->schemaSvg(); @endphp

<div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
    <h2 class="text-sm font-semibold uppercase tracking-wide text-stone-500">Náhled</h2>

    @if ($svg === null)
        <p class="mt-3 text-sm text-stone-400">Náhled se vykreslí po zadání rozměrů.</p>
    @else
        <div class="mt-3">
            {!! $svg !!}
        </div>
    @endif
</div>
