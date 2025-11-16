<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Database\Seeder;

class TicketTypeSeeder extends Seeder
{
    public function run(): void
    {
        $events = Event::all();

        foreach ($events as $event) {
            $ticketTypeCount = fake()->randomElement([2, 3]);

            if ($ticketTypeCount === 2) {
                TicketType::factory()->general()->create(['event_id' => $event->id]);
                TicketType::factory()->vip()->create(['event_id' => $event->id]);
            } else {
                TicketType::factory()->general()->create(['event_id' => $event->id]);
                TicketType::factory()->premium()->create(['event_id' => $event->id]);
                TicketType::factory()->vip()->create(['event_id' => $event->id]);
            }
        }

        $randomTickets = TicketType::inRandomOrder()->limit(5)->get();
        foreach ($randomTickets as $ticket) {
            $ticket->update([
                'sold_count' => $ticket->total_quantity,
            ]);
        }

        $partiallyBookedTickets = TicketType::inRandomOrder()->limit(10)->get();
        foreach ($partiallyBookedTickets as $ticket) {
            $soldPercentage = fake()->numberBetween(30, 80);
            $ticket->update([
                'sold_count' => (int) ($ticket->total_quantity * $soldPercentage / 100),
            ]);
        }
    }
}
