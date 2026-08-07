@php
    use App\Enums\InquiryProduct;
    use App\Enums\MeasurementType;
    use App\Enums\SpaceType;

    $selectedProducts = old('products', $preselectedProducts);
    $inputClasses = 'mt-1 block w-full rounded-lg border border-line bg-white px-3 py-2.5 focus:border-brand-600 focus:ring-brand-600';
@endphp

<x-layouts.web title="Nezávazná poptávka" description="Poptejte šití bytového textilu na míru — záclony, závěsy, polštáře, sedáky i rolety. Ozveme se do 2 pracovních dnů.">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:py-16">
        <p class="eyebrow">Nezávazná poptávka</p>
        <h1 class="text-section-title mt-2">Napište nám, co potřebujete</h1>
        <p class="mt-3 max-w-2xl text-lg text-muted">Vyplňte formulář a ozveme se vám do 2 pracovních dnů.</p>

        <div class="mt-10 grid items-start gap-10 lg:grid-cols-[1fr_360px]">
            <form method="POST" action="{{ route('web.inquiry.store') }}" class="bg-white p-6 sm:p-8">
                @csrf

                {{-- Honeypot — lidé toto pole nevidí --}}
                <div class="hidden" aria-hidden="true">
                    <label for="website">Nevyplňujte</label>
                    <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                </div>
                <input type="hidden" name="_hp_time" value="{{ $honeypotTime }}">

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="name" class="block text-sm font-medium">Jméno a příjmení *</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required autocomplete="name" class="{{ $inputClasses }}">
                        @error('name') <p class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium">E-mail *</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="{{ $inputClasses }}">
                        @error('email') <p class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium">Telefon *</label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required autocomplete="tel" class="{{ $inputClasses }}">
                        @error('phone') <p class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="city" class="block text-sm font-medium">Město / obec</label>
                        <input type="text" id="city" name="city" value="{{ old('city') }}" autocomplete="address-level2" class="{{ $inputClasses }}">
                        @error('city') <p class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="space_type" class="block text-sm font-medium">Typ prostoru</label>
                        <select id="space_type" name="space_type" class="{{ $inputClasses }}">
                            <option value="">— Vyberte —</option>
                            @foreach (SpaceType::cases() as $spaceType)
                                <option value="{{ $spaceType->value }}" @selected(old('space_type') === $spaceType->value)>{{ $spaceType->label() }}</option>
                            @endforeach
                        </select>
                        @error('space_type') <p class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p> @enderror
                    </div>
                </div>

                <fieldset class="mt-7">
                    <legend class="text-sm font-medium">O co máte zájem?</legend>
                    <div class="mt-2 grid gap-2 sm:grid-cols-2">
                        @foreach (InquiryProduct::cases() as $product)
                            <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-line px-4 py-2.5 transition has-checked:border-brand-600 has-checked:bg-brand-50">
                                <input
                                    type="checkbox"
                                    name="products[]"
                                    value="{{ $product->value }}"
                                    @checked(in_array($product->value, $selectedProducts, true))
                                    class="h-5 w-5 rounded border-line text-brand-600 focus:ring-brand-600"
                                >
                                <span class="font-medium">{{ $product->label() }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('products') <p class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p> @enderror
                    @error('products.*') <p class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p> @enderror
                </fieldset>

                <fieldset class="mt-7">
                    <legend class="text-sm font-medium">Způsob zaměření *</legend>
                    <div class="mt-2 grid gap-2 sm:grid-cols-2">
                        @foreach (MeasurementType::cases() as $measurement)
                            <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-line px-4 py-2.5 transition has-checked:border-brand-600 has-checked:bg-brand-50">
                                <input
                                    type="radio"
                                    name="measurement"
                                    value="{{ $measurement->value }}"
                                    data-measurement-radio
                                    @checked(old('measurement', MeasurementType::Professional->value) === $measurement->value)
                                    class="h-5 w-5 border-line text-brand-600 focus:ring-brand-600"
                                >
                                <span class="font-medium">
                                    {{ $measurement->label() }}
                                    @if ($measurement === MeasurementType::Professional)
                                        <span class="ml-1 rounded-full bg-brand-100 px-2 py-0.5 text-xs font-semibold text-brand-700">Doporučujeme</span>
                                    @endif
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('measurement') <p class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p> @enderror

                    <div data-measurement-info class="mt-3 hidden rounded-lg border border-brand-300 bg-brand-50 p-4 text-sm" aria-live="polite">
                        <h2 class="font-bold">Jak správně zaměřit</h2>
                        <ol class="mt-2 list-decimal space-y-1 pl-5">
                            <li>Měřte vždy kovovou svinovací mírou, ne krejčovským metrem.</li>
                            <li>Zaznamenejte šířku i výšku okna včetně rámu.</li>
                            <li>U garnýží měřte vzdálenost od stropu a od stěny.</li>
                            <li>Každý rozměr změřte 2× pro kontrolu.</li>
                            <li>Fotografie okna a okolí nám pomohou s návrhem.</li>
                        </ol>
                        <p class="mt-2 text-muted">
                            <strong>Upozornění:</strong> Při zaměření svépomocí neručíme za případné
                            nesrovnalosti mezi vašimi rozměry a skutečností.
                        </p>
                    </div>
                </fieldset>

                <div class="mt-7">
                    <label for="message" class="block text-sm font-medium">Popis požadavku</label>
                    <textarea
                        id="message"
                        name="message"
                        rows="5"
                        placeholder="Počet oken, místností, preferovaný styl, barvy…"
                        class="{{ $inputClasses }}"
                    >{{ old('message') }}</textarea>
                    @error('message') <p class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p> @enderror
                </div>

                <label class="mt-7 flex cursor-pointer items-start gap-3">
                    <input type="checkbox" name="gdpr" value="1" @checked(old('gdpr')) required class="mt-0.5 h-5 w-5 rounded border-line text-brand-600 focus:ring-brand-600">
                    <span class="text-sm text-muted">
                        Souhlasím se zpracováním osobních údajů za účelem vyřízení poptávky.
                        <a href="{{ route('web.privacy') }}" class="font-medium text-brand-600 underline underline-offset-2 hover:text-brand-700" target="_blank">Jak s údaji nakládáme</a> *
                    </span>
                </label>
                @error('gdpr') <p class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p> @enderror

                <button type="submit" class="btn-primary mt-8 w-full sm:w-auto">Odeslat poptávku</button>
            </form>

            <aside class="space-y-5 lg:sticky lg:top-24">
                @if ($configuration)
                    <div class="border-2 border-brand-600 bg-white p-6">
                        <h2 class="text-sm font-semibold uppercase tracking-[0.08em] text-brand-600">Vaše konfigurace z kalkulačky</h2>
                        <p class="mt-3 font-semibold">{{ $configuration['summary'] ?? 'Konfigurace' }}</p>
                        @if (isset($configuration['total_price_vat']))
                            <p class="mt-1 text-2xl font-extrabold tabular-nums">
                                {{ number_format($configuration['total_price_vat'], 0, ',', ' ') }}&nbsp;Kč
                                <span class="text-sm font-normal text-muted">vč. DPH, orientačně</span>
                            </p>
                        @endif
                        <p class="mt-3 text-xs text-muted">Přiložíme ji k poptávce, ať víme, s čím počítáte.</p>
                        <div class="mt-4 flex gap-4 text-sm font-semibold">
                            <a href="{{ route('web.home') }}#kalkulacka" class="text-brand-600 hover:text-brand-700">Upravit</a>
                            <a href="{{ route('web.inquiry.create', ['bez-konfigurace' => 1]) }}" class="text-muted hover:text-ink">Odebrat</a>
                        </div>
                    </div>
                @endif

                <div class="bg-surface-alt p-6">
                    <h2 class="font-extrabold">Raději po telefonu?</h2>
                    <p class="mt-2 text-muted">Zavolejte nám v pracovní dny 8:00–17:00.</p>
                    <p class="mt-3">
                        <a href="tel:+420516453000" class="text-lg font-bold text-brand-600 hover:text-brand-700">+420 516 453 000</a><br>
                        <a href="mailto:info@textilnamiru.cz" class="font-medium text-brand-600 hover:text-brand-700">info@textilnamiru.cz</a>
                    </p>
                </div>
            </aside>
        </div>
    </div>
</x-layouts.web>
