<?php

namespace App\Models;

use App\Casts\ItemParametersCast;
use App\Enums\ItemType;
use Database\Factories\OrderItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['order_id', 'type', 'material', 'room', 'count', 'notes', 'parameters', 'unit_price', 'total_price', 'consumed_meters', 'price_breakdown'])]
class OrderItem extends Model
{
    /** @use HasFactory<OrderItemFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'type' => ItemType::class,
            'count' => 'integer',
            'parameters' => ItemParametersCast::class,
            'unit_price' => 'float',
            'total_price' => 'float',
            'consumed_meters' => 'float',
            'price_breakdown' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
