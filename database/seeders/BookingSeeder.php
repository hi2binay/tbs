<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Event;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $events = Event::published()->get();

        foreach ($users->take(8) as $user) {
            $bookingCount = fake()->numberBetween(1, 3);

            for ($i = 0; $i < $bookingCount; $i++) {
                $event = $events->random();
                $ticketType = $event->ticketTypes()->active()->inRandomOrder()->first();

                if (! $ticketType) {
                    continue;
                }

                $quantity = fake()->numberBetween(1, min(3, $ticketType->per_user_limit));
                $totalAmount = $ticketType->price_cents * $quantity;

                $reservation = Reservation::create([
                    'user_id' => $user->id,
                    'event_id' => $event->id,
                    'ticket_type_id' => $ticketType->id,
                    'quantity' => $quantity,
                    'status' => 'confirmed',
                    'expires_at' => now()->addMinutes(15),
                    'payment_intent_id' => 'pi_'.fake()->unique()->bothify('??????????##########'),
                    'idempotency_key' => fake()->uuid(),
                ]);

                $booking = Booking::create([
                    'reservation_id' => $reservation->id,
                    'user_id' => $user->id,
                    'status' => 'confirmed',
                    'total_amount_cents' => $totalAmount,
                    'currency' => 'INR',
                ]);

                BookingItem::create([
                    'booking_id' => $booking->id,
                    'ticket_type_id' => $ticketType->id,
                    'quantity' => $quantity,
                    'unit_price_cents' => $ticketType->price_cents,
                ]);

                Payment::create([
                    'booking_id' => $booking->id,
                    'provider' => fake()->randomElement(['stripe', 'razorpay']),
                    'provider_payment_id' => 'pay_'.fake()->unique()->bothify('??????????##########'),
                    'status' => 'captured',
                    'amount_cents' => $totalAmount,
                    'currency' => 'INR',
                    'idempotency_key' => fake()->uuid(),
                    'payload' => [
                        'method' => fake()->randomElement(['card', 'upi', 'netbanking', 'wallet']),
                        'last4' => fake()->numberBetween(1000, 9999),
                        'email' => $user->email,
                    ],
                ]);
            }
        }

        $randomUsers = User::inRandomOrder()->limit(3)->get();
        foreach ($randomUsers as $user) {
            $event = $events->random();
            $ticketType = $event->ticketTypes()->active()->inRandomOrder()->first();

            if (! $ticketType) {
                continue;
            }

            $quantity = fake()->numberBetween(1, 2);

            Reservation::create([
                'user_id' => $user->id,
                'event_id' => $event->id,
                'ticket_type_id' => $ticketType->id,
                'quantity' => $quantity,
                'status' => 'pending',
                'expires_at' => now()->addMinutes(15),
                'payment_intent_id' => 'pi_'.fake()->unique()->bothify('??????????##########'),
                'idempotency_key' => fake()->uuid(),
            ]);
        }
    }
}
