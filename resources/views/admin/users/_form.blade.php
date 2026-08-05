@php $editing = $user !== null; @endphp

<div class="grid gap-5 sm:grid-cols-2">
    <x-form.field name="name" label="Jméno">
        <x-form.input name="name" type="text" :value="old('name', $user?->name)" required />
    </x-form.field>

    <x-form.field name="username" label="Přihlašovací jméno" hint="Slouží k přihlášení — může být e-mail i krátké jméno.">
        <x-form.input name="username" type="text" :value="old('username', $user?->username)" required />
    </x-form.field>

    <x-form.field name="password" :label="$editing ? 'Nové heslo (nepovinné)' : 'Heslo'" :hint="$editing ? 'Vyplňte jen při změně hesla.' : 'Předejte uživateli bezpečnou cestou.'">
        <x-form.input name="password" type="password" autocomplete="new-password" :required="! $editing" />
    </x-form.field>

    <x-form.field name="phone" label="Telefon">
        <x-form.input name="phone" type="text" :value="old('phone', $user?->phone)" />
    </x-form.field>

    <x-form.field name="address" label="Adresa" class="sm:col-span-2">
        <textarea id="address" name="address" rows="3"
            class="block w-full rounded-lg border border-stone-300 px-3 py-2 shadow-sm focus:border-teal-500 focus:ring-teal-500">{{ old('address', $user?->address) }}</textarea>
    </x-form.field>

    <div class="sm:col-span-2">
        <label class="flex items-center gap-2 text-sm text-stone-700">
            <input type="hidden" name="is_admin" value="0">
            <input type="checkbox" name="is_admin" value="1" @checked(old('is_admin', $user?->is_admin))
                class="h-4 w-4 rounded border-stone-300 text-teal-700 focus:ring-teal-500">
            Účet dílny (admin) — vidí všechny zakázky, spravuje ceník a uživatele
        </label>
    </div>
</div>

<div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-stone-100 pt-5">
    <a href="{{ route('admin.users.index') }}" class="rounded-lg border border-stone-300 px-4 py-2 text-sm font-medium text-stone-600 transition hover:bg-stone-100">
        ← Zpět na seznam
    </a>
    <button type="submit" class="rounded-lg bg-teal-700 px-6 py-2.5 font-semibold text-white transition hover:bg-teal-800">
        {{ $editing ? 'Uložit změny' : 'Založit účet' }}
    </button>
</div>
