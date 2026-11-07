<?php

namespace Database\Factories;

use App\Domain\Pricing\Parameters\PillowParameters;
use App\Enums\ItemType;
use App\Enums\ZipperSide;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'type' => ItemType::Pillow,
            'material' => fake()->randomElement(['voal', 'závěsovina', 'bavlna']),
            'count' => fake()->numberBetween(1, 4),
            'parameters' => new PillowParameters(
                width: 50,
                height: 50,
                stuffing: false,
                complex: false,
                zipper: ZipperSide::Short,
            ),
            'unit_price' => 110.0,
            'total_price' => 110.0,
            'consumed_meters' => 0,
            'price_breakdown' => [],
        ];
    }
}
