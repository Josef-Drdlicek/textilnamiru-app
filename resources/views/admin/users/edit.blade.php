<x-layouts.app :title="'Uživatel – '.$user->name">
    <div class="mx-auto max-w-2xl">
        <h1 class="text-2xl font-bold text-stone-900">Upravit uživatele</h1>

        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="mt-6 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            @csrf
            @method('PATCH')
            @include('admin.users._form', ['user' => $user])
        </form>
    </div>
</x-layouts.app>
