<?php

namespace Database\Factories;

use App\Enums\InquiryProduct;
use App\Enums\InquiryStatus;
use App\Enums\MeasurementType;
use App\Enums\SpaceType;
use App\Models\Inquiry;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inquiry>
 */
class InquiryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'status' => InquiryStatus::New,
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '+420'.fake()->numerify('#########'),
            'city' => fake()->city(),
            'space_type' => fake()->randomElement(SpaceType::cases()),
            'products' => fake()->randomElements(InquiryProduct::cases(), 2),
            'measurement' => fake()->randomElement(MeasurementType::cases()),
            'message' => fake()->sentence(10),
            'gdpr_consented_at' => now(),
            'ip' => fake()->ipv4(),
        ];
    }

    public function contacted(): static
    {
        return $this->state(['status' => InquiryStatus::Contacted]);
    }

    public function status(InquiryStatus $status): static
    {
        return $this->state(['status' => $status]);
    }

    public function withConfiguration(): static
    {
        return $this->state([
            'configuration' => [
                'type' => 'pillow',
                'parameters' => [
                    'width' => 50.0,
                    'height' => 50.0,
                    'stuffing' => true,
                    'zipper' => 'short',
                ],
                'count' => 2,
                'unit_price' => 110.0,
                'unit_price_vat' => 133.1,
                'total_price_vat' => 266.2,
                'vat_rate' => 21.0,
                'price_lines' => [
                    ['label' => 'Šití polštáře', 'amount' => 110.0],
                ],
                'calculated_at' => now()->toIso8601String(),
            ],
        ]);
    }

    public function convertedToOrder(): static
    {
        return $this->state([
            'status' => InquiryStatus::Contacted,
            'order_id' => Order::factory(),
        ]);
    }
}
