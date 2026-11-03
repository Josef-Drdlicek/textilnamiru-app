{{-- Vizuální B2B přihlášení. DOPLNIT: napojení na backend --}}
<x-layouts.web
    title="B2B přihlášení"
    description="Přihlášení pro interiérová studia."
    robots="noindex, nofollow"
>
    <section class="flex min-h-[calc(100vh-6rem)] items-center justify-center px-4 py-16 sm:px-6">
        <div class="w-full max-w-md bg-white p-6 shadow-md sm:p-10">
            <h1 class="text-section-title">Přihlášení pro interiérová studia</h1>

            <form data-b2b-form class="mt-8 space-y-5" novalidate>
                <div>
                    <label for="b2b-uzivatel" class="block text-sm font-medium">Uživatel</label>
                    <input
                        type="text"
                        id="b2b-uzivatel"
                        name="uzivatel"
                        autocomplete="username"
                        class="mt-1 block w-full rounded-lg border border-line bg-white px-3 py-2.5 focus:border-brand-600 focus:ring-brand-600"
                    >
                </div>

                <div>
                    <label for="b2b-heslo" class="block text-sm font-medium">Heslo</label>
                    <input
                        type="password"
                        id="b2b-heslo"
                        name="heslo"
                        autocomplete="current-password"
                        class="mt-1 block w-full rounded-lg border border-line bg-white px-3 py-2.5 focus:border-brand-600 focus:ring-brand-600"
                    >
                </div>

                {{-- DOPLNIT: napojení na backend --}}
                <button type="submit" class="btn-primary w-full sm:w-auto">Přihlásit se</button>

                <p data-b2b-message class="hidden text-sm font-medium text-brand-700" role="status" aria-live="polite">
                    Přihlášení bude brzy zprovozněno.
                </p>
            </form>
        </div>
    </section>
</x-layouts.web>
