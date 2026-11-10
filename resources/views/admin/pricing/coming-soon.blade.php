{{-- Zástupná stránka pro ceník, který teprve vznikne (stropy, rolety). --}}
<x-layouts.app :title="$title">
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-stone-900">{{ $title }}</h1>

        <p class="rounded-2xl border border-dashed border-stone-300 bg-white px-5 py-8 text-center text-sm text-stone-500">
            Tento ceník připravujeme. Zatím tu nic není.
        </p>
    </div>
</x-layouts.app>
