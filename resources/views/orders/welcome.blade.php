<x-layouts.app title="Moje zakázky">
    <div class="mx-auto max-w-4xl space-y-10">
        <section class="rounded-2xl border border-stone-200 bg-white p-8 shadow-sm">
            <h1 class="text-xl font-bold text-stone-900">Nová zakázka</h1>
            <p class="mt-1 text-sm text-stone-500">
                Pojmenujte zakázku, ať ji později snadno poznáte — třeba „Novákovi – obývák“.
                Dokud ji neodešlete, najdete ji mezi <a href="{{ route('orders.quotes') }}" class="font-medium text-teal-800 hover:underline">cenovými nabídkami</a>.
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

        <section>
            <h2 class="text-lg font-semibold text-stone-900">Zakázky</h2>

            @if ($submitted->isEmpty())
                <p class="mt-3 rounded-2xl border border-dashed border-stone-300 bg-white px-5 py-8 text-center text-sm text-stone-500">
                    Zatím nemáte žádné odeslané zakázky.
                </p>
            @else
                <div class="mt-3 overflow-x-auto rounded-2xl border border-stone-200 bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-stone-200 text-sm">
                        <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">
                            <tr>
                                <th class="px-5 py-3">Název zakázky</th>
                                <th class="px-5 py-3">Objednáno</th>
                                <th class="px-5 py-3 text-right">Cena bez DPH</th>
                                <th class="px-5 py-3">Stav</th>
                                <th class="px-5 py-3"><span class="sr-only">Akce</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @foreach ($submitted as $order)
                                <tr>
                                    <td class="px-5 py-3 font-medium text-stone-800">
                                        <a href="{{ route('orders.show', $order) }}" class="text-teal-800 hover:underline">{{ $order->name }}</a>
                                    </td>
                                    <td class="px-5 py-3 text-stone-600">{{ $order->submitted_at?->format('j. n. Y') }}</td>
                                    <td class="px-5 py-3 text-right tabular-nums">
                                        {{ $order->total_price !== null ? number_format($order->total_price, 2, ',', ' ').' Kč' : '—' }}
                                    </td>
                                    <td class="px-5 py-3"><x-order-status-badge :status="$order->status" /></td>
                                    <td class="whitespace-nowrap px-5 py-3 text-right">
                                        <a href="{{ route('orders.show', $order) }}" class="font-medium text-teal-800 hover:underline">Detail</a>
                                        @can('delete', $order)
                                            <form method="POST" action="{{ route('orders.destroy', $order) }}" class="ml-3 inline"
                                                  onsubmit="return confirm('Opravdu smazat hotovou zakázku ze svého přehledu?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="font-medium text-red-700 hover:underline">Smazat</button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
</x-layouts.app>
