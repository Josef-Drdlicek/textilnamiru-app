<x-layouts.app :title="$type->label().' – '.$order->name">
    <div class="mx-auto max-w-6xl">
        <p class="text-sm text-stone-500">
            <a href="{{ route('orders.welcome') }}" class="hover:underline">Moje zakázky</a>
            <span class="mx-1">/</span>
            <a href="{{ route('orders.show', $order) }}" class="hover:underline">{{ $order->name }}</a>
            <span class="mx-1">/</span>
            {{ $type->label() }}
        </p>

        <div class="mt-2 flex items-center gap-3">
            <x-item-type-icon :type="$type" class="h-9 w-9 text-teal-700" />
            <h1 class="text-2xl font-bold text-stone-900">
                {{ $item !== null ? 'Upravit: ' : '' }}{{ $type->label() }}
            </h1>
        </div>

        <div class="mt-6">
            @livewire($type->configuratorClass(), ['order' => $order, 'item' => $item])
        </div>
    </div>
</x-layouts.app>
