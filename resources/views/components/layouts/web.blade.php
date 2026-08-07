@props([
    'title' => null,
    'description' => 'Zakázková výroba bytového textilu od roku 1992. Záclony, závěsy, polštáře, sedáky a římské rolety šité na míru pro domácnosti, hotely i firmy. Boskovice.',
])
<!DOCTYPE html>
<html lang="cs" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title.' – Textil na míru' : 'Textil na míru | Zakázková výroba bytového textilu od roku 1992' }}</title>
    <meta name="description" content="{{ $description }}">

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
    {
        "@@context": "https://schema.org",
        "@@type": "LocalBusiness",
        "name": "Textil na míru",
        "description": "Rodinná šicí dílna specializující se na zakázkovou výrobu bytového textilu od roku 1992.",
        "url": "{{ route('web.home') }}",
        "telephone": "+420 516 453 000",
        "address": {
            "@@type": "PostalAddress",
            "streetAddress": "Masarykovo náměstí 49",
            "addressLocality": "Boskovice",
            "postalCode": "680 01",
            "addressCountry": "CZ"
        },
        "geo": {
            "@@type": "GeoCoordinates",
            "latitude": 49.4875,
            "longitude": 16.6603
        },
        "openingHours": "Mo-Fr 08:00-17:00",
        "priceRange": "$$"
    }
    </script>
</head>
<body class="min-h-screen bg-paper font-sans text-ink antialiased">
    <a href="#obsah" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:shadow-md">
        Přeskočit na hlavní obsah
    </a>

    <header class="sticky top-0 z-40 border-b border-line bg-white/95 shadow-sm backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
            <a href="{{ route('web.home') }}" class="flex items-center gap-2.5" aria-label="Textil na míru — domovská stránka">
                <x-web.logo class="h-9 w-9 text-brand-500" />
                <span class="text-lg leading-tight tracking-tight">
                    <span class="font-extrabold">Textil</span>
                    <span class="font-normal text-muted">na míru</span>
                </span>
            </a>

            <button
                type="button"
                class="rounded-lg p-2 text-ink hover:bg-surface-alt md:hidden"
                data-nav-toggle
                aria-expanded="false"
                aria-controls="hlavni-navigace"
                aria-label="Otevřít menu"
            >
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <path d="M4 7h16M4 12h16M4 17h16"/>
                </svg>
            </button>

            <nav
                id="hlavni-navigace"
                data-nav-menu
                class="absolute inset-x-0 top-full hidden border-b border-line bg-white px-4 pb-4 md:static md:block md:border-0 md:bg-transparent md:p-0"
                aria-label="Hlavní navigace"
            >
                <ul class="flex flex-col gap-1 pt-2 md:flex-row md:items-center md:gap-6 md:pt-0">
                    <li><a href="{{ route('web.home') }}#sortiment" class="block rounded-lg px-3 py-2 font-medium text-muted transition hover:text-ink md:p-0">Sortiment</a></li>
                    <li><a href="{{ route('web.home') }}#kalkulacka" class="block rounded-lg px-3 py-2 font-medium text-muted transition hover:text-ink md:p-0">Kalkulačka</a></li>
                    <li><a href="{{ route('web.home') }}#realizace" class="block rounded-lg px-3 py-2 font-medium text-muted transition hover:text-ink md:p-0">Realizace</a></li>
                    <li><a href="{{ route('web.home') }}#o-nas" class="block rounded-lg px-3 py-2 font-medium text-muted transition hover:text-ink md:p-0">O nás</a></li>
                    <li class="pt-2 md:pt-0">
                        <a href="{{ route('web.inquiry.create') }}" class="btn-primary !px-5 !py-2.5 text-sm">Nezávazná poptávka</a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <main id="obsah">
        {{ $slot }}
    </main>

    <footer id="kontakt" class="bg-ink text-white">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6">
            <div class="grid gap-10 md:grid-cols-4">
                <div>
                    <a href="{{ route('web.home') }}" class="flex items-center gap-2.5" aria-label="Textil na míru">
                        <x-web.logo class="h-9 w-9 text-brand-400" />
                        <span class="text-lg leading-tight tracking-tight">
                            <span class="font-extrabold">Textil</span>
                            <span class="font-normal text-white/70">na míru</span>
                        </span>
                    </a>
                    <p class="mt-3 text-white/70">Zaměříme, ušijeme, pověsíme.</p>
                </div>

                <div>
                    <h2 class="text-sm font-semibold uppercase tracking-[0.08em] text-white/60">Kontakt</h2>
                    <address class="mt-3 space-y-2 not-italic text-white/85">
                        <p>Masarykovo náměstí 49<br>680 01 Boskovice</p>
                        <p>
                            <a href="tel:+420516453000" class="underline decoration-white/30 underline-offset-4 transition hover:decoration-brand-400">+420 516 453 000</a><br>
                            <a href="mailto:info@textilnamiru.cz" class="underline decoration-white/30 underline-offset-4 transition hover:decoration-brand-400">info@textilnamiru.cz</a>
                        </p>
                    </address>
                </div>

                <div>
                    <h2 class="text-sm font-semibold uppercase tracking-[0.08em] text-white/60">Otevírací doba</h2>
                    <dl class="mt-3 space-y-1 text-white/85">
                        <div class="flex justify-between gap-4"><dt>Pondělí – Pátek</dt><dd>8:00 – 17:00</dd></div>
                        <div class="flex justify-between gap-4"><dt>Sobota</dt><dd>Po domluvě</dd></div>
                        <div class="flex justify-between gap-4"><dt>Neděle</dt><dd>Zavřeno</dd></div>
                    </dl>
                </div>

                <div>
                    <h2 class="text-sm font-semibold uppercase tracking-[0.08em] text-white/60">Rychlé odkazy</h2>
                    <ul class="mt-3 space-y-2 text-white/85">
                        <li><a href="{{ route('web.home') }}#sortiment" class="transition hover:text-white">Sortiment</a></li>
                        <li><a href="{{ route('web.home') }}#kalkulacka" class="transition hover:text-white">Kalkulačka</a></li>
                        <li><a href="{{ route('web.home') }}#realizace" class="transition hover:text-white">Realizace</a></li>
                        <li><a href="{{ route('web.inquiry.create') }}" class="font-semibold text-brand-400 transition hover:text-brand-300">Nezávazná poptávka</a></li>
                    </ul>
                </div>
            </div>

            <div class="mt-12 flex flex-col items-start justify-between gap-3 border-t border-white/15 pt-6 text-sm text-white/60 sm:flex-row">
                <p>© {{ date('Y') }} Textil na míru. Všechna práva vyhrazena.</p>
                <a href="{{ route('web.privacy') }}" class="transition hover:text-white">Ochrana osobních údajů</a>
            </div>
        </div>
    </footer>
</body>
</html>
