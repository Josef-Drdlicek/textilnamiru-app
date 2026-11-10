<x-layouts.app title="Přehled zakázek">
    <div class="space-y-8">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <h1 class="text-2xl font-bold text-stone-900">Přehled zakázek</h1>

            <form method="GET" action="{{ route('admin.orders.index') }}" class="flex flex-wrap items-end gap-3 text-sm">
                <div>
                    <label for="submitted_from" class="block text-xs font-medium text-stone-500">Objednáno od</label>
                    <input id="submitted_from" name="submitted_from" type="date" value="{{ $filter['submitted_from'] ?? '' }}"
                        class="mt-1 rounded-lg border border-stone-300 px-3 py-1.5 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                </div>
                <div>
                    <label for="submitted_to" class="block text-xs font-medium text-stone-500">do</label>
                    <input id="submitted_to" name="submitted_to" type="date" value="{{ $filter['submitted_to'] ?? '' }}"
                        class="mt-1 rounded-lg border border-stone-300 px-3 py-1.5 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                </div>
                <div>
                    <label for="user_id" class="block text-xs font-medium text-stone-500">Zákazník</label>
                    <select id="user_id" name="user_id"
                        class="mt-1 rounded-lg border border-stone-300 px-3 py-1.5 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                        <option value="">Všichni</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}" @selected(($filter['user_id'] ?? null) == $customer->id)>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 font-semibold text-white transition hover:bg-teal-800">
                    Filtrovat
                </button>
                @if (($filter['submitted_from'] ?? null) || ($filter['submitted_to'] ?? null) || ($filter['user_id'] ?? null))
                    <a href="{{ route('admin.orders.index') }}" class="py-2 text-stone-500 hover:underline">Zrušit filtr</a>
                @endif
            </form>
        </div>

        @error('submitted_to')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
        @error('user_id')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
        @error('status')
            <p class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</p>
        @enderror

        @foreach ($sections as $section)
            @php $sectionOrders = $orders->get($section->value, collect()); @endphp

            <section>
                <div class="flex items-center gap-3">
                    <h2 class="text-lg font-semibold text-stone-900">
                        @switch($section)
                            @case(\App\Enums\OrderStatus::New) Nové zakázky @break
                            @case(\App\Enums\OrderStatus::Accepted) Přijaté zakázky @break
                            @case(\App\Enums\OrderStatus::ToInvoice) Zakázky k fakturaci @break
                            @case(\App\Enums\OrderStatus::Done) Hotové zakázky @break
                        @endswitch
                    </h2>
                    <span class="rounded-full bg-stone-200 px-2.5 py-0.5 text-xs font-semibold text-stone-600">
                        {{ $sectionOrders->count() }}
                    </span>
                </div>

                @if ($sectionOrders->isEmpty())
                    <p class="mt-2 rounded-xl border border-dashed border-stone-200 bg-white px-4 py-4 text-sm text-stone-500">
                        Žádné zakázky v této fázi.
                    </p>
                @else
                    <div class="mt-2 overflow-x-auto rounded-2xl border border-stone-200 bg-white shadow-sm">
                        <table class="min-w-full divide-y divide-stone-200 text-sm">
                            <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">
                                <tr>
                                    {{-- Datové sloupce se zúží na obsah a prázdné sloupce mezi nimi si rovným dílem
                                         rozdělí volné místo, takže mezery mezi sloupci i k tlačítkům stavu jsou stejné. --}}
                                    <th class="w-px whitespace-nowrap px-5 py-3">Zákazník</th>
                                    <th class="p-0" aria-hidden="true"></th>
                                    <th class="w-px whitespace-nowrap px-5 py-3">Zakázka</th>
                                    <th class="p-0" aria-hidden="true"></th>
                                    <th class="w-px whitespace-nowrap px-5 py-3">Objednáno</th>
                                    <th class="p-0" aria-hidden="true"></th>
                                    <th class="w-px whitespace-nowrap px-5 py-3">Termín</th>
                                    <th class="p-0" aria-hidden="true"></th>
                                    <th class="w-px whitespace-nowrap px-5 py-3 text-right">Cena bez DPH</th>
                                    <th class="p-0" aria-hidden="true"></th>
                                    <th class="w-px whitespace-nowrap px-5 py-3 text-right">Cena s DPH</th>
                                    <th class="p-0" aria-hidden="true"></th>
                                    <th class="w-px whitespace-nowrap px-5 py-3 text-right"><span class="sr-only">Akce</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100">
                                @foreach ($sectionOrders as $order)
                                    <tr>
                                        <td class="w-px whitespace-nowrap px-5 py-3">{{ $order->user->name }}</td>
                                        <td class="p-0" aria-hidden="true"></td>
                                        <td class="w-px whitespace-nowrap px-5 py-3">
                                            <a href="{{ route('admin.orders.show', $order) }}" class="font-medium text-teal-800 hover:underline">
                                                {{ $order->name }}
                                            </a>
                                        </td>
                                        <td class="p-0" aria-hidden="true"></td>
                                        <td class="w-px whitespace-nowrap px-5 py-3">{{ $order->submitted_at?->format('j. n. Y') }}</td>
                                        <td class="p-0" aria-hidden="true"></td>
                                        <td class="w-px whitespace-nowrap px-5 py-3">{{ $order->deadline?->format('j. n. Y') ?? '—' }}</td>
                                        <td class="p-0" aria-hidden="true"></td>
                                        <td class="w-px whitespace-nowrap px-5 py-3 text-right tabular-nums">
                                            {{ $order->total_price !== null ? number_format($order->total_price, 2, ',', ' ').' Kč' : '—' }}
                                        </td>
                                        <td class="p-0" aria-hidden="true"></td>
                                        <td class="w-px whitespace-nowrap px-5 py-3 text-right tabular-nums">
                                            @php $withVat = $order->totalPriceWithVat(); @endphp
                                            {{ $withVat !== null ? number_format($withVat, 2, ',', ' ').' Kč' : '—' }}
                                        </td>
                                        <td class="p-0" aria-hidden="true"></td>
                                        <td class="w-px whitespace-nowrap px-5 py-3">
                                            @php
                                                $transitions = collect($order->status->allowedTransitions())
                                                    ->filter(fn ($target) => $target->transitionActionLabel() !== null);
                                            @endphp
                                            <div class="flex items-center justify-end gap-2">
                                                @foreach ($transitions as $target)
                                                    <form method="POST" action="{{ route('admin.orders.status', $order) }}">
                                                        @csrf
                                                        <input type="hidden" name="status" value="{{ $target->value }}">
                                                        <button type="submit" class="rounded-lg bg-teal-700 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-teal-800">
                                                            {{ $target->transitionActionLabel() }}
                                                        </button>
                                                    </form>
                                                @endforeach

                                                {{-- Neviditelná rezerva stejně široká jako tlačítka stavu: posune je o jejich
                                                     vlastní šířku doleva, dál od tlačítka Smazat. --}}
                                                @if ($transitions->isNotEmpty())
                                                    <div class="invisible flex gap-2" aria-hidden="true">
                                                        @foreach ($transitions as $target)
                                                            <span class="rounded-lg px-3 py-1.5 text-xs font-semibold">{{ $target->transitionActionLabel() }}</span>
                                                        @endforeach
                                                    </div>
                                                @endif

                                                <form method="POST" action="{{ route('admin.orders.destroy', $order) }}"
                                                      onsubmit="return confirm('Opravdu smazat zakázku „{{ $order->name }}“?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="rounded-lg border border-red-300 px-3 py-1.5 text-xs font-semibold text-red-700 transition hover:bg-red-50">
                                                        Smazat
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        @endforeach
    </div>
</x-layouts.app>
