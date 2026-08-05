@props(['status'])

@php
    $classes = match ($status) {
        \App\Enums\OrderStatus::Draft => 'bg-stone-100 text-stone-700',
        \App\Enums\OrderStatus::New => 'bg-amber-100 text-amber-800',
        \App\Enums\OrderStatus::Accepted => 'bg-sky-100 text-sky-800',
        \App\Enums\OrderStatus::ToInvoice => 'bg-violet-100 text-violet-800',
        \App\Enums\OrderStatus::Done => 'bg-emerald-100 text-emerald-800',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {$classes}"]) }}>
    {{ $status->label() }}
</span>
