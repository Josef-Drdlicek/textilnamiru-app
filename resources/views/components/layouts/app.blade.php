<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ isset($title) ? $title.' – '.config('app.name') : config('app.name') }}</title>

    @fonts

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50 font-sans text-stone-800 antialiased">
    @php
        $isAdmin = auth()->check() && auth()->user()->isAdmin();

        // Rozpracovaná zakázka (konfigurátor, detail) patří pod Cenové nabídky, odeslaná pod Moje zakázky.
        $routeOrder = request()->route('order');
        $onQuote = request()->routeIs('orders.quotes')
            || ($routeOrder instanceof \App\Models\Order && $routeOrder->isDraft() && request()->routeIs('orders.show', 'orders.materials', 'items.*'));

        // Levé svislé menu – pořadí shora dolů. `active` = je položka právě otevřená.
        $menu = $isAdmin
            ? [
                ['label' => 'Zakázky', 'route' => 'admin.orders.index', 'active' => request()->routeIs('admin.orders.*')],
                ['label' => 'Poptávky', 'route' => 'admin.inquiries.index', 'active' => request()->routeIs('admin.inquiries.*'), 'badge' => $newInquiriesCount ?? 0],
                ['label' => 'Ceník šití', 'route' => 'admin.pricing.index', 'active' => request()->routeIs('admin.pricing.*')],
                ['label' => 'Ceník stropů', 'route' => 'admin.ceiling-pricing.index', 'active' => request()->routeIs('admin.ceiling-pricing.*')],
                ['label' => 'Ceník rolet', 'route' => 'admin.blind-pricing.index', 'active' => request()->routeIs('admin.blind-pricing.*')],
                ['label' => 'Uživatelé', 'route' => 'admin.users.index', 'active' => request()->routeIs('admin.users.*')],
            ]
            : [
                ['label' => 'Moje zakázky', 'route' => 'orders.welcome', 'active' => ! $onQuote && request()->routeIs('orders.*', 'items.*')],
                ['label' => 'Cenové nabídky', 'route' => 'orders.quotes', 'active' => $onQuote],
                ['label' => 'Profil', 'route' => 'profile.edit', 'active' => request()->routeIs('profile.*')],
            ];
    @endphp

    @auth
        {{-- Přihlášený uživatel (dílna i studio): svislé menu u levého okraje stránky, vpravo obsah.
             Na telefonu (pod md) je menu kvůli místu nad obsahem. --}}
        <div class="flex min-h-screen flex-col md:flex-row">
            <aside class="border-b border-stone-200 bg-white md:w-60 md:shrink-0 md:border-r md:border-b-0">
                <div class="flex flex-col gap-4 px-4 py-4 md:sticky md:top-0 md:h-screen md:py-6">
                    <a href="{{ route('home') }}" class="px-3 text-lg font-bold tracking-tight text-teal-800">
                        {{ config('app.name') }}
                    </a>

                    <nav aria-label="{{ $isAdmin ? 'Administrace' : 'Hlavní menu' }}" class="md:flex-1">
                        <ul class="flex flex-wrap gap-1 md:flex-col md:flex-nowrap">
                            @foreach ($menu as $item)
                                @php $active = $item['active']; @endphp
                                <li>
                                    <a
                                        href="{{ route($item['route']) }}"
                                        class="flex min-h-11 items-center justify-between gap-2 rounded-lg px-3 text-sm font-medium transition {{ $active ? 'bg-teal-50 text-teal-800' : 'text-stone-600 hover:bg-stone-100 hover:text-teal-800' }}"
                                        @if ($active) aria-current="page" @endif
                                    >
                                        {{ $item['label'] }}
                                        @if (($item['badge'] ?? 0) > 0)
                                            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">{{ $item['badge'] }}</span>
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </nav>

                    <div class="flex items-center justify-between gap-3 px-3 text-sm md:flex-col md:items-start md:border-t md:border-stone-200 md:pt-4">
                        <span class="text-stone-500">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="rounded-lg border border-stone-300 px-3 py-1.5 font-medium text-stone-600 transition hover:bg-stone-100">
                                Odhlásit se
                            </button>
                        </form>
                    </div>
                </div>
            </aside>

            <div class="flex min-w-0 flex-1 flex-col">
                <main class="w-full flex-1 px-4 py-8 lg:px-8">
                    @if (session('status'))
                        <div class="mb-6 rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-900" role="status">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{ $slot }}
                </main>

                <footer class="border-t border-stone-200 py-4 text-center text-xs text-stone-500">
                    {{ config('app.name') }} — interní aplikace pro zadávání zakázek
                </footer>
            </div>
        </div>
    @else
        {{-- Nepřihlášený (např. přihlašovací stránka): jen obsah bez menu. --}}
        <div class="flex min-h-screen flex-col">
            <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-8">
                @if (session('status'))
                    <div class="mb-6 rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-900" role="status">
                        {{ session('status') }}
                    </div>
                @endif

                {{ $slot }}
            </main>

            <footer class="border-t border-stone-200 py-4 text-center text-xs text-stone-500">
                {{ config('app.name') }} — interní aplikace pro zadávání zakázek
            </footer>
        </div>
    @endauth
</body>
</html>
