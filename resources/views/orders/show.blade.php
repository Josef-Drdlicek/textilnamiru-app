<x-layouts.app :title="$order->name">
    <div class="mx-auto max-w-5xl space-y-8">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm text-stone-500">
                    <a href="{{ route('orders.welcome') }}" class="hover:underline">Moje zakázky</a>
                    <span class="mx-1">/</span>
                </p>
                <h1 class="text-2xl font-bold text-stone-900">{{ $order->name }}</h1>
            </div>
            <x-order-status-badge :status="$order->status" />
        </div>

        @if ($order->isDraft())
            <section>
                <h2 class="text-lg font-semibold text-stone-900">Přidat výrobek</h2>
                <p class="mt-1 text-sm text-stone-500">Vyberte typ výrobku — cenu uvidíte hned při vyplňování.</p>

                <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                    @foreach (\App\Enums\ItemType::cases() as $type)
                        <a
                            href="{{ route('items.create', [$order, $type]) }}"
                            class="group flex flex-col items-center gap-3 rounded-2xl border border-stone-200 bg-white p-6 text-center shadow-sm transition hover:-translate-y-0.5 hover:border-teal-600 hover:shadow-md"
                        >
                            <x-item-type-icon :type="$type" class="h-12 w-12 text-teal-700 transition group-hover:scale-110" />
                            <span class="font-semibold text-stone-800">{{ $type->label() }}</span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <section>
            <h2 class="text-lg font-semibold text-stone-900">Položky zakázky</h2>

            @if ($order->items->isEmpty())
                <p class="mt-3 rounded-2xl border border-dashed border-stone-300 bg-white px-5 py-10 text-center text-sm text-stone-500">
                    Zatím žádné položky — vyberte výrobek výše a nakonfigurujte první kus.
                </p>
            @else
                <div class="mt-3 overflow-x-auto rounded-2xl border border-stone-200 bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-stone-200 text-sm">
                        <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">
                            <tr>
                                <th class="px-5 py-3">Výrobek</th>
                                <th class="px-5 py-3">Materiál</th>
                                <th class="px-5 py-3 text-right">Kusů</th>
                                <th class="px-5 py-3 text-right">Cena/kus</th>
                                <th class="px-5 py-3 text-right">Celkem bez DPH</th>
                                @if ($order->isDraft())
                                    <th class="px-5 py-3"><span class="sr-only">Akce</span></th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @foreach ($order->items as $item)
                                <tr>
                                    <td class="px-5 py-3 font-medium text-stone-800">
                                        {{ $item->type->label() }}
                                        @if ($item->room)
                                            <span class="text-stone-400">· {{ $item->room }}</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3">{{ $item->material }}</td>
                                    <td class="px-5 py-3 text-right">{{ $item->count }}</td>
                                    <td class="px-5 py-3 text-right">{{ number_format($item->unit_price, 2, ',', ' ') }} Kč</td>
                                    <td class="px-5 py-3 text-right font-medium">{{ number_format($item->total_price, 2, ',', ' ') }} Kč</td>
                                    @if ($order->isDraft())
                                        <td class="px-5 py-3 text-right">
                                            <div class="flex items-center justify-end gap-3">
                                                <a href="{{ route('items.edit', [$order, $item]) }}" class="font-medium text-teal-700 hover:underline">Upravit</a>
                                                <form method="POST" action="{{ route('items.destroy', $item) }}" onsubmit="return confirm('Odebrat položku {{ $item->type->label() }} ze zakázky?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="font-medium text-red-600 hover:underline">Odebrat</button>
                                                </form>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-stone-50 font-semibold text-stone-800">
                            <tr>
                                <td colspan="4" class="px-5 py-3 text-right">Celkem bez DPH</td>
                                <td class="px-5 py-3 text-right">{{ number_format($order->items->sum('total_price'), 2, ',', ' ') }} Kč</td>
                                @if ($order->isDraft())
                                    <td></td>
                                @endif
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </section>

        @if ($order->isDraft())
            <section class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                <form method="POST" action="{{ route('orders.destroy', $order) }}" onsubmit="return confirm('Opravdu zrušit celou zakázku „{{ $order->name }}“ včetně všech položek?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-lg border border-red-300 px-4 py-2 text-sm font-medium text-red-700 transition hover:bg-red-50">
                        Zrušit zakázku
                    </button>
                </form>

                @if ($order->items->isNotEmpty())
                    <a href="{{ route('orders.materials', $order) }}" class="rounded-lg bg-teal-700 px-5 py-2.5 font-semibold text-white transition hover:bg-teal-800">
                        Pokračovat k objednání →
                    </a>
                @else
                    <p class="text-sm text-stone-400">Přidejte alespoň jednu položku, pak můžete objednat.</p>
                @endif
            </section>
        @else
            <section class="rounded-2xl border border-stone-200 bg-white p-5 text-sm text-stone-600 shadow-sm">
                <dl class="grid gap-3 sm:grid-cols-3">
                    <div>
                        <dt class="font-semibold text-stone-500">Objednáno</dt>
                        <dd>{{ $order->submitted_at?->format('j. n. Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-stone-500">Požadovaná realizace</dt>
                        <dd>{{ $order->deadline?->format('j. n. Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-stone-500">Celkem vč. DPH</dt>
                        <dd>{{ $order->totalPriceWithVat() !== null ? number_format($order->totalPriceWithVat(), 2, ',', ' ').' Kč' : '—' }}</dd>
                    </div>
                </dl>
                @if ($order->materials_note)
                    <div class="mt-4">
                        <dt class="font-semibold text-stone-500">Dodaný materiál</dt>
                        <dd class="whitespace-pre-line">{{ $order->materials_note }}</dd>
                    </div>
                @endif
            </section>
        @endif
    </div>
</x-layouts.app>
