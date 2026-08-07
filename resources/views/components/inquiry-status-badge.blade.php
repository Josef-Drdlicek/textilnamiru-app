@props(['status'])

@php
    $classes = match ($status) {
        \App\Enums\InquiryStatus::New => 'bg-amber-100 text-amber-800',
        \App\Enums\InquiryStatus::Contacted => 'bg-sky-100 text-sky-800',
        \App\Enums\InquiryStatus::Resolved => 'bg-emerald-100 text-emerald-800',
        \App\Enums\InquiryStatus::Rejected => 'bg-stone-100 text-stone-700',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {$classes}"]) }}>
    {{ $status->label() }}
</span>
