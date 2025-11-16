<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketTypeFactory extends Factory
{
    protected $model = TicketType::class;

    public function definition(): array
    {
        $types = [
            ['name' => 'VIP', 'price' => fake()->numberBetween(3000, 5000), 'quantity' => fake()->numberBetween(50, 150)],
            ['name' => 'Premium', 'price' => fake()->numberBetween(1500, 2500), 'quantity' => fake()->numberBetween(100, 300)],
            ['name' => 'General', 'price' => fake()->numberBetween(500, 1200), 'quantity' => fake()->numberBetween(200, 500)],
        ];

        $type = fake()->randomElement($types);

        return [
            'event_id' => Event::factory(),
            'name' => $type['name'],
            'description' => fake()->sentence(),
            'currency' => 'INR',
            'price_cents' => $type['price'] * 100,
            'total_quantity' => $type['quantity'],
            'sold_count' => 0,
            'reserved_count' => 0,
            'per_user_limit' => fake()->numberBetween(4, 10),
            'is_active' => true,
        ];
    }

    public function soldOut(): static
    {
        return $this->state(fn (array $attributes) => [
            'sold_count' => $attributes['total_quantity'],
        ]);
    }

    public function partiallyBooked(): static
    {
        return $this->state(function (array $attributes) {
            $soldPercentage = fake()->numberBetween(30, 80);

            return [
                'sold_count' => (int) ($attributes['total_quantity'] * $soldPercentage / 100),
            ];
        });
    }

    public function vip(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'VIP',
            'price_cents' => fake()->numberBetween(3000, 5000) * 100,
            'total_quantity' => fake()->numberBetween(50, 150),
        ]);
    }

    public function premium(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Premium',
            'price_cents' => fake()->numberBetween(1500, 2500) * 100,
            'total_quantity' => fake()->numberBetween(100, 300),
        ]);
    }

    public function general(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'General',
            'price_cents' => fake()->numberBetween(500, 1200) * 100,
            'total_quantity' => fake()->numberBetween(200, 500),
        ]);
    }
}
