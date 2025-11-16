<?php

use App\Models\Booking;
use App\Models\Event;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\TicketType;
use App\Models\User;
use App\Services\Reservation\ReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event as EventFacade;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    Notification::fake();
    EventFacade::fake();
    $this->service = new ReservationService();
});

it('completes full booking flow with payment and notification', function () {
    $event = Event::factory()->create();
    $user = User::factory()->create();
    $ticketType = TicketType::factory()->create([
        'event_id' => $event->id,
        'total_quantity' => 100,
        'price_cents' => 5000,
        'sold_count' => 0,
        'reserved_count' => 0,
    ]);

    $reservation = $this->service->reserve($ticketType->id, 3, $user->id);

    expect($reservation->status)->toBe('pending');
    $ticketType->refresh();
    expect($ticketType->reserved_count)->toBe(3);

    $booking = $this->service->confirm($reservation->id, 'pi_test_payment_123');

    expect($booking)->toBeInstanceOf(Booking::class)
        ->and($booking->status)->toBe('confirmed')
        ->and($booking->total_amount_cents)->toBe(15000)
        ->and($booking->user_id)->toBe($user->id);

    $reservation->refresh();
    expect($reservation->status)->toBe('confirmed')
        ->and($reservation->payment_intent_id)->toBe('pi_test_payment_123');

    $ticketType->refresh();
    expect($ticketType->sold_count)->toBe(3)
        ->and($ticketType->reserved_count)->toBe(0)
        ->and($ticketType->available_quantity)->toBe(97);

    EventFacade::assertDispatched(\App\Events\BookingCreated::class);
});

it('handles expiry flow and restores inventory', function () {
    $event = Event::factory()->create();
    $user = User::factory()->create();
    $ticketType = TicketType::factory()->create([
        'event_id' => $event->id,
        'total_quantity' => 100,
        'sold_count' => 0,
        'reserved_count' => 0,
    ]);

    $reservation = $this->service->reserve($ticketType->id, 5, $user->id);

    expect($reservation->status)->toBe('pending');
    $ticketType->refresh();
    expect($ticketType->reserved_count)->toBe(5);

    $this->travel(15)->minutes();

    $expiredCount = $this->service->expireStaleReservations();

    expect($expiredCount)->toBe(1);

    $reservation->refresh();
    expect($reservation->status)->toBe('expired');

    $ticketType->refresh();
    expect($ticketType->reserved_count)->toBe(0)
        ->and($ticketType->available_quantity)->toBe(100);
});

it('processes refund flow and restores inventory', function () {
    $event = Event::factory()->create();
    $user = User::factory()->create();
    $ticketType = TicketType::factory()->create([
        'event_id' => $event->id,
        'total_quantity' => 100,
        'price_cents' => 5000,
        'sold_count' => 0,
        'reserved_count' => 0,
    ]);

    $reservation = $this->service->reserve($ticketType->id, 4, $user->id);
    $booking = $this->service->confirm($reservation->id, 'pi_test_refund');

    $ticketType->refresh();
    expect($ticketType->sold_count)->toBe(4)
        ->and($ticketType->reserved_count)->toBe(0);

    $booking->update(['status' => 'refunded']);

    \Illuminate\Support\Facades\DB::table('ticket_types')
        ->where('id', $ticketType->id)
        ->update(['sold_count' => \Illuminate\Support\Facades\DB::raw('sold_count - 4')]);

    $ticketType->refresh();
    expect($ticketType->sold_count)->toBe(0)
        ->and($ticketType->available_quantity)->toBe(100);
});

it('handles multiple reservations and confirmations in sequence', function () {
    $event = Event::factory()->create();
    $users = User::factory()->count(5)->create();
    $ticketType = TicketType::factory()->create([
        'event_id' => $event->id,
        'total_quantity' => 50,
        'price_cents' => 2500,
        'sold_count' => 0,
        'reserved_count' => 0,
    ]);

    $reservations = [];
    foreach ($users as $user) {
        $reservations[] = $this->service->reserve($ticketType->id, 3, $user->id);
    }

    $ticketType->refresh();
    expect($ticketType->reserved_count)->toBe(15);

    $bookings = [];
    foreach ($reservations as $index => $reservation) {
        $bookings[] = $this->service->confirm($reservation->id, 'pi_test_' . $index);
    }

    expect(count($bookings))->toBe(5);

    $ticketType->refresh();
    expect($ticketType->sold_count)->toBe(15)
        ->and($ticketType->reserved_count)->toBe(0)
        ->and($ticketType->available_quantity)->toBe(35);

    EventFacade::assertDispatched(\App\Events\BookingCreated::class, 5);
});

it('handles partial booking scenario', function () {
    $event = Event::factory()->create();
    $users = User::factory()->count(3)->create();
    $ticketType = TicketType::factory()->create([
        'event_id' => $event->id,
        'total_quantity' => 20,
        'sold_count' => 0,
        'reserved_count' => 0,
    ]);

    $reservation1 = $this->service->reserve($ticketType->id, 8, $users[0]->id);
    $reservation2 = $this->service->reserve($ticketType->id, 8, $users[1]->id);
    $reservation3 = $this->service->reserve($ticketType->id, 4, $users[2]->id);

    $ticketType->refresh();
    expect($ticketType->reserved_count)->toBe(20)
        ->and($ticketType->available_quantity)->toBe(0);

    $booking1 = $this->service->confirm($reservation1->id, 'pi_1');

    $this->service->cancel($reservation2->id);

    $ticketType->refresh();
    expect($ticketType->sold_count)->toBe(8)
        ->and($ticketType->reserved_count)->toBe(4)
        ->and($ticketType->available_quantity)->toBe(8);

    $reservation4 = $this->service->reserve($ticketType->id, 5, $users[1]->id);
    $booking4 = $this->service->confirm($reservation4->id, 'pi_4');

    $ticketType->refresh();
    expect($ticketType->sold_count)->toBe(13)
        ->and($ticketType->reserved_count)->toBe(4)
        ->and($ticketType->available_quantity)->toBe(3);
});

it('maintains data consistency across reservation lifecycle', function () {
    $event = Event::factory()->create();
    $user = User::factory()->create();
    $ticketType = TicketType::factory()->create([
        'event_id' => $event->id,
        'total_quantity' => 100,
        'price_cents' => 7500,
    ]);

    $initialAvailable = $ticketType->available_quantity;

    $reservation = $this->service->reserve($ticketType->id, 10, $user->id);
    $ticketType->refresh();
    expect($ticketType->available_quantity)->toBe($initialAvailable - 10);

    $booking = $this->service->confirm($reservation->id, 'pi_consistency_test');
    $ticketType->refresh();
    expect($ticketType->available_quantity)->toBe($initialAvailable - 10);

    $this->assertDatabaseHas('bookings', [
        'id' => $booking->id,
        'user_id' => $user->id,
        'status' => 'confirmed',
    ]);

    $this->assertDatabaseHas('booking_items', [
        'booking_id' => $booking->id,
        'ticket_type_id' => $ticketType->id,
        'quantity' => 10,
    ]);

    expect($booking->items->count())->toBe(1)
        ->and($booking->items->first()->quantity)->toBe(10);
});
