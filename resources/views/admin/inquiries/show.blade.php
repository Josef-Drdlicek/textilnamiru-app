@php
    use App\Enums\InquiryStatus;
    use App\Enums\ItemType;
@endphp

<x-layouts.app :title="'Poptávka — '.$inquiry->name">
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-stone-900">Poptávka od {{ $inquiry->name }}</h1>
                <x-inquiry-status-badge :status="$inquiry->status" />
            </div>
            <a href="{{ route('admin.inquiries.index') }}" class="text-sm font-medium text-teal-800 hover:underline">
                ← Zpět na přehled
            </a>
        </div>

        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-stone-900">Kontakt a požadavek</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-stone-500">Telefon</dt>
                        <dd class="font-medium"><a href="tel:{{ $inquiry->phone }}" class="text-teal-800 hover:underline">{{ $inquiry->phone }}</a></dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-stone-500">E-mail</dt>
                        <dd class="font-medium"><a href="mailto:{{ $inquiry->email }}" class="text-teal-800 hover:underline">{{ $inquiry->email }}</a></dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-stone-500">Město</dt>
                        <dd class="font-medium">{{ $inquiry->city ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-stone-500">Typ prostoru</dt>
                        <dd class="font-medium">{{ $inquiry->space_type?->label() ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-stone-500">Zájem o</dt>
                        <dd class="font-medium text-right">{{ $inquiry->products?->map(fn ($product) => $product->label())->implode(', ') ?: '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-stone-500">Zaměření</dt>
                        <dd class="font-medium">{{ $inquiry->measurement?->label() ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-stone-500">Přijato</dt>
                        <dd class="font-medium">{{ $inquiry->created_at->format('j. n. Y H:i') }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-stone-500">Souhlas GDPR</dt>
                        <dd class="font-medium">{{ $inquiry->gdpr_consented_at->format('j. n. Y H:i') }}</dd>
                    </div>
                </dl>

                @if ($inquiry->message)
                    <h3 class="mt-5 text-sm font-semibold text-stone-500">Zpráva zákazníka</h3>
                    <p class="mt-1 whitespace-pre-line rounded-lg bg-stone-50 p-4 text-sm">{{ $inquiry->message }}</p>
                @endif
            </section>

            <section class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-stone-900">Konfigurace z kalkulačky</h2>

                @if ($inquiry->configuration)
                    @php
                        $configuration = $inquiry->configuration;
                        $configType = ItemType::tryFrom($configuration['type'] ?? '');
                    @endphp

                    <div class="mt-4 flex items-center gap-3">
                        @if ($configType)
                            <x-item-type-icon :type="$configType" class="h-10 w-10 text-teal-700" />
                        @endif
                        <div>
                            <p class="font-semibold">{{ $configuration['summary'] ?? $configType?->label() ?? 'Konfigurace' }}</p>
                            <p class="text-sm text-stone-500">
                                Orientačně
                                <span class="font-semibold tabular-nums">{{ number_format($configuration['total_price_vat'] ?? 0, 0, ',', ' ') }} Kč</span>
                                vč. DPH ({{ number_format($configuration['vat_rate'] ?? 0, 0) }} %)
                                · spočteno {{ isset($configuration['calculated_at']) ? \Illuminate\Support\Carbon::parse($configuration['calculated_at'])->format('j. n. Y') : '—' }}
                            </p>
                        </div>
                    </div>

                    <dl class="mt-4 grid grid-cols-2 gap-x-6 gap-y-1.5 text-sm">
                        @foreach ($configuration['parameters'] ?? [] as $key => $value)
                            @continue($value === null)
                            <div class="flex justify-between gap-3 border-b border-stone-100 py-1">
                                <dt class="text-stone-500">{{ __('parameters.'.$key) }}</dt>
                                <dd class="font-medium tabular-nums">
                                    @if (is_bool($value))
                                        {{ $value ? 'ano' : 'ne' }}
                                    @elseif (is_string($value) && __('parameters.values.'.$value) !== 'parameters.values.'.$value)
                                        {{ __('parameters.values.'.$value) }}
                                    @else
                                        {{ $value }}
                                    @endif
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                @else
                    <p class="mt-4 rounded-lg border border-dashed border-stone-200 px-4 py-4 text-sm text-stone-400">
                        Zákazník kalkulačku nepoužil.
                    </p>
                @endif
            </section>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-stone-900">Stav poptávky</h2>
                <form method="POST" action="{{ route('admin.inquiries.status', $inquiry) }}" class="mt-4 flex flex-wrap items-center gap-3">
                    @csrf
                    <label for="status" class="sr-only">Stav</label>
                    <select id="status" name="status" class="rounded-lg border border-stone-300 px-3 py-2 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500">
                        @foreach (InquiryStatus::cases() as $status)
                            <option value="{{ $status->value }}" @selected($inquiry->status === $status)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-800">
                        Změnit stav
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.inquiries.destroy', $inquiry) }}" class="mt-6 border-t border-stone-100 pt-4"
                      onsubmit="return confirm('Opravdu smazat poptávku od „{{ $inquiry->name }}“? Údaje budou nenávratně odstraněny.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-lg border border-red-300 px-3 py-1.5 text-xs font-semibold text-red-700 transition hover:bg-red-50">
                        Smazat poptávku (výmaz údajů)
                    </button>
                </form>
            </section>

            <section class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-stone-900">Převzít do zakázky</h2>

                @if ($inquiry->order)
                    <p class="mt-4 text-sm">
                        Poptávka je převedena na zakázku
                        <a href="{{ route('admin.orders.show', $inquiry->order) }}" class="font-medium text-teal-800 hover:underline">{{ $inquiry->order->name }}</a>
                        ({{ $inquiry->order->user->name }}).
                    </p>
                @elseif ($designers->isEmpty())
                    <p class="mt-4 rounded-lg border border-dashed border-stone-200 px-4 py-4 text-sm text-stone-400">
                        Nejdřív založte aktivní účet designérky v sekci Uživatelé.
                    </p>
                @else
                    <form method="POST" action="{{ route('admin.inquiries.convert', $inquiry) }}" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <label for="user_id" class="block text-sm font-medium text-stone-700">Designérka</label>
                            <select id="user_id" name="user_id" class="mt-1 block w-full rounded-lg border border-stone-300 px-3 py-2 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                @foreach ($designers as $designer)
                                    <option value="{{ $designer->id }}" @selected((int) old('user_id') === $designer->id)>{{ $designer->name }}</option>
                                @endforeach
                            </select>
                            @error('user_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="name" class="block text-sm font-medium text-stone-700">Název zakázky</label>
                            <input type="text" id="name" name="name" value="{{ old('name', 'Poptávka – '.$inquiry->name) }}"
                                class="mt-1 block w-full rounded-lg border border-stone-300 px-3 py-2 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500">
                            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-800">
                            Založit rozpracovanou zakázku
                        </button>
                        <p class="text-xs text-stone-400">
                            Vznikne draft pro vybranou designérku — kontakt a případná konfigurace
                            z kalkulačky se přenesou, ceny se spočítají znovu při odeslání.
                        </p>
                    </form>
                @endif
            </section>
        </div>
    </div>
</x-layouts.app>
