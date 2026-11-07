<x-layouts.app :title="'Zakázka – '.$order->name">
    <div class="space-y-8">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm text-stone-500">
                    <a href="{{ route('admin.orders.index') }}" class="hover:underline">Přehled zakázek</a>
                    <span class="mx-1">/</span>
                </p>
                <h1 class="text-2xl font-bold text-stone-900">{{ $order->name }}</h1>
            </div>

            <div class="flex items-center gap-3">
                <x-order-status-badge :status="$order->status" />

                @foreach ($order->status->allowedTransitions() as $target)
                    @if ($target->transitionActionLabel() !== null)
                        <form method="POST" action="{{ route('admin.orders.status', $order) }}">
                            @csrf
                            <input type="hidden" name="status" value="{{ $target->value }}">
                            <button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-800">
                                {{ $target->transitionActionLabel() }}
                            </button>
                        </form>
                    @endif
                @endforeach

                <form method="POST" action="{{ route('admin.orders.destroy', $order) }}"
                      onsubmit="return confirm('Opravdu smazat zakázku „{{ $order->name }}“?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-lg border border-red-300 px-4 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-50">
                        Smazat
                    </button>
                </form>
            </div>
        </div>

        @error('status')
            <p class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</p>
        @enderror

        <section class="grid gap-4 rounded-2xl border border-stone-200 bg-white p-6 text-sm shadow-sm sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <h2 class="text-xs font-semibold uppercase tracking-wide text-stone-500">Zákazník</h2>
                <p class="mt-1 font-medium text-stone-800">{{ $order->user->name }}</p>
                @if ($order->user->phone)
                    <p class="text-stone-600">{{ $order->user->phone }}</p>
                @endif
                @if ($order->user->address)
                    <p class="whitespace-pre-line text-stone-600">{{ $order->user->address }}</p>
                @endif
            </div>
            <div>
                <h2 class="text-xs font-semibold uppercase tracking-wide text-stone-500">Objednáno</h2>
                <p class="mt-1 text-stone-800">{{ $order->submitted_at?->format('j. n. Y H:i') ?? '—' }}</p>
                <h2 class="mt-3 text-xs font-semibold uppercase tracking-wide text-stone-500">Požadovaná realizace</h2>
                <p class="mt-1 text-stone-800">{{ $order->deadline?->format('j. n. Y') ?? '—' }}</p>
            </div>
            <div>
                <h2 class="text-xs font-semibold uppercase tracking-wide text-stone-500">Cena (zamrazená při objednání)</h2>
                <p class="mt-1 text-lg font-bold tabular-nums text-teal-800">
                    {{ $order->total_price !== null ? number_format($order->total_price, 2, ',', ' ').' Kč' : '—' }}
                </p>
                @if ($order->totalPriceWithVat() !== null)
                    <p class="text-stone-600">{{ number_format($order->totalPriceWithVat(), 2, ',', ' ') }} Kč vč. DPH ({{ number_format($order->vat_rate, 0) }} %)</p>
                @endif
            </div>
            <div>
                <h2 class="text-xs font-semibold uppercase tracking-wide text-stone-500">Spotřeba látky (odhad)</h2>
                @if ($consumption === [])
                    <p class="mt-1 text-stone-400">Žádná položka nepočítá spotřebu.</p>
                @else
                    <ul class="mt-1 space-y-0.5 text-stone-700">
                        @foreach ($consumption as $row)
                            <li><span class="font-medium">{{ $row['material'] }}</span>: {{ number_format($row['meters'], 2, ',', ' ') }} m</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>

        @if ($order->materials_note)
            <section class="rounded-2xl border border-stone-200 bg-white p-6 text-sm shadow-sm">
                <h2 class="text-xs font-semibold uppercase tracking-wide text-stone-500">Dodaný materiál (soupis zákazníka)</h2>
                <p class="mt-2 whitespace-pre-line text-stone-700">{{ $order->materials_note }}</p>
            </section>
        @endif

        <section>
            <h2 class="text-lg font-semibold text-stone-900">Položky ({{ $order->items->count() }})</h2>
            <div class="mt-3 space-y-4">
                @foreach ($order->items as $item)
                    <article class="grid gap-4 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm lg:grid-cols-[1fr_220px]">
                        <div>
                            <header class="flex flex-wrap items-baseline justify-between gap-2">
                                <h3 class="font-semibold text-stone-900">
                                    {{ $loop->iteration }}. {{ $item->type->label() }}
                                    @if ($item->room)
                                        <span class="font-normal text-stone-400">· {{ $item->room }}</span>
                                    @endif
                                </h3>
                                <p class="text-sm tabular-nums text-stone-600">
                                    {{ $item->count }} ks × {{ number_format($item->unit_price, 2, ',', ' ') }} Kč
                                    = <span class="font-semibold text-stone-800">{{ number_format($item->total_price, 2, ',', ' ') }} Kč</span>
                                </p>
                            </header>

                            <dl class="mt-3 grid grid-cols-2 gap-x-6 gap-y-1 text-sm text-stone-600 sm:grid-cols-3">
                                <div class="col-span-2 sm:col-span-3">
                                    <dt class="inline font-medium text-stone-500">Materiál:</dt>
                                    <dd class="inline">{{ $item->material }}</dd>
                                </div>
                                @foreach (app(\App\Support\ItemParameterPresenter::class)->rows($item->parameters->toArray()) as $row)
                                    <div>
                                        <dt class="inline font-medium text-stone-500">{{ $row['label'] }}:</dt>
                                        <dd class="inline">{{ $row['value'] }}</dd>
                                    </div>
                                @endforeach
                            </dl>

                            @if ($item->notes)
                                <p class="mt-2 rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-900">
                                    <span class="font-medium">Poznámka:</span> {{ $item->notes }}
                                </p>
                            @endif

                            <details class="mt-2 text-sm">
                                <summary class="cursor-pointer text-teal-700 hover:underline">Rozpad ceny</summary>
                                <ul class="mt-1 space-y-0.5 border-l-2 border-teal-100 pl-3 text-stone-600">
                                    @foreach ($item->price_breakdown as $line)
                                        <li class="flex justify-between gap-3">
                                            <span>{{ $line['label'] }}</span>
                                            <span class="tabular-nums">{{ number_format($line['amount'], 2, ',', ' ') }} Kč</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </details>
                        </div>

                        <a href="{{ route('items.schema', $item) }}" target="_blank" class="block rounded-xl border border-stone-100 bg-stone-50 p-2 transition hover:border-teal-300" title="Otevřít nákres">
                            <img src="{{ route('items.schema', $item) }}" alt="Nákres položky {{ $item->type->label() }}" class="h-full w-full object-contain">
                        </a>
                    </article>
                @endforeach
            </div>
        </section>

        @if ($order->statusChanges->isNotEmpty())
            <section class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="text-xs font-semibold uppercase tracking-wide text-stone-500">Historie stavů</h2>
                <ol class="mt-3 space-y-2 text-sm">
                    @foreach ($order->statusChanges as $change)
                        <li class="flex flex-wrap items-center gap-2 text-stone-600">
                            <span class="tabular-nums text-stone-400">{{ $change->created_at->format('j. n. Y H:i') }}</span>
                            <span>
                                {{ $change->from_status !== null ? $change->from_status->label().' →' : '' }}
                                <span class="font-medium text-stone-800">{{ $change->to_status->label() }}</span>
                            </span>
                            <span class="text-stone-400">({{ $change->user->name }})</span>
                        </li>
                    @endforeach
                </ol>
            </section>
        @endif
    </div>
</x-layouts.app>
