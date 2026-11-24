<x-layouts.app title="Nový produkt – Ceník textilu">
    <div class="mx-auto max-w-2xl">
        <h1 class="text-2xl font-bold text-stone-900">Nový produkt</h1>

        <form method="POST" action="{{ route('admin.textile-pricing.store') }}" enctype="multipart/form-data" class="mt-6 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            @csrf
            @include('admin.pricing.textile._form')
        </form>
    </div>
</x-layouts.app>
