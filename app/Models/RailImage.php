<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Fotka kolejnice/garnýže (role „photo“) nebo dílu – jezdce, držáku, sestavy (role „component“).
 */
#[Fillable(['rail_product_id', 'rail_variant_id', 'role', 'image_path', 'source_url', 'alt', 'sort_order'])]
class RailImage extends Model
{
    public const string PHOTO = 'photo';

    public const string COMPONENT = 'component';

    public function url(): string
    {
        return route('web.rail-image', ['image' => $this, 'v' => $this->updated_at?->timestamp]);
    }
}
