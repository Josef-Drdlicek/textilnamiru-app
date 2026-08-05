<?php

namespace App\Livewire\Configurators;

use App\Domain\Pricing\Parameters\BlindParameters;
use App\Domain\Pricing\Parameters\ItemParameters;
use App\Enums\ItemType;
use Illuminate\View\View;

class BlindConfigurator extends AbstractConfigurator
{
    public ?float $width = null;

    public ?float $height = null;

    public bool $mechanism = false;

    public bool $tunnels = false;

    public bool $finishing = false;

    public ?int $cells = null;

    public ?float $cellHeightTop = null;

    public ?float $cellHeight = null;

    public ?float $cellHeightBottom = null;

    public function itemType(): ItemType
    {
        return ItemType::Blind;
    }

    protected function typeRules(): array
    {
        return [
            'width' => ['required', 'numeric', 'min:10', 'max:300'],
            'height' => ['required', 'numeric', 'min:10', 'max:300'],
            'mechanism' => ['boolean'],
            'tunnels' => ['boolean'],
            'finishing' => ['boolean'],
            'cells' => ['nullable', 'integer', 'min:1', 'max:12'],
            'cellHeightTop' => ['nullable', 'numeric', 'min:1', 'max:300'],
            'cellHeight' => ['nullable', 'numeric', 'min:1', 'max:300'],
            'cellHeightBottom' => ['nullable', 'numeric', 'min:1', 'max:300'],
        ];
    }

    protected function typeValidationAttributes(): array
    {
        return [
            'width' => 'šířka',
            'height' => 'výška',
            'cells' => 'počet polí',
            'cellHeightTop' => 'výška horního pole',
            'cellHeight' => 'výška pole',
            'cellHeightBottom' => 'výška spodního pole',
        ];
    }

    protected function buildParameters(): ItemParameters
    {
        return BlindParameters::fromArray([
            'width' => (float) $this->width,
            'height' => (float) $this->height,
            'mechanism' => $this->mechanism,
            'tunnels' => $this->tunnels,
            'finishing' => $this->finishing,
            'cells' => $this->cells,
            'cell_height_top' => $this->cellHeightTop,
            'cell_height' => $this->cellHeight,
            'cell_height_bottom' => $this->cellHeightBottom,
        ]);
    }

    protected function fillFromParameters(ItemParameters $parameters): void
    {
        $data = $parameters->toArray();

        $this->width = $data['width'];
        $this->height = $data['height'];
        $this->mechanism = $data['mechanism'];
        $this->tunnels = $data['tunnels'];
        $this->finishing = $data['finishing'];
        $this->cells = $data['cells'];
        $this->cellHeightTop = $data['cell_height_top'];
        $this->cellHeight = $data['cell_height'];
        $this->cellHeightBottom = $data['cell_height_bottom'];
    }

    public function render(): View
    {
        return view('livewire.configurators.blind');
    }
}
