<?php

namespace App\Livewire\Configurators;

use App\Domain\Pricing\Parameters\ItemParameters;
use App\Domain\Pricing\Parameters\StretchCeilingParameters;
use App\Enums\CeilingSurface;
use App\Enums\ItemType;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StretchCeilingConfigurator extends AbstractConfigurator
{
    public ?float $width = null;

    public ?float $length = null;

    public string $surface = CeilingSurface::Matte->value;

    public ?float $ledLength = null;

    public ?float $distance = null;

    public function itemType(): ItemType
    {
        return ItemType::StretchCeiling;
    }

    protected function typeRules(): array
    {
        return [
            'width' => ['required', 'numeric', 'min:10', 'max:2000'],
            'length' => ['required', 'numeric', 'min:10', 'max:2000'],
            'surface' => ['required', Rule::enum(CeilingSurface::class)],
            'ledLength' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'distance' => ['nullable', 'numeric', 'min:0', 'max:2000'],
        ];
    }

    protected function typeValidationAttributes(): array
    {
        return [
            'width' => 'šířka',
            'length' => 'délka',
            'surface' => 'povrch',
            'ledLength' => 'délka LED pásku',
            'distance' => 'vzdálenost',
        ];
    }

    protected function buildParameters(): ItemParameters
    {
        return StretchCeilingParameters::fromArray([
            'width' => (float) $this->width,
            'length' => (float) $this->length,
            'surface' => $this->surface,
            'led_length' => (float) $this->ledLength,
            'distance' => (float) $this->distance,
        ]);
    }

    protected function fillFromParameters(ItemParameters $parameters): void
    {
        $data = $parameters->toArray();

        $this->width = $data['width'];
        $this->length = $data['length'];
        $this->surface = $data['surface'];
        $this->ledLength = $data['led_length'] ?: null;
        $this->distance = $data['distance'] ?: null;
    }

    public function render(): View
    {
        return view('livewire.configurators.stretch-ceiling');
    }
}
