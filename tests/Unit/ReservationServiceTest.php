<?php

use App\Models\Event;
use App\Models\Reservation;
use App\Models\TicketType;
use App\Models\User;
use App\Services\Reservation\OutOfStockException;
use App\Services\Reservation\PerUserLimitException;
use App\Services\Reservation\ReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->service = new ReservationService();
    $this->event = Event::factory()->create();
    $this->user = User::factory()->create();
});

describe('reserve()', function () {
    it('successfully reserves tickets', function () {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'total_quantity' => 100,
            'sold_count' => 0,
            'reserved_count' => 0,
        ]);

        $reservation = $this->service->reserve($ticketType->id, 5, $this->user->id);

        expect($reservation)->toBeInstanceOf(Reservation::class)
            ->and($reservation->quantity)->toBe(5)
            ->and($reservation->status)->toBe('pending')
            ->and($reservation->user_id)->toBe($this->user->id);

        $ticketType->refresh();
        expect($ticketType->reserved_count)->toBe(5);
    });

    it('reserves tickets at exact capacity', function () {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'total_quantity' => 100,
            'sold_count' => 95,
            'reserved_count' => 0,
        ]);

        $reservation = $this->service->reserve($ticketType->id, 5, $this->user->id);

        expect($reservation->quantity)->toBe(5);
        $ticketType->refresh();
        expect($ticketType->reserved_count)->toBe(5)
            ->and($ticketType->available_quantity)->toBe(0);
    });

    it('throws exception when quantity exceeds available', function () {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'total_quantity' => 100,
            'sold_count' => 95,
            'reserved_count' => 0,
        ]);

        expect(fn () => $this->service->reserve($ticketType->id, 6, $this->user->id))
            ->toThrow(OutOfStockException::class);
    });

    it('enforces per-user limits', function () {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'total_quantity' => 100,
            'per_user_limit' => 5,
        ]);

        Reservation::factory()->create([
            'user_id' => $this->user->id,
            'ticket_type_id' => $ticketType->id,
            'quantity' => 3,
            'status' => 'pending',
        ]);

        expect(fn () => $this->service->reserve($ticketType->id, 3, $this->user->id))
            ->toThrow(PerUserLimitException::class);
    });

    it('respects idempotency key', function () {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'total_quantity' => 100,
        ]);

        $key = 'unique-key-123';

        $first = $this->service->reserve($ticketType->id, 5, $this->user->id, $key);
        $second = $this->service->reserve($ticketType->id, 5, $this->user->id, $key);

        expect($first->id)->toBe($second->id);

        $ticketType->refresh();
        expect($ticketType->reserved_count)->toBe(5);
    });

    it('handles boundary condition of zero available tickets', function () {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'total_quantity' => 100,
            'sold_count' => 100,
            'reserved_count' => 0,
        ]);

        expect(fn () => $this->service->reserve($ticketType->id, 1, $this->user->id))
            ->toThrow(OutOfStockException::class);
    });

    it('counts confirmed reservations in per-user limit', function () {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'total_quantity' => 100,
            'per_user_limit' => 5,
        ]);

        Reservation::factory()->create([
            'user_id' => $this->user->id,
            'ticket_type_id' => $ticketType->id,
            'quantity' => 2,
            'status' => 'confirmed',
        ]);

        Reservation::factory()->create([
            'user_id' => $this->user->id,
            'ticket_type_id' => $ticketType->id,
            'quantity' => 2,
            'status' => 'pending',
        ]);

        expect(fn () => $this->service->reserve($ticketType->id, 2, $this->user->id))
            ->toThrow(PerUserLimitException::class);
    });
});

describe('confirm()', function () {
    it('confirms a pending reservation and creates booking', function () {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'total_quantity' => 100,
            'sold_count' => 0,
            'reserved_count' => 5,
            'price_cents' => 5000,
        ]);

        $reservation = Reservation::factory()->create([
            'user_id' => $this->user->id,
            'ticket_type_id' => $ticketType->id,
            'quantity' => 5,
            'status' => 'pending',
            'expires_at' => now()->addMinutes(10),
        ]);

        $booking = $this->service->confirm($reservation->id, 'pi_test_123');

        expect($booking)->toBeInstanceOf(\App\Models\Booking::class)
            ->and($booking->status)->toBe('confirmed')
            ->and($booking->total_amount_cents)->toBe(25000);

        $reservation->refresh();
        expect($reservation->status)->toBe('confirmed');

        $ticketType->refresh();
        expect($ticketType->sold_count)->toBe(5)
            ->and($ticketType->reserved_count)->toBe(0);
    });

    it('throws exception when confirming expired reservation', function () {
        $ticketType = TicketType::factory()->create(['event_id' => $this->event->id]);

        $reservation = Reservation::factory()->create([
            'ticket_type_id' => $ticketType->id,
            'status' => 'pending',
            'expires_at' => now()->subMinute(),
        ]);

        expect(fn () => $this->service->confirm($reservation->id, 'pi_test_123'))
            ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
    });
});

describe('expire()', function () {
    it('expires a reservation and restores inventory', function () {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'total_quantity' => 100,
            'sold_count' => 0,
            'reserved_count' => 5,
        ]);

        $reservation = Reservation::factory()->create([
            'ticket_type_id' => $ticketType->id,
            'quantity' => 5,
            'status' => 'pending',
        ]);

        $this->service->expire($reservation);

        $reservation->refresh();
        expect($reservation->status)->toBe('expired');

        $ticketType->refresh();
        expect($ticketType->reserved_count)->toBe(0);
    });

    it('does not expire already confirmed reservations', function () {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'reserved_count' => 0,
        ]);

        $reservation = Reservation::factory()->create([
            'ticket_type_id' => $ticketType->id,
            'quantity' => 5,
            'status' => 'confirmed',
        ]);

        $this->service->expire($reservation);

        $reservation->refresh();
        expect($reservation->status)->toBe('confirmed');

        $ticketType->refresh();
        expect($ticketType->reserved_count)->toBe(0);
    });
});

describe('cancel()', function () {
    it('cancels a pending reservation and restores inventory', function () {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'total_quantity' => 100,
            'sold_count' => 0,
            'reserved_count' => 5,
        ]);

        $reservation = Reservation::factory()->create([
            'ticket_type_id' => $ticketType->id,
            'quantity' => 5,
            'status' => 'pending',
        ]);

        $this->service->cancel($reservation->id);

        $reservation->refresh();
        expect($reservation->status)->toBe('canceled');

        $ticketType->refresh();
        expect($ticketType->reserved_count)->toBe(0);
    });

    it('throws exception when canceling non-pending reservation', function () {
        $reservation = Reservation::factory()->create([
            'status' => 'confirmed',
        ]);

        expect(fn () => $this->service->cancel($reservation->id))
            ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
    });
});
