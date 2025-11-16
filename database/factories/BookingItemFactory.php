<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\TicketType;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingItemFactory extends Factory
{
    protected $model = BookingItem::class;

    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 4);
        $unitPrice = fake()->numberBetween(500, 5000) * 100;

        return [
            'booking_id' => Booking::factory(),
            'ticket_type_id' => TicketType::factory(),
            'quantity' => $quantity,
            'unit_price_cents' => $unitPrice,
        ];
    }
}
