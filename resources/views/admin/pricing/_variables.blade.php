{{-- Tabulka řádků ceníku s hromadným uložením a mazáním vlastních řádků.
     Sdílí ji ceník šití i stropů; `$category` určuje, kam se po uložení vrátí. --}}
<form method="POST" action="{{ route('admin.pricing.variables.update') }}" class="mt-3">
    @csrf
    @method('PUT')
    <input type="hidden" name="category" value="{{ $category->value }}">

    <div class="overflow-x-auto rounded-2xl border border-stone-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-stone-200 text-sm">
            <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">
                <tr>
                    <th class="px-5 py-3">Položka</th>
                    <th class="px-5 py-3">Kód</th>
                    <th class="px-5 py-3">Jednotka</th>
                    <th class="px-5 py-3 text-right">Hodnota</th>
                    <th class="px-5 py-3"><span class="sr-only">Akce</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($variables as $variable)
                    <tr>
                        <td class="px-5 py-2.5 font-medium text-stone-800">{{ $variable->name }}</td>
                        <td class="px-5 py-2.5 font-mono text-xs text-stone-500">{{ $variable->code }}</td>
                        <td class="px-5 py-2.5 text-stone-500">{{ $variable->unit ?? '—' }}</td>
                        <td class="px-5 py-2.5 text-right">
                            <input
                                type="number"
                                name="values[{{ $variable->code }}]"
                                value="{{ old('values.'.$variable->code, rtrim(rtrim(number_format($variable->value, 2, '.', ''), '0'), '.')) }}"
                                step="0.01"
                                min="0"
                                required
                                class="w-28 rounded-lg border border-stone-300 px-2 py-1 text-right tabular-nums shadow-sm focus:border-teal-500 focus:ring-teal-500"
                            >
                            <span class="ml-1 text-xs text-stone-500">{{ $variable->valueSuffix() }}</span>
                        </td>
                        <td class="px-5 py-2.5 text-right">
                            @unless ($variable->is_system)
                                <button type="submit" form="delete-variable-{{ $variable->id }}"
                                    class="text-sm font-medium text-red-700 hover:underline"
                                    onclick="return confirm('Opravdu smazat tuto položku ceníku?')">
                                    Smazat
                                </button>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-6 text-center text-stone-500">Ceník zatím nemá žádné položky.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @error('values.*')
        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
    @enderror

    <button type="submit" class="mt-3 rounded-lg bg-teal-700 px-5 py-2 font-semibold text-white transition hover:bg-teal-800">
        Uložit ceník
    </button>
</form>

{{-- Mazací formuláře stojí mimo formulář ceníku (vnořené formuláře HTML nedovoluje). --}}
@foreach ($variables->reject->is_system as $variable)
    <form id="delete-variable-{{ $variable->id }}" method="POST" action="{{ route('admin.pricing.variables.destroy', $variable) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
@endforeach
