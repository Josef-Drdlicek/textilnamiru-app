<x-layouts.app title="Můj profil">
    <div class="mx-auto max-w-xl">
        <div class="rounded-2xl border border-stone-200 bg-white p-8 shadow-sm">
            <h1 class="text-xl font-bold text-stone-900">Můj profil</h1>
            <p class="mt-1 text-sm text-stone-500">
                Telefon a adresa se zobrazují dílně u vašich zakázek.
            </p>

            <form method="POST" action="{{ route('profile.update') }}" class="mt-6 space-y-5">
                @csrf
                @method('PATCH')

                <div>
                    <label class="block text-sm font-medium text-stone-700">Přihlašovací jméno</label>
                    <input type="text" value="{{ $user->username }}" disabled
                        class="mt-1 block w-full rounded-lg border border-stone-200 bg-stone-100 px-3 py-2 text-stone-500">
                    <p class="mt-1 text-xs text-stone-400">Přihlašovací jméno mění pouze správce.</p>
                </div>

                <div>
                    <label for="name" class="block text-sm font-medium text-stone-700">Jméno</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required
                        class="mt-1 block w-full rounded-lg border border-stone-300 px-3 py-2 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-stone-700">Telefon</label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}"
                        class="mt-1 block w-full rounded-lg border border-stone-300 px-3 py-2 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                    @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="address" class="block text-sm font-medium text-stone-700">Adresa</label>
                    <textarea id="address" name="address" rows="3"
                        class="mt-1 block w-full rounded-lg border border-stone-300 px-3 py-2 shadow-sm focus:border-teal-500 focus:ring-teal-500">{{ old('address', $user->address) }}</textarea>
                    @error('address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <fieldset class="rounded-xl border border-stone-200 p-4">
                    <legend class="px-1 text-sm font-medium text-stone-500">Změna hesla (nepovinné)</legend>

                    <div class="space-y-4">
                        <div>
                            <label for="password" class="block text-sm font-medium text-stone-700">Nové heslo</label>
                            <input id="password" name="password" type="password" autocomplete="new-password"
                                class="mt-1 block w-full rounded-lg border border-stone-300 px-3 py-2 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                            @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-stone-700">Nové heslo znovu</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                                class="mt-1 block w-full rounded-lg border border-stone-300 px-3 py-2 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                        </div>
                    </div>
                </fieldset>

                <button type="submit" class="w-full rounded-lg bg-teal-700 px-4 py-2.5 font-semibold text-white transition hover:bg-teal-800">
                    Uložit profil
                </button>
            </form>
        </div>
    </div>
</x-layouts.app>
