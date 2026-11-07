<x-layouts.app title="Poptávky z webu">
    <div class="space-y-8">
        <h1 class="text-2xl font-bold text-stone-900">Poptávky z webu</h1>

        @foreach ($sections as $section)
            @php $sectionInquiries = $inquiries->get($section->value, collect()); @endphp

            <section>
                <div class="flex items-center gap-3">
                    <h2 class="text-lg font-semibold text-stone-900">
                        @switch($section)
                            @case(\App\Enums\InquiryStatus::New) Nové poptávky @break
                            @case(\App\Enums\InquiryStatus::Contacted) Kontaktované @break
                            @case(\App\Enums\InquiryStatus::Resolved) Vyřízené @break
                            @case(\App\Enums\InquiryStatus::Rejected) Zamítnuté @break
                        @endswitch
                    </h2>
                    <span class="rounded-full bg-stone-200 px-2.5 py-0.5 text-xs font-semibold text-stone-600">
                        {{ $sectionInquiries->count() }}
                    </span>
                </div>

                @if ($sectionInquiries->isEmpty())
                    <p class="mt-2 rounded-xl border border-dashed border-stone-200 bg-white px-4 py-4 text-sm text-stone-500">
                        Žádné poptávky v této fázi.
                    </p>
                @else
                    <div class="mt-2 overflow-x-auto rounded-2xl border border-stone-200 bg-white shadow-sm">
                        <table class="min-w-full divide-y divide-stone-200 text-sm">
                            <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">
                                <tr>
                                    <th class="px-5 py-3">Zákazník</th>
                                    <th class="px-5 py-3">Kontakt</th>
                                    <th class="px-5 py-3">Zájem</th>
                                    <th class="px-5 py-3">Konfigurace</th>
                                    <th class="px-5 py-3">Přijato</th>
                                    <th class="px-5 py-3">Zakázka</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100">
                                @foreach ($sectionInquiries as $inquiry)
                                    <tr>
                                        <td class="px-5 py-3">
                                            <a href="{{ route('admin.inquiries.show', $inquiry) }}" class="font-medium text-teal-800 hover:underline">
                                                {{ $inquiry->name }}
                                            </a>
                                            @if ($inquiry->city)
                                                <span class="block text-xs text-stone-500">{{ $inquiry->city }}</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3">
                                            {{ $inquiry->phone }}<br>
                                            <span class="text-stone-500">{{ $inquiry->email }}</span>
                                        </td>
                                        <td class="px-5 py-3">
                                            {{ $inquiry->products?->map(fn ($product) => $product->label())->implode(', ') ?: '—' }}
                                        </td>
                                        <td class="px-5 py-3">
                                            {{ $inquiry->configuration['summary'] ?? '—' }}
                                        </td>
                                        <td class="px-5 py-3">{{ $inquiry->created_at->format('j. n. Y H:i') }}</td>
                                        <td class="px-5 py-3">
                                            @if ($inquiry->order)
                                                <a href="{{ route('admin.orders.show', $inquiry->order) }}" class="font-medium text-teal-800 hover:underline">
                                                    {{ $inquiry->order->name }}
                                                </a>
                                            @else
                                                —
                                            @endif
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
