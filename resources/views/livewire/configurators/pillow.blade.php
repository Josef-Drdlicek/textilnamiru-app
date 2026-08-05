<div class="grid items-start gap-6 lg:grid-cols-[1fr_360px]">
    <div class="space-y-6 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
        @include('livewire.configurators.partials._pillow-fields', ['showDepth' => false])

        <div class="grid gap-4 border-t border-stone-100 pt-5 sm:grid-cols-2">
            @include('livewire.configurators.partials._common-fields')
        </div>

        @include('livewire.configurators.partials._actions')
    </div>

    <aside class="space-y-6 lg:sticky lg:top-6">
        @include('livewire.configurators.partials._price-panel')
        @include('livewire.configurators.partials._schema')
    </aside>
</div>
