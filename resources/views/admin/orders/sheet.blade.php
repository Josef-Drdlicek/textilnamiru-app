<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Výrobní list – {{ $order->name }}</title>

    @fonts

    @vite(['resources/css/app.css'])
</head>
<body class="bg-white font-sans text-stone-800 antialiased">
    <div class="mx-auto max-w-4xl px-6 py-8 print:max-w-none print:p-0">
        <div class="mb-6 flex items-center justify-between gap-3 print:hidden">
            <a href="{{ route('admin.orders.show', $order) }}" class="text-sm font-medium text-teal-800 hover:underline">← Zpět na zakázku</a>
            <button type="button" onclick="window.print()" class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">
                Tisk
            </button>
        </div>

        <header class="border-b-2 border-stone-800 pb-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-stone-500">Výrobní list</p>
            <h1 class="text-2xl font-bold">{{ $order->name }}</h1>
            <dl class="mt-3 grid grid-cols-2 gap-x-6 gap-y-1 text-sm sm:grid-cols-4">
                <div><dt class="text-stone-500">Studio</dt><dd class="font-medium">{{ $order->user->name }}</dd></div>
                <div><dt class="text-stone-500">Objednáno</dt><dd class="font-medium">{{ $order->submitted_at?->format('j. n. Y') ?? '—' }}</dd></div>
                <div><dt class="text-stone-500">Termín</dt><dd class="font-medium">{{ $order->deadline?->format('j. n. Y') ?? '—' }}</dd></div>
                <div><dt class="text-stone-500">Položek</dt><dd class="font-medium">{{ $order->items->count() }}</dd></div>
            </dl>
            @if ($order->materials_note)
                <p class="mt-3 whitespace-pre-line rounded border border-stone-200 px-3 py-2 text-sm"><span class="font-semibold">Dodaný materiál:</span> {{ $order->materials_note }}</p>
            @endif
        </header>

        @foreach ($order->items as $item)
            <article class="break-inside-avoid border-b border-stone-200 py-6">
                <h2 class="text-lg font-bold">
                    {{ $loop->iteration }}. {{ $item->type->label() }}
                    <span class="font-normal text-stone-500">· {{ $item->count }} ks · {{ $item->material }}</span>
                    @if ($item->room)
                        <span class="font-normal text-stone-500">· {{ $item->room }}</span>
                    @endif
                </h2>

                <div class="mt-3 grid gap-6 sm:grid-cols-[1fr_260px]">
                    <div>
                        <table class="w-full text-sm">
                            <tbody class="divide-y divide-stone-100">
                                @foreach ($presenter->rows($item->parameters->toArray()) as $row)
                                    <tr>
                                        <th class="py-1 pr-4 text-left font-medium text-stone-500">{{ $row['label'] }}</th>
                                        <td class="py-1 tabular-nums">{{ $row['value'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        @if ($item->notes)
                            <p class="mt-3 rounded bg-amber-50 px-3 py-2 text-sm text-amber-900">{{ $item->notes }}</p>
                        @endif

                        @if ($item->price_breakdown)
                            <table class="mt-4 w-full text-xs text-stone-600">
                                <tbody>
                                    @foreach ($item->price_breakdown as $line)
                                        <tr>
                                            <td class="py-0.5">{{ $line['label'] }}</td>
                                            <td class="py-0.5 text-right tabular-nums">{{ number_format($line['amount'], 2, ',', ' ') }} Kč</td>
                                        </tr>
                                    @endforeach
                                    <tr class="font-semibold text-stone-800">
                                        <td class="pt-1">Cena za kus bez DPH</td>
                                        <td class="pt-1 text-right tabular-nums">{{ number_format($item->unit_price, 2, ',', ' ') }} Kč</td>
                                    </tr>
                                </tbody>
                            </table>
                        @endif
                    </div>

                    {{-- Nákres generuje server z číselných parametrů, neobsahuje vstup uživatele. --}}
                    <div class="[&_svg]:h-auto [&_svg]:w-full">
                        {!! app($item->type->schemaRendererClass())->render($item->parameters) !!}
                    </div>
                </div>
            </article>
        @endforeach
    </div>
</body>
</html>
