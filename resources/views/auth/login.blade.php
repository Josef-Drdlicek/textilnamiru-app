<x-layouts.app title="Přihlášení">
    <div class="mx-auto mt-12 w-full max-w-md">
        <div class="rounded-2xl border border-stone-200 bg-white p-8 shadow-sm">
            <h1 class="text-center text-2xl font-bold tracking-tight text-teal-800">
                {{ config('app.name') }}
            </h1>
            <p class="mt-1 text-center text-sm text-stone-500">
                Přihlaste se ke svému účtu
            </p>

            <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5">
                @csrf

                <div>
                    <label for="username" class="block text-sm font-medium text-stone-700">Přihlašovací jméno</label>
                    <input
                        id="username"
                        name="username"
                        type="text"
                        value="{{ old('username') }}"
                        required
                        autofocus
                        autocomplete="username"
                        class="mt-1 block w-full rounded-lg border border-stone-300 px-3 py-2 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                    >
                    @error('username')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-stone-700">Heslo</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        required
                        autocomplete="current-password"
                        class="mt-1 block w-full rounded-lg border border-stone-300 px-3 py-2 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                    >
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full rounded-lg bg-teal-700 px-4 py-2.5 font-semibold text-white transition hover:bg-teal-800">
                    Přihlásit se
                </button>
            </form>
        </div>

        <p class="mt-6 text-center text-sm text-stone-500">
            Pokud nemáte přihlašovací údaje, kontaktujte správce na tel. 775&nbsp;488&nbsp;898.
        </p>
    </div>
</x-layouts.app>
