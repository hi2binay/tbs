<?php

use App\Models\Event;
use App\Models\Reservation;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->event = Event::factory()->create();
});

it('creates a reservation via API', function () {
    $ticketType = TicketType::factory()->create([
        'event_id' => $this->event->id,
        'total_quantity' => 100,
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/reservations', [
            'ticket_type_id' => $ticketType->id,
            'quantity' => 2,
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.quantity', 2)
        ->assertJsonPath('data.status', 'pending');

    $this->assertDatabaseHas('reservations', [
        'user_id' => $this->user->id,
        'ticket_type_id' => $ticketType->id,
        'quantity' => 2,
        'status' => 'pending',
    ]);

    $ticketType->refresh();
    expect($ticketType->reserved_count)->toBe(2);
});

it('requires authentication to create reservation', function () {
    $ticketType = TicketType::factory()->create(['event_id' => $this->event->id]);

    $response = $this->postJson('/api/v1/reservations', [
        'ticket_type_id' => $ticketType->id,
        'quantity' => 2,
    ]);

    $response->assertUnauthorized();
});

it('validates reservation quantity', function () {
    $ticketType = TicketType::factory()->create(['event_id' => $this->event->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/reservations', [
            'ticket_type_id' => $ticketType->id,
            'quantity' => 0,
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['quantity']);
});

it('returns error when tickets unavailable', function () {
    $ticketType = TicketType::factory()->create([
        'event_id' => $this->event->id,
        'total_quantity' => 10,
        'sold_count' => 10,
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/reservations', [
            'ticket_type_id' => $ticketType->id,
            'quantity' => 1,
        ]);

    $response->assertStatus(422);
});

it('enforces per-user limit via API', function () {
    $ticketType = TicketType::factory()->create([
        'event_id' => $this->event->id,
        'total_quantity' => 100,
        'per_user_limit' => 3,
    ]);

    Reservation::factory()->create([
        'user_id' => $this->user->id,
        'ticket_type_id' => $ticketType->id,
        'quantity' => 2,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/reservations', [
            'ticket_type_id' => $ticketType->id,
            'quantity' => 2,
        ]);

    $response->assertStatus(422);
});

it('handles idempotency key in API request', function () {
    $ticketType = TicketType::factory()->create([
        'event_id' => $this->event->id,
        'total_quantity' => 100,
    ]);

    $idempotencyKey = 'test-key-123';

    $first = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/reservations', [
            'ticket_type_id' => $ticketType->id,
            'quantity' => 2,
        ], [
            'X-Idempotency-Key' => $idempotencyKey,
        ]);

    $second = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/reservations', [
            'ticket_type_id' => $ticketType->id,
            'quantity' => 2,
        ], [
            'X-Idempotency-Key' => $idempotencyKey,
        ]);

    $first->assertCreated();
    $second->assertCreated();

    expect($first->json('data.id'))->toBe($second->json('data.id'));

    $ticketType->refresh();
    expect($ticketType->reserved_count)->toBe(2);
});

it('lists user reservations', function () {
    Reservation::factory()->count(3)->create(['user_id' => $this->user->id]);
    Reservation::factory()->count(2)->create();

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/reservations');

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

it('cancels a reservation via API', function () {
    $ticketType = TicketType::factory()->create([
        'event_id' => $this->event->id,
        'reserved_count' => 2,
    ]);

    $reservation = Reservation::factory()->create([
        'user_id' => $this->user->id,
        'ticket_type_id' => $ticketType->id,
        'quantity' => 2,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->deleteJson("/api/v1/reservations/{$reservation->id}");

    $response->assertOk();

    $reservation->refresh();
    expect($reservation->status)->toBe('canceled');

    $ticketType->refresh();
    expect($ticketType->reserved_count)->toBe(0);
});
