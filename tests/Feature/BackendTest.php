<?php

use App\Models\Event;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true]);
});

describe('Event Management', function () {
    it('lists events in admin panel', function () {
        Event::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)
            ->get('/backend/events');

        $response->assertOk()
            ->assertViewHas('events');
    });

    it('shows event creation form', function () {
        $response = $this->actingAs($this->admin)
            ->get('/backend/events/create');

        $response->assertOk();
    });

    it('creates new event', function () {
        $response = $this->actingAs($this->admin)
            ->post('/backend/events', [
                'name' => 'New Event',
                'slug' => 'new-event',
                'description' => 'Event description',
                'venue' => 'Main Hall',
                'location' => 'City Center',
                'status' => 'draft',
                'starts_at' => now()->addDays(30)->format('Y-m-d H:i:s'),
                'ends_at' => now()->addDays(30)->addHours(3)->format('Y-m-d H:i:s'),
            ]);

        $response->assertRedirect('/backend/events');

        $this->assertDatabaseHas('events', [
            'name' => 'New Event',
            'slug' => 'new-event',
        ]);
    });

    it('updates existing event', function () {
        $event = Event::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($this->admin)
            ->put("/backend/events/{$event->id}", [
                'name' => 'Updated Name',
                'slug' => $event->slug,
                'description' => $event->description,
                'venue' => $event->venue,
                'location' => $event->location,
                'status' => $event->status,
                'starts_at' => $event->starts_at->format('Y-m-d H:i:s'),
                'ends_at' => $event->ends_at->format('Y-m-d H:i:s'),
            ]);

        $response->assertRedirect("/backend/events/{$event->id}/edit");

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'name' => 'Updated Name',
        ]);
    });

    it('deletes event', function () {
        $event = Event::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete("/backend/events/{$event->id}");

        $response->assertRedirect('/backend/events');

        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    });
});

describe('Ticket Type Management', function () {
    it('creates ticket type for event', function () {
        $event = Event::factory()->create();

        $response = $this->actingAs($this->admin)
            ->post('/backend/ticket-types', [
                'event_id' => $event->id,
                'name' => 'VIP Ticket',
                'description' => 'VIP access',
                'price_cents' => 10000,
                'total_quantity' => 100,
                'per_user_limit' => 5,
                'currency' => 'USD',
                'is_active' => true,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('ticket_types', [
            'event_id' => $event->id,
            'name' => 'VIP Ticket',
            'price_cents' => 10000,
        ]);
    });

    it('updates ticket type', function () {
        $ticketType = TicketType::factory()->create(['price_cents' => 5000]);

        $response = $this->actingAs($this->admin)
            ->put("/backend/ticket-types/{$ticketType->id}", [
                'event_id' => $ticketType->event_id,
                'name' => $ticketType->name,
                'description' => $ticketType->description,
                'price_cents' => 7500,
                'total_quantity' => $ticketType->total_quantity,
                'currency' => $ticketType->currency,
                'is_active' => $ticketType->is_active,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('ticket_types', [
            'id' => $ticketType->id,
            'price_cents' => 7500,
        ]);
    });

    it('deletes ticket type', function () {
        $ticketType = TicketType::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete("/backend/ticket-types/{$ticketType->id}");

        $response->assertRedirect();

        $this->assertDatabaseMissing('ticket_types', ['id' => $ticketType->id]);
    });
});

describe('User Management', function () {
    it('lists users in admin panel', function () {
        User::factory()->count(10)->create();

        $response = $this->actingAs($this->admin)
            ->get('/backend/users');

        $response->assertOk()
            ->assertViewHas('users');
    });

    it('shows user details', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get("/backend/users/{$user->id}");

        $response->assertOk()
            ->assertViewHas('user', $user);
    });
});

describe('Booking Management', function () {
    it('lists all bookings', function () {
        \App\Models\Booking::factory()->count(10)->create();

        $response = $this->actingAs($this->admin)
            ->get('/backend/bookings');

        $response->assertOk()
            ->assertViewHas('bookings');
    });

    it('shows booking details', function () {
        $booking = \App\Models\Booking::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get("/backend/bookings/{$booking->id}");

        $response->assertOk()
            ->assertViewHas('booking', $booking);
    });

    it('filters bookings by status', function () {
        \App\Models\Booking::factory()->count(3)->create(['status' => 'confirmed']);
        \App\Models\Booking::factory()->count(2)->create(['status' => 'refunded']);

        $response = $this->actingAs($this->admin)
            ->get('/backend/bookings?status=confirmed');

        $response->assertOk();
    });
});

describe('Authorization', function () {
    it('prevents non-admin access to backend', function () {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)
            ->get('/backend/events');

        $response->assertForbidden();
    });

    it('requires authentication for backend access', function () {
        $response = $this->get('/backend/events');

        $response->assertRedirect('/login');
    });
});
