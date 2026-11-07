{{-- Mobil: cena je vidět i při vyplňování formuláře (panel vpravo je na telefonu až pod ním). --}}
<div class="fixed inset-x-0 bottom-0 z-30 border-t border-stone-200 bg-white/95 px-4 py-3 shadow-[0_-4px_12px_rgba(0,0,0,0.06)] backdrop-blur lg:hidden" aria-live="polite">
    <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 text-sm">
        @if ($unitPrice === null)
            <span class="text-stone-500">Cena se zobrazí po doplnění povinných údajů.</span>
        @else
            <span class="text-stone-500">Celkem s DPH ({{ $count }} ks)</span>
            <span class="text-lg font-bold tabular-nums text-stone-900">{{ number_format($totalPriceVat, 2, ',', ' ') }} Kč</span>
        @endif
    </div>
</div>
<div class="h-16 lg:hidden" aria-hidden="true"></div>
