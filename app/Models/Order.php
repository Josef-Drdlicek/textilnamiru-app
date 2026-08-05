<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['user_id', 'name', 'status', 'deadline', 'materials_note', 'submitted_at', 'total_price', 'vat_rate'])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'deadline' => 'date',
            'submitted_at' => 'datetime',
            'total_price' => 'float',
            'vat_rate' => 'float',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return HasMany<OrderStatusChange, $this>
     */
    public function statusChanges(): HasMany
    {
        return $this->hasMany(OrderStatusChange::class)->orderBy('created_at');
    }

    public function isDraft(): bool
    {
        return $this->status === OrderStatus::Draft;
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeSubmitted(Builder $query): void
    {
        $query->where('status', '!=', OrderStatus::Draft);
    }

    public function totalPriceWithVat(): ?float
    {
        if ($this->total_price === null || $this->vat_rate === null) {
            return null;
        }

        return round($this->total_price * (100 + $this->vat_rate)) / 100;
    }
}
