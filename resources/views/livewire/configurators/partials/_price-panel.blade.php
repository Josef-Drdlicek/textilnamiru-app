<div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm" aria-live="polite">
    <h2 class="text-sm font-semibold uppercase tracking-wide text-stone-500">Cena</h2>

    @if ($unitPrice === null)
        <p class="mt-3 text-sm text-stone-500">
            Cena se zobrazí, jakmile doplníte všechny povinné údaje.
        </p>
    @else
        <dl class="mt-3 space-y-1.5 text-sm">
            <div class="flex justify-between">
                <dt class="text-stone-500">Cena za kus</dt>
                <dd class="font-medium tabular-nums">{{ number_format($unitPrice, 2, ',', ' ') }} Kč</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-stone-500">Za kus vč. DPH</dt>
                <dd class="tabular-nums">{{ number_format($unitPriceVat, 2, ',', ' ') }} Kč</dd>
            </div>
            <div class="mt-2 flex justify-between border-t border-stone-100 pt-2 text-base">
                <dt class="font-semibold text-stone-700">Celkem ({{ $count }} ks)</dt>
                <dd class="font-bold tabular-nums text-teal-800">{{ number_format($totalPrice, 2, ',', ' ') }} Kč</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-stone-500">Celkem vč. DPH</dt>
                <dd class="font-medium tabular-nums">{{ number_format($totalPriceVat, 2, ',', ' ') }} Kč</dd>
            </div>
        </dl>

        @if ($priceLines !== [])
            <details class="mt-4 text-sm">
                <summary class="cursor-pointer font-medium text-teal-700 hover:underline">Z čeho se cena skládá</summary>
                <ul class="mt-2 space-y-1 border-l-2 border-teal-100 pl-3">
                    @foreach ($priceLines as $line)
                        <li class="flex justify-between gap-2 text-stone-600">
                            <span>{{ $line['label'] }}</span>
                            <span class="tabular-nums">{{ number_format($line['amount'], 2, ',', ' ') }} Kč</span>
                        </li>
                    @endforeach
                </ul>
            </details>
        @endif
    @endif
</div>
