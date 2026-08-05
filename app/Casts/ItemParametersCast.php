<?php

namespace App\Casts;

use App\Domain\Pricing\Parameters\ItemParameters;
use App\Enums\ItemType;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * Hydratuje JSON sloupec `parameters` na typované DTO podle sloupce `type`.
 *
 * @implements CastsAttributes<ItemParameters, ItemParameters>
 */
class ItemParametersCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?ItemParameters
    {
        if ($value === null) {
            return null;
        }

        $type = ItemType::from($attributes['type']);

        return $type->parametersClass()::fromArray(json_decode($value, associative: true));
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if (! $value instanceof ItemParameters) {
            throw new InvalidArgumentException('Parametry položky musí být instance ItemParameters.');
        }

        return json_encode($value->toArray());
    }
}
