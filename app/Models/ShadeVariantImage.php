<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['image_path', 'source_url', 'alt', 'sort_order'])]
class ShadeVariantImage extends Model
{
    public function url(): ?string
    {
        return $this->image_path
            ? route('web.shade-image', ['image' => $this, 'v' => $this->updated_at?->timestamp])
            : null;
    }
}
