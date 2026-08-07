<?php

namespace App\Models;

use App\Enums\InquiryProduct;
use App\Enums\InquiryStatus;
use App\Enums\MeasurementType;
use App\Enums\SpaceType;
use Database\Factories\InquiryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Poptávka z veřejného webu. Na rozdíl od Order nemá vlastníka (user_id) —
 * přichází od anonymního návštěvníka; do zakázky ji převádí admin.
 */
#[Fillable([
    'status', 'name', 'email', 'phone', 'city', 'space_type', 'products',
    'measurement', 'message', 'gdpr_consented_at', 'configuration', 'ip', 'order_id',
])]
class Inquiry extends Model
{
    /** @use HasFactory<InquiryFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => InquiryStatus::class,
            'space_type' => SpaceType::class,
            'products' => AsEnumCollection::class.':'.InquiryProduct::class,
            'measurement' => MeasurementType::class,
            'gdpr_consented_at' => 'datetime',
            'configuration' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isConverted(): bool
    {
        return $this->order_id !== null;
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeNew(Builder $query): void
    {
        $query->where('status', InquiryStatus::New);
    }
}
