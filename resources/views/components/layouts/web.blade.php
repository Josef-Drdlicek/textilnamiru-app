@props([
    'title' => null,
    'description' => 'Zakázková výroba bytového textilu od roku 1992. Záclony, závěsy, polštáře, sedáky a římské rolety šité na míru pro domácnosti, hotely i firmy. Boskovice.',
    'robots' => null,
])
<!DOCTYPE html>
<html lang="cs" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title.' – Textil na míru' : 'Textil na míru | Zakázková výroba bytového textilu od roku 1992' }}</title>
    <meta name="description" content="{{ $description }}">
    @if ($robots)
        <meta name="robots" content="{{ $robots }}">
    @endif

    <meta property="og:title" content="{{ $title ?? 'Textil na míru | Zakázková výroba bytového textilu' }}">
    <meta property="og:description" content="Rodinná šicí dílna s tradicí od roku 1992. Záclony, závěsy a stínicí technika šitá na míru pro váš interiér.">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="cs_CZ">
    <meta property="og:url" content="{{ url()->current() }}">
    @if (file_exists(public_path('images/web/og.jpg')))
        <meta property="og:image" content="{{ asset('images/web/og.jpg') }}">
    @endif

    <link rel="canonical" href="{{ url()->current() }}">

    @fonts

    @vite(['resources/css/app.css', 'resources/js/web.js'])

    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'LocalBusiness',
        'name' => config('company.name'),
        'description' => 'Rodinná šicí dílna specializující se na zakázkovou výrobu bytového textilu od roku 1992.',
        'url' => route('web.home'),
        'telephone' => config('company.phone_display'),
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => config('company.street'),
            'addressLocality' => config('company.city'),
            'postalCode' => config('company.zip'),
            'addressCountry' => 'CZ',
        ],
        'geo' => ['@type' => 'GeoCoordinates', 'latitude' => config('company.geo.lat'), 'longitude' => config('company.geo.lng')],
        'openingHours' => config('company.opening_hours_schema'),
        'priceRange' => '$$',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
</head>
<body class="min-h-screen bg-paper font-sans text-ink antialiased">
    <a href="#obsah" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:shadow-md">
        Přeskočit na hlavní obsah
    </a>

    <header class="sticky top-0 z-40 border-b border-line bg-white/95 shadow-sm backdrop-blur">
        {{-- Od xl: menu uprostřed mezi logem a tlačítky. Pod xl hamburger, ať se sedm položek neláme. --}}
        <div class="mx-auto flex max-w-7xl items-center gap-3 px-4 py-3.5 sm:px-6">
            <a href="{{ route('web.home') }}" class="flex min-w-0 shrink items-center gap-3" aria-label="{{ config('company.name') }} — domovská stránka">
                <x-logo :size="36" class="h-9 w-9" />
                <span class="min-w-0 leading-tight tracking-tight">
                    <span class="block text-lg">
                        <span class="font-extrabold">Textil</span>
                        <span class="font-normal text-muted">na míru</span>
                    </span>
                    <span class="mt-0.5 hidden text-xs font-normal text-muted sm:block">Dáváme interiérům nový rozměr</span>
                </span>
            </a>

            @php
                $menu = [
                    ['route' => 'web.vypinany-podhled', 'label' => 'Vypínaný podhled', 'active' => ['web.vypinany-podhled']],
                    ['route' => 'web.stinici-technika', 'label' => 'Stínicí technika', 'active' => ['web.stinici-technika']],
                    ['route' => 'web.sortiment', 'label' => 'Sortiment', 'active' => ['web.sortiment']],
                    ['route' => 'web.kalkulacka', 'label' => 'Kalkulačka', 'active' => ['web.kalkulacka', 'web.kalkulacka.*']],
                    ['route' => 'web.realizace', 'label' => 'Realizace', 'active' => ['web.realizace']],
                ];
            @endphp

            <nav
                id="hlavni-navigace"
                data-nav-menu
                class="absolute inset-x-0 top-full hidden border-b border-line bg-white px-4 pb-4 shadow-md xl:static xl:block xl:flex-1 xl:border-0 xl:bg-transparent xl:p-0 xl:shadow-none"
                aria-label="Hlavní navigace"
            >
                <ul class="flex flex-col gap-1 pt-2 xl:flex-row xl:items-center xl:justify-center xl:gap-5 xl:pt-0">
                    @foreach ($menu as $item)
                        @php $active = request()->routeIs(...$item['active']); @endphp
                        <li>
                            <a
                                href="{{ route($item['route']) }}"
                                class="block rounded-lg px-3 py-2 font-medium transition hover:text-ink xl:p-0 {{ $active ? 'text-ink' : 'text-muted' }}"
                                @if ($active) aria-current="page" @endif
                            >{{ $item['label'] }}</a>
                        </li>
                    @endforeach
                    <li><a href="{{ route('web.home') }}#o-nas" class="block rounded-lg px-3 py-2 font-medium text-muted transition hover:text-ink xl:p-0">O nás</a></li>
                    <li class="pt-2 sm:hidden">
                        <a href="{{ route('web.inquiry.create') }}" class="btn-primary btn-sm w-full">Nezávazná poptávka</a>
                    </li>
                    <li class="xl:hidden">
                        <a href="{{ route('login') }}" class="block rounded-lg px-3 py-2 font-semibold text-muted transition hover:text-ink">Přihlášení pro studia (B2B)</a>
                    </li>
                </ul>
            </nav>

            <div class="ml-auto flex shrink-0 items-center gap-2 xl:ml-0">
                <a href="{{ route('web.inquiry.create') }}" class="btn-primary btn-sm hidden sm:inline-flex">Nezávazná poptávka</a>
                <a
                    href="{{ route('login') }}"
                    class="btn-secondary btn-sm hidden font-extrabold tracking-wide xl:inline-flex"
                    title="Přihlášení pro interiérová studia"
                    @if (request()->routeIs('login')) aria-current="page" @endif
                >B2B</a>
                <button
                    type="button"
                    class="rounded-lg p-2 text-ink hover:bg-surface-alt xl:hidden"
                    data-nav-toggle
                    aria-expanded="false"
                    aria-controls="hlavni-navigace"
                    aria-label="Otevřít menu"
                >
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                        <path d="M4 7h16M4 12h16M4 17h16"/>
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <main id="obsah">
        {{ $slot }}
    </main>

    <footer id="kontakt" class="bg-ink text-white">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6">
            <div class="grid gap-10 md:grid-cols-4">
                <div>
                    <a href="{{ route('web.home') }}" class="flex items-center gap-2.5" aria-label="{{ config('company.name') }}">
                        <x-logo :size="32" class="h-8 w-8" />
                        <span class="text-lg leading-tight tracking-tight">
                            <span class="font-extrabold">Textil</span>
                            <span class="font-normal text-white/70">na míru</span>
                        </span>
                    </a>
                    <p class="mt-3 text-white/70">{{ config('company.claim') }}</p>
                </div>

                <div>
                    <h2 class="text-sm font-semibold uppercase tracking-[0.08em] text-white/60">Kontakt</h2>
                    <address class="mt-3 space-y-2 not-italic text-white/85">
                        <p>{{ config('company.street') }}<br>{{ config('company.zip') }} {{ config('company.city') }}</p>
                        <p>
                            <a href="tel:{{ config('company.phone') }}" class="underline decoration-white/30 underline-offset-4 transition hover:decoration-brand-400">{{ config('company.phone_display') }}</a><br>
                            <a href="mailto:{{ config('company.email') }}" class="underline decoration-white/30 underline-offset-4 transition hover:decoration-brand-400">{{ config('company.email') }}</a>
                        </p>
                    </address>
                </div>

                <div>
                    <h2 class="text-sm font-semibold uppercase tracking-[0.08em] text-white/60">Otevírací doba</h2>
                    <dl class="mt-3 space-y-1 text-white/85">
                        @foreach (config('company.hours') as [$days, $time])
                            <div class="flex justify-between gap-4"><dt>{{ $days }}</dt><dd>{{ $time }}</dd></div>
                        @endforeach
                    </dl>
                </div>

                <div>
                    <h2 class="text-sm font-semibold uppercase tracking-[0.08em] text-white/60">Rychlé odkazy</h2>
                    <ul class="mt-3 space-y-2 text-white/85">
                        <li><a href="{{ route('web.vypinany-podhled') }}" class="transition hover:text-white">Vypínaný podhled</a></li>
                        <li><a href="{{ route('web.stinici-technika') }}" class="transition hover:text-white">Stínicí technika</a></li>
                        <li><a href="{{ route('web.sortiment') }}" class="transition hover:text-white">Sortiment</a></li>
                        <li><a href="{{ route('web.kalkulacka') }}" class="transition hover:text-white">Kalkulačka</a></li>
                        <li><a href="{{ route('web.realizace') }}" class="transition hover:text-white">Realizace</a></li>
                        <li><a href="{{ route('web.inquiry.create') }}" class="font-semibold text-brand-400 transition hover:text-brand-300">Nezávazná poptávka</a></li>
                        <li><a href="{{ route('login') }}" class="text-white/60 transition hover:text-white">Přihlášení pro studia (B2B)</a></li>
                    </ul>
                </div>
            </div>

            <div class="mt-12 flex flex-col items-start justify-between gap-3 border-t border-white/15 pt-6 text-sm text-white/60 sm:flex-row">
                <p>© {{ date('Y') }} {{ config('company.name') }}. Všechna práva vyhrazena.</p>
                <a href="{{ route('web.privacy') }}" class="transition hover:text-white">Ochrana osobních údajů</a>
            </div>
        </div>
    </footer>
</body>
</html>
