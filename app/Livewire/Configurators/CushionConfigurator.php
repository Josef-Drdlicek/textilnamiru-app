<?php

namespace App\Livewire\Configurators;

use App\Domain\Pricing\Parameters\CushionParameters;
use App\Domain\Pricing\Parameters\ItemParameters;
use App\Enums\ItemType;
use App\Enums\ZipperSide;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CushionConfigurator extends AbstractConfigurator
{
    public ?float $width = null;

    public ?float $height = null;

    public ?float $depth = null;

    public bool $foam = true;

    public bool $felt = false;

    public bool $ties = false;

    public string $zipper = ZipperSide::None->value;

    public function itemType(): ItemType
    {
        return ItemType::Cushion;
    }

    protected function typeRules(): array
    {
        return [
            'width' => ['required', 'numeric', 'min:10', 'max:200'],
            'height' => ['required', 'numeric', 'min:10', 'max:200'],
            'depth' => ['required', 'numeric', 'min:1', 'max:50'],
            'foam' => ['boolean'],
            'felt' => ['boolean'],
            'ties' => ['boolean'],
            'zipper' => ['required', Rule::enum(ZipperSide::class)],
        ];
    }

    protected function typeValidationAttributes(): array
    {
        return [
            'width' => 'šířka',
            'height' => 'délka',
            'depth' => 'tloušťka',
            'zipper' => 'zip',
        ];
    }

    protected function buildParameters(): ItemParameters
    {
        return CushionParameters::fromArray([
            'width' => (float) $this->width,
            'height' => (float) $this->height,
            'depth' => (float) $this->depth,
            'foam' => $this->foam,
            'felt' => $this->felt,
            'ties' => $this->ties,
            'zipper' => $this->zipper,
        ]);
    }

    protected function fillFromParameters(ItemParameters $parameters): void
    {
        $data = $parameters->toArray();

        $this->width = $data['width'];
        $this->height = $data['height'];
        $this->depth = $data['depth'];
        $this->foam = $data['foam'];
        $this->felt = $data['felt'];
        $this->ties = $data['ties'];
        $this->zipper = $data['zipper'];
    }

    public function render(): View
    {
        return view('livewire.configurators.cushion');
    }
}
