<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Reservation;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'event_id' => Event::factory(),
            'ticket_type_id' => TicketType::factory(),
            'quantity' => fake()->numberBetween(1, 4),
            'status' => 'pending',
            'expires_at' => now()->addMinutes(15),
            'payment_intent_id' => 'pi_'.Str::random(24),
            'idempotency_key' => Str::uuid(),
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'expires_at' => now()->subMinutes(30),
        ]);
    }

    public function canceled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'canceled',
        ]);
    }
}
