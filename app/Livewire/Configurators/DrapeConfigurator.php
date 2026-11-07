<?php

namespace App\Livewire\Configurators;

use App\Domain\Pricing\Parameters\DrapeParameters;
use App\Domain\Pricing\Parameters\ItemParameters;
use App\Enums\ItemType;
use Illuminate\View\View;

/**
 * Závěs = záclona s raportem a spodní záložkou navíc (zadání §7.2).
 */
class DrapeConfigurator extends CurtainConfigurator
{
    public ?float $raport = null;

    public ?float $folding = null;

    public function itemType(): ItemType
    {
        return ItemType::Drape;
    }

    protected function typeRules(): array
    {
        return [
            ...parent::typeRules(),
            'raport' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'folding' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    protected function typeValidationAttributes(): array
    {
        return [
            ...parent::typeValidationAttributes(),
            'raport' => 'raport délky',
            'folding' => 'zalomení',
        ];
    }

    protected function buildParameters(): ItemParameters
    {
        return DrapeParameters::fromArray([
            ...$this->fabricParameterData(),
            'raport' => $this->raport !== null ? (float) $this->raport : null,
            'folding' => $this->folding !== null ? (float) $this->folding : null,
        ]);
    }

    protected function fillFromParameters(ItemParameters $parameters): void
    {
        $data = $parameters->toArray();

        $this->fillFabricFields($data);

        $this->raport = $data['raport'];
        $this->folding = $data['folding'];
    }

    public function render(): View
    {
        return view('livewire.configurators.drape');
    }
}
