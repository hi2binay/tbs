<?php

use App\Models\Booking;
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

it('completes full booking flow end-to-end', function () {
    $ticketType = TicketType::factory()->create([
        'event_id' => $this->event->id,
        'total_quantity' => 100,
        'price_cents' => 5000,
    ]);

    $reserveResponse = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/reservations', [
            'ticket_type_id' => $ticketType->id,
            'quantity' => 2,
        ]);

    $reserveResponse->assertCreated();
    $reservationId = $reserveResponse->json('data.id');

    $paymentResponse = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/payments', [
            'reservation_id' => $reservationId,
            'payment_method' => 'stripe',
        ]);

    $paymentResponse->assertCreated();

    $reservation = Reservation::find($reservationId);
    expect($reservation->status)->toBe('confirmed');

    $booking = $reservation->booking;
    expect($booking)->not->toBeNull()
        ->and($booking->status)->toBe('confirmed')
        ->and($booking->total_amount_cents)->toBe(10000)
        ->and($booking->user_id)->toBe($this->user->id);

    $ticketType->refresh();
    expect($ticketType->sold_count)->toBe(2)
        ->and($ticketType->reserved_count)->toBe(0);
});

it('lists user bookings', function () {
    Booking::factory()->count(3)->create(['user_id' => $this->user->id]);
    Booking::factory()->count(2)->create();

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/bookings');

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

it('shows booking details', function () {
    $booking = Booking::factory()->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson("/api/v1/bookings/{$booking->code}");

    $response->assertOk()
        ->assertJsonPath('data.code', $booking->code)
        ->assertJsonPath('data.status', $booking->status);
});

it('prevents accessing other users bookings', function () {
    $otherUser = User::factory()->create();
    $booking = Booking::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson("/api/v1/bookings/{$booking->code}");

    $response->assertForbidden();
});

it('generates unique booking code', function () {
    $booking1 = Booking::factory()->create();
    $booking2 = Booking::factory()->create();

    expect($booking1->code)->not->toBe($booking2->code)
        ->and($booking1->code)->toStartWith('BK-')
        ->and($booking2->code)->toStartWith('BK-');
});

it('calculates total amount correctly', function () {
    $ticketType = TicketType::factory()->create([
        'price_cents' => 2500,
    ]);

    $reservation = Reservation::factory()->create([
        'ticket_type_id' => $ticketType->id,
        'quantity' => 3,
        'status' => 'pending',
        'expires_at' => now()->addMinutes(10),
    ]);

    $booking = app(\App\Services\Reservation\ReservationService::class)
        ->confirm($reservation->id, 'pi_test_123');

    expect($booking->total_amount_cents)->toBe(7500)
        ->and($booking->total_amount)->toBe(75.0);
});

it('includes booking items in response', function () {
    $booking = Booking::factory()
        ->has(\App\Models\BookingItem::factory()->count(2), 'items')
        ->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson("/api/v1/bookings/{$booking->code}");

    $response->assertOk()
        ->assertJsonCount(2, 'data.items');
});

it('filters bookings by status', function () {
    Booking::factory()->count(2)->create(['user_id' => $this->user->id, 'status' => 'confirmed']);
    Booking::factory()->create(['user_id' => $this->user->id, 'status' => 'refunded']);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/bookings?status=confirmed');

    $response->assertOk()
        ->assertJsonCount(2, 'data');
});
