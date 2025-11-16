<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $events = [
            ['name' => 'Arijit Singh Live in Concert', 'venue' => 'NSCI Dome', 'location' => 'Mumbai'],
            ['name' => 'Coldplay Music of the Spheres World Tour', 'venue' => 'DY Patil Stadium', 'location' => 'Mumbai'],
            ['name' => 'Diljit Dosanjh Dil-Luminati Tour', 'venue' => 'JLN Stadium', 'location' => 'Delhi'],
            ['name' => 'Sunburn Festival 2025', 'venue' => 'Vagator Beach', 'location' => 'Goa'],
            ['name' => 'NH7 Weekender', 'venue' => 'Backyard Sports Club', 'location' => 'Pune'],
            ['name' => 'IPL 2025: Mumbai Indians vs Chennai Super Kings', 'venue' => 'Wankhede Stadium', 'location' => 'Mumbai'],
            ['name' => 'IPL 2025: Royal Challengers Bangalore vs Kolkata Knight Riders', 'venue' => 'M. Chinnaswamy Stadium', 'location' => 'Bengaluru'],
            ['name' => 'India vs Australia Test Match', 'venue' => 'Eden Gardens', 'location' => 'Kolkata'],
            ['name' => 'Pro Kabaddi League Finals', 'venue' => 'EKA Arena', 'location' => 'Ahmedabad'],
            ['name' => 'TechCrunch Disrupt India', 'venue' => 'Jio World Convention Centre', 'location' => 'Mumbai'],
            ['name' => 'AWS Summit India', 'venue' => 'KTPO Convention Centre', 'location' => 'Bengaluru'],
            ['name' => 'Google DevFest', 'venue' => 'India Expo Mart', 'location' => 'Greater Noida'],
            ['name' => 'Comic Con India', 'venue' => 'NSCI Dome', 'location' => 'Mumbai'],
            ['name' => 'Lollapalooza India', 'venue' => 'Mahalaxmi Race Course', 'location' => 'Mumbai'],
            ['name' => 'Zakir Khan Stand-Up Comedy', 'venue' => 'Phoenix Marketcity', 'location' => 'Pune'],
            ['name' => 'Bharatanatyam Festival', 'venue' => 'Music Academy', 'location' => 'Chennai'],
            ['name' => 'EDM Night with DJ Alan Walker', 'venue' => 'Phoenix Marketcity', 'location' => 'Bengaluru'],
            ['name' => 'Theatre Play: Mughal-e-Azam', 'venue' => 'NCPA', 'location' => 'Mumbai'],
            ['name' => 'International Film Festival', 'venue' => 'Siri Fort Auditorium', 'location' => 'Delhi'],
            ['name' => 'Food & Wine Festival', 'venue' => 'The Lalit Hotel', 'location' => 'Goa'],
        ];

        $event = fake()->randomElement($events);
        $startsAt = fake()->dateTimeBetween('-1 month', '+6 months');
        $endsAt = (clone $startsAt)->modify('+'.fake()->numberBetween(2, 8).' hours');

        return [
            'slug' => Str::slug($event['name']).'-'.fake()->unique()->numberBetween(1000, 9999),
            'name' => $event['name'],
            'description' => fake()->paragraphs(3, true),
            'venue' => $event['venue'],
            'location' => $event['location'],
            'status' => fake()->randomElement(['draft', 'published', 'published', 'published']),
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
        ]);
    }

    public function upcoming(): static
    {
        return $this->state(function (array $attributes) {
            $startsAt = fake()->dateTimeBetween('now', '+6 months');
            $endsAt = (clone $startsAt)->modify('+'.fake()->numberBetween(2, 8).' hours');

            return [
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
            ];
        });
    }

    public function past(): static
    {
        return $this->state(function (array $attributes) {
            $startsAt = fake()->dateTimeBetween('-1 month', '-1 day');
            $endsAt = (clone $startsAt)->modify('+'.fake()->numberBetween(2, 8).' hours');

            return [
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
            ];
        });
    }
}
