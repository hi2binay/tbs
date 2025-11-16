<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'provider' => fake()->randomElement(['stripe', 'razorpay']),
            'provider_payment_id' => 'pay_'.Str::random(24),
            'status' => 'captured',
            'amount_cents' => fake()->numberBetween(50000, 500000),
            'currency' => 'INR',
            'idempotency_key' => Str::uuid(),
            'payload' => [
                'method' => fake()->randomElement(['card', 'upi', 'netbanking']),
                'last4' => fake()->numberBetween(1000, 9999),
            ],
            'error_message' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function captured(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'captured',
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'error_message' => fake()->sentence(),
        ]);
    }
}
