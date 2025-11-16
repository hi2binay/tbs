<?php

use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists all published events', function () {
    Event::factory()->count(3)->create(['status' => 'published']);
    Event::factory()->count(2)->create(['status' => 'draft']);

    $response = $this->getJson('/api/v1/events');

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

it('filters events by status', function () {
    Event::factory()->count(3)->create(['status' => 'published']);
    Event::factory()->count(2)->create(['status' => 'draft']);

    $response = $this->getJson('/api/v1/events?status=draft');

    $response->assertOk()
        ->assertJsonCount(2, 'data');
});

it('filters upcoming events', function () {
    Event::factory()->create(['status' => 'published', 'starts_at' => now()->addDays(5)]);
    Event::factory()->create(['status' => 'published', 'starts_at' => now()->subDays(5)]);

    $response = $this->getJson('/api/v1/events?upcoming=1');

    $response->assertOk()
        ->assertJsonCount(1, 'data');
});

it('searches events by name', function () {
    Event::factory()->create(['name' => 'Rock Concert', 'status' => 'published']);
    Event::factory()->create(['name' => 'Jazz Festival', 'status' => 'published']);

    $response = $this->getJson('/api/v1/events?search=Rock');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Rock Concert');
});

it('sorts events by start date', function () {
    $first = Event::factory()->create(['starts_at' => now()->addDays(1), 'status' => 'published']);
    $second = Event::factory()->create(['starts_at' => now()->addDays(5), 'status' => 'published']);

    $response = $this->getJson('/api/v1/events?sort=starts_at');

    $response->assertOk()
        ->assertJsonPath('data.0.id', $first->id)
        ->assertJsonPath('data.1.id', $second->id);
});

it('shows event details with ticket types', function () {
    $event = Event::factory()->create(['status' => 'published']);
    TicketType::factory()->count(3)->create(['event_id' => $event->id]);

    $response = $this->getJson("/api/v1/events/{$event->slug}");

    $response->assertOk()
        ->assertJsonPath('data.id', $event->id)
        ->assertJsonCount(3, 'data.ticket_types');
});

it('returns 404 for non-existent event', function () {
    $response = $this->getJson('/api/v1/events/non-existent');

    $response->assertNotFound();
});

it('paginates event results', function () {
    Event::factory()->count(25)->create(['status' => 'published']);

    $response = $this->getJson('/api/v1/events?per_page=10');

    $response->assertOk()
        ->assertJsonCount(10, 'data')
        ->assertJsonStructure(['data', 'links', 'meta']);
});
