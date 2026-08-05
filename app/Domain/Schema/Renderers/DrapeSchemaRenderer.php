<?php

namespace App\Domain\Schema\Renderers;

use App\Domain\Pricing\Parameters\DrapeParameters;
use App\Domain\Pricing\Parameters\ItemParameters;
use InvalidArgumentException;

class DrapeSchemaRenderer extends CurtainSchemaRenderer
{
    public function render(ItemParameters $parameters): string
    {
        if (! $parameters instanceof DrapeParameters) {
            throw new InvalidArgumentException('Nákres závěsu vyžaduje DrapeParameters.');
        }

        return $this->renderCurtainLike(
            width: $parameters->width,
            height: $parameters->height,
            kanyrHeight: $parameters->kanyr ? ($parameters->kanyrHeight ?? 0) : 0,
            bottomLead: false,
        );
    }
}
