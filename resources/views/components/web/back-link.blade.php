{{-- Lišta „zpět“ pod hlavičkou u podstránek, které mají nadřazený rozcestník. --}}
@props(['href', 'label'])

<div class="border-b border-line bg-white">
    <div class="mx-auto max-w-7xl px-4 py-3 sm:px-6">
        <a href="{{ $href }}" class="inline-flex min-h-11 items-center gap-1.5 text-sm font-medium text-brand-600 transition hover:text-brand-700 focus-visible:rounded focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2">
            <span aria-hidden="true">←</span> {{ $label }}
        </a>
    </div>
</div>
