<x-layouts.app title="Uživatelé">
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-bold text-stone-900">Uživatelé</h1>
            <a href="{{ route('admin.users.create') }}" class="rounded-lg bg-teal-700 px-5 py-2 font-semibold text-white transition hover:bg-teal-800">
                + Nový uživatel
            </a>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-stone-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-stone-200 text-sm">
                <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">
                    <tr>
                        <th class="px-5 py-3">Jméno</th>
                        <th class="px-5 py-3">Přihlašovací jméno</th>
                        <th class="px-5 py-3">Telefon</th>
                        <th class="px-5 py-3 text-right">Zakázek</th>
                        <th class="px-5 py-3">Role</th>
                        <th class="px-5 py-3">Stav</th>
                        <th class="px-5 py-3 text-right"><span class="sr-only">Akce</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($users as $user)
                        <tr class="{{ $user->is_active ? '' : 'opacity-50' }}">
                            <td class="px-5 py-3 font-medium text-stone-800">{{ $user->name }}</td>
                            <td class="px-5 py-3 font-mono text-xs">{{ $user->username }}</td>
                            <td class="px-5 py-3">{{ $user->phone ?? '—' }}</td>
                            <td class="px-5 py-3 text-right">{{ $user->orders_count }}</td>
                            <td class="px-5 py-3">
                                @if ($user->is_admin)
                                    <span class="rounded-full bg-teal-100 px-2.5 py-0.5 text-xs font-semibold text-teal-800">dílna</span>
                                @else
                                    <span class="text-stone-500">studio</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                {{ $user->is_active ? 'aktivní' : 'deaktivovaný' }}
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="font-medium text-teal-700 hover:underline">Upravit</a>

                                    @if ($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}"
                                              @if ($user->is_active) onsubmit="return confirm('Deaktivovat účet „{{ $user->username }}“? Uživatel se nebude moci přihlásit.');" @endif>
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="font-medium {{ $user->is_active ? 'text-red-600' : 'text-emerald-600' }} hover:underline">
                                                {{ $user->is_active ? 'Deaktivovat' : 'Aktivovat' }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
