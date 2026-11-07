<x-layouts.web
    title="B2B přihlášení"
    description="Přihlášení pro interiérová studia."
    robots="noindex, nofollow"
>
    <section class="flex min-h-[calc(100vh-6rem)] items-center justify-center px-4 py-16 sm:px-6">
        <div class="w-full max-w-md bg-white p-6 shadow-md sm:p-10">
            <h1 class="text-section-title">Přihlášení pro interiérová studia</h1>

            <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5">
                @csrf

                <div>
                    <label for="username" class="block text-sm font-medium">Uživatel</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        value="{{ old('username') }}"
                        required
                        autofocus
                        autocomplete="username"
                        class="mt-1 block w-full rounded-lg border border-line bg-white px-3 py-2.5 focus:border-brand-600 focus:ring-brand-600"
                    >
                    @error('username')
                        <p class="mt-1 text-sm font-medium text-error" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium">Heslo</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        class="mt-1 block w-full rounded-lg border border-line bg-white px-3 py-2.5 focus:border-brand-600 focus:ring-brand-600"
                    >
                    @error('password')
                        <p class="mt-1 text-sm font-medium text-error" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn-primary w-full sm:w-auto">Přihlásit se</button>

                @if (app()->environment('local'))
                    <p class="text-sm text-muted">
                        Lokální demo:
                        <strong>lucie</strong> / demo-heslo-lucie
                        nebo
                        <strong>dilna</strong> / demo-heslo-dilna
                    </p>
                @endif
            </form>
        </div>
    </section>
</x-layouts.web>
