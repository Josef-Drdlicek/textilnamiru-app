<?php

namespace App\Livewire\Configurators;

use App\Domain\Pricing\Parameters\ItemParameters;
use App\Domain\Pricing\Parameters\PillowParameters;
use App\Enums\ItemType;
use App\Enums\ZipperSide;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PillowConfigurator extends AbstractConfigurator
{
    public ?float $width = null;

    public ?float $height = null;

    public bool $stuffing = false;

    public string $zipper = ZipperSide::Short->value;

    public function itemType(): ItemType
    {
        return ItemType::Pillow;
    }

    protected function typeRules(): array
    {
        return [
            'width' => ['required', 'numeric', 'min:10', 'max:100'],
            'height' => ['required', 'numeric', 'min:10', 'max:100'],
            'stuffing' => ['boolean'],
            'zipper' => ['required', Rule::enum(ZipperSide::class)],
        ];
    }

    protected function typeValidationAttributes(): array
    {
        return [
            'width' => 'šířka',
            'height' => 'výška',
            'zipper' => 'umístění zipu',
        ];
    }

    protected function buildParameters(): ItemParameters
    {
        return PillowParameters::fromArray([
            'width' => (float) $this->width,
            'height' => (float) $this->height,
            'stuffing' => $this->stuffing,
            'zipper' => $this->zipper,
        ]);
    }

    protected function fillFromParameters(ItemParameters $parameters): void
    {
        $data = $parameters->toArray();

        $this->width = $data['width'];
        $this->height = $data['height'];
        $this->stuffing = $data['stuffing'];
        $this->zipper = $data['zipper'];
    }

    public function render(): View
    {
        return view('livewire.configurators.pillow');
    }
}
