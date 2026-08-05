<x-layouts.app title="Moje zakázky">
    <div class="mx-auto max-w-3xl space-y-10">
        <section class="rounded-2xl border border-stone-200 bg-white p-8 shadow-sm">
            <h1 class="text-xl font-bold text-stone-900">Nová zakázka</h1>
            <p class="mt-1 text-sm text-stone-500">
                Pojmenujte zakázku, ať ji později snadno poznáte — třeba „Novákovi – obývák“.
            </p>

            <form method="POST" action="{{ route('orders.store') }}" class="mt-5 flex flex-col gap-3 sm:flex-row">
                @csrf
                <div class="flex-1">
                    <label for="name" class="sr-only">Název zakázky</label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name') }}"
                        required
                        placeholder="Název zakázky"
                        class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="rounded-lg bg-teal-700 px-5 py-2.5 font-semibold text-white transition hover:bg-teal-800">
                    Založit zakázku
                </button>
            </form>
        </section>

        @if ($drafts->isNotEmpty())
            <section>
                <h2 class="text-lg font-semibold text-stone-900">Rozpracované zakázky</h2>
                <ul class="mt-3 divide-y divide-stone-200 overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
                    @foreach ($drafts as $draft)
                        <li class="flex items-center justify-between gap-4 px-5 py-4">
                            <div>
                                <a href="{{ route('orders.show', $draft) }}" class="font-medium text-teal-800 hover:underline">
                                    {{ $draft->name }}
                                </a>
                                <p class="text-sm text-stone-500">založena {{ $draft->created_at->format('j. n. Y') }}</p>
                            </div>
                            <a href="{{ route('orders.show', $draft) }}" class="rounded-lg border border-teal-700 px-4 py-2 text-sm font-medium text-teal-800 transition hover:bg-teal-50">
                                Pokračovat
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <section>
            <h2 class="text-lg font-semibold text-stone-900">Odeslané zakázky</h2>

            @if ($submitted->isEmpty())
                <p class="mt-3 rounded-2xl border border-dashed border-stone-300 bg-white px-5 py-8 text-center text-sm text-stone-500">
                    Zatím nemáte žádné odeslané zakázky.
                </p>
            @else
                <ul class="mt-3 divide-y divide-stone-200 overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
                    @foreach ($submitted as $order)
                        <li class="flex items-center justify-between gap-4 px-5 py-4">
                            <div>
                                <a href="{{ route('orders.show', $order) }}" class="font-medium text-teal-800 hover:underline">
                                    {{ $order->name }}
                                </a>
                                <p class="text-sm text-stone-500">
                                    objednána {{ $order->submitted_at?->format('j. n. Y') }}
                                    @if ($order->total_price !== null)
                                        · {{ number_format($order->total_price, 2, ',', ' ') }} Kč bez DPH
                                    @endif
                                </p>
                            </div>
                            <x-order-status-badge :status="$order->status" />
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
</x-layouts.app>
