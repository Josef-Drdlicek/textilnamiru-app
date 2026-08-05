<?php

namespace App\Livewire\Configurators;

use App\Domain\Pricing\Parameters\CushionParameters;
use App\Domain\Pricing\Parameters\ItemParameters;
use App\Enums\ItemType;
use Illuminate\View\View;

/**
 * Sedák = polštář s hloubkou navíc; hloubka do ceny nevstupuje (zadání §7.4).
 */
class CushionConfigurator extends PillowConfigurator
{
    public ?float $depth = null;

    public function itemType(): ItemType
    {
        return ItemType::Cushion;
    }

    protected function typeRules(): array
    {
        return [
            ...parent::typeRules(),
            'depth' => ['required', 'numeric', 'min:1', 'max:300'],
        ];
    }

    protected function typeValidationAttributes(): array
    {
        return [
            ...parent::typeValidationAttributes(),
            'depth' => 'hloubka',
        ];
    }

    protected function buildParameters(): ItemParameters
    {
        return CushionParameters::fromArray([
            'width' => (float) $this->width,
            'height' => (float) $this->height,
            'depth' => (float) $this->depth,
            'stuffing' => $this->stuffing,
            'zipper' => $this->zipper,
        ]);
    }

    protected function fillFromParameters(ItemParameters $parameters): void
    {
        parent::fillFromParameters($parameters);

        $this->depth = $parameters->toArray()['depth'];
    }

    public function render(): View
    {
        return view('livewire.configurators.cushion');
    }
}
