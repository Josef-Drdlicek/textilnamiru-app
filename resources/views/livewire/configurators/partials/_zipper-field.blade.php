<x-form.field name="zipper" label="Zip">
    <x-form.select name="zipper" wire:model.live="zipper">
        @foreach (\App\Enums\ZipperSide::cases() as $side)
            <option value="{{ $side->value }}">{{ $side->label() }}</option>
        @endforeach
    </x-form.select>
</x-form.field>
