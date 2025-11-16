<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        Event::factory()->count(5)->past()->published()->create();

        Event::factory()->count(2)->published()->create([
            'starts_at' => now()->subHours(2),
            'ends_at' => now()->addHours(2),
        ]);

        Event::factory()->count(13)->upcoming()->published()->create();
    }
}
