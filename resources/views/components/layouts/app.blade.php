<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ isset($title) ? $title.' – '.config('app.name') : config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=raleway:400,500,600,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50 font-sans text-stone-800 antialiased">
    <div class="flex min-h-screen flex-col">
        @auth
            <header class="border-b border-stone-200 bg-white">
                <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3">
                    <a href="{{ route('home') }}" class="text-lg font-bold tracking-tight text-teal-800">
                        {{ config('app.name') }}
                    </a>

                    <nav class="flex items-center gap-4 text-sm">
                        @if (auth()->user()->isAdmin())
                            <a href="{{ route('admin.orders.index') }}" class="font-medium text-stone-600 hover:text-teal-800 {{ request()->routeIs('admin.orders.*') ? 'text-teal-800' : '' }}">
                                Zakázky
                            </a>
                            <a href="{{ route('admin.pricing.index') }}" class="font-medium text-stone-600 hover:text-teal-800 {{ request()->routeIs('admin.pricing.*') ? 'text-teal-800' : '' }}">
                                Ceník
                            </a>
                            <a href="{{ route('admin.users.index') }}" class="font-medium text-stone-600 hover:text-teal-800 {{ request()->routeIs('admin.users.*') ? 'text-teal-800' : '' }}">
                                Uživatelé
                            </a>
                        @endif
                        @unless (auth()->user()->isAdmin())
                            <a href="{{ route('orders.welcome') }}" class="font-medium text-stone-600 hover:text-teal-800 {{ request()->routeIs('orders.*') ? 'text-teal-800' : '' }}">
                                Moje zakázky
                            </a>
                            <a href="{{ route('profile.edit') }}" class="font-medium text-stone-600 hover:text-teal-800 {{ request()->routeIs('profile.*') ? 'text-teal-800' : '' }}">
                                Profil
                            </a>
                        @endunless

                        <span class="hidden text-stone-500 sm:inline">{{ auth()->user()->name }}</span>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="rounded-lg border border-stone-300 px-3 py-1.5 font-medium text-stone-600 transition hover:bg-stone-100">
                                Odhlásit se
                            </button>
                        </form>
                    </nav>
                </div>
            </header>
        @endauth

        <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-8">
            @if (session('status'))
                <div class="mb-6 rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-900" role="status">
                    {{ session('status') }}
                </div>
            @endif

            {{ $slot }}
        </main>

        <footer class="border-t border-stone-200 py-4 text-center text-xs text-stone-400">
            {{ config('app.name') }} — interní aplikace pro zadávání zakázek
        </footer>
    </div>
</body>
</html>
