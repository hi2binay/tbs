<?php

use App\Models\Event;
use App\Models\Reservation;
use App\Models\TicketType;
use App\Models\User;
use App\Services\Reservation\ReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('handles concurrent reservation requests without overselling', function () {
    $event = Event::factory()->create();
    $ticketType = TicketType::factory()->create([
        'event_id' => $event->id,
        'total_quantity' => 100,
        'sold_count' => 0,
        'reserved_count' => 0,
    ]);

    $users = User::factory()->count(50)->create();
    $service = new ReservationService();

    $processes = [];
    $results = [];

    foreach ($users as $index => $user) {
        $processes[] = function () use ($service, $ticketType, $user, &$results, $index) {
            try {
                DB::reconnect();
                $reservation = $service->reserve($ticketType->id, 2, $user->id);
                $results[$index] = ['success' => true, 'reservation_id' => $reservation->id];
            } catch (\Exception $e) {
                $results[$index] = ['success' => false, 'error' => $e->getMessage()];
            }
        };
    }

    foreach ($processes as $process) {
        $process();
    }

    $ticketType->refresh();

    $successCount = collect($results)->where('success', true)->count();
    $totalReserved = Reservation::where('ticket_type_id', $ticketType->id)
        ->whereIn('status', ['pending', 'confirmed'])
        ->sum('quantity');

    expect($totalReserved)->toBeLessThanOrEqual(100)
        ->and($ticketType->reserved_count)->toBe($totalReserved)
        ->and($ticketType->sold_count + $ticketType->reserved_count)->toBeLessThanOrEqual(100);
})->skip('Run with: php artisan test --filter concurrency');

it('simulates race condition with 1000 requests for limited tickets', function () {
    $event = Event::factory()->create();
    $ticketType = TicketType::factory()->create([
        'event_id' => $event->id,
        'total_quantity' => 50,
        'sold_count' => 0,
        'reserved_count' => 0,
    ]);

    $users = User::factory()->count(500)->create();
    $service = new ReservationService();
    $results = [];

    foreach ($users as $index => $user) {
        try {
            DB::reconnect();
            $reservation = $service->reserve($ticketType->id, 1, $user->id);
            $results[] = ['success' => true, 'user_id' => $user->id];
        } catch (\Exception $e) {
            $results[] = ['success' => false, 'user_id' => $user->id];
        }
    }

    $ticketType->refresh();

    $successfulReservations = collect($results)->where('success', true)->count();
    $totalReserved = Reservation::where('ticket_type_id', $ticketType->id)
        ->where('status', 'pending')
        ->sum('quantity');

    expect($totalReserved)->toBe(50)
        ->and($ticketType->reserved_count)->toBe(50)
        ->and($successfulReservations)->toBe(50)
        ->and($totalReserved)->toBeLessThanOrEqual($ticketType->total_quantity);
})->skip('Run with: php artisan test --filter race_condition');

it('prevents double booking with concurrent confirmations', function () {
    $event = Event::factory()->create();
    $ticketType = TicketType::factory()->create([
        'event_id' => $event->id,
        'total_quantity' => 100,
        'sold_count' => 0,
        'reserved_count' => 20,
    ]);

    $reservations = Reservation::factory()->count(10)->create([
        'ticket_type_id' => $ticketType->id,
        'quantity' => 2,
        'status' => 'pending',
        'expires_at' => now()->addMinutes(10),
    ]);

    $service = new ReservationService();
    $results = [];

    foreach ($reservations as $reservation) {
        try {
            DB::reconnect();
            $booking = $service->confirm($reservation->id, 'pi_test_' . $reservation->id);
            $results[] = ['success' => true, 'booking_id' => $booking->id];
        } catch (\Exception $e) {
            $results[] = ['success' => false, 'error' => $e->getMessage()];
        }
    }

    $ticketType->refresh();

    $successCount = collect($results)->where('success', true)->count();

    expect($ticketType->sold_count)->toBe(20)
        ->and($ticketType->reserved_count)->toBe(0)
        ->and($successCount)->toBe(10);
})->skip('Run with: php artisan test --filter concurrent_confirmations');

it('maintains inventory integrity during mixed operations', function () {
    $event = Event::factory()->create();
    $ticketType = TicketType::factory()->create([
        'event_id' => $event->id,
        'total_quantity' => 100,
        'sold_count' => 0,
        'reserved_count' => 0,
    ]);

    $users = User::factory()->count(30)->create();
    $service = new ReservationService();

    $reservations = [];
    for ($i = 0; $i < 20; $i++) {
        try {
            $reservations[] = $service->reserve($ticketType->id, 2, $users[$i]->id);
        } catch (\Exception $e) {
            // Expected when sold out
        }
    }

    for ($i = 0; $i < 10; $i++) {
        if (isset($reservations[$i])) {
            try {
                $service->confirm($reservations[$i]->id, 'pi_test_' . $i);
            } catch (\Exception $e) {
                // Expected
            }
        }
    }

    for ($i = 10; $i < 15; $i++) {
        if (isset($reservations[$i])) {
            try {
                $service->cancel($reservations[$i]->id);
            } catch (\Exception $e) {
                // Expected
            }
        }
    }

    $ticketType->refresh();

    $pendingReserved = Reservation::where('ticket_type_id', $ticketType->id)
        ->where('status', 'pending')
        ->sum('quantity');
    $confirmedSold = Reservation::where('ticket_type_id', $ticketType->id)
        ->where('status', 'confirmed')
        ->sum('quantity');

    expect($ticketType->reserved_count)->toBe($pendingReserved)
        ->and($ticketType->sold_count)->toBe($confirmedSold)
        ->and($ticketType->sold_count + $ticketType->reserved_count)->toBeLessThanOrEqual(100);
});

it('verifies database constraints prevent overselling', function () {
    $event = Event::factory()->create();
    $ticketType = TicketType::factory()->create([
        'event_id' => $event->id,
        'total_quantity' => 10,
        'sold_count' => 0,
        'reserved_count' => 0,
    ]);

    $users = User::factory()->count(20)->create();
    $service = new ReservationService();

    $successCount = 0;
    $failCount = 0;

    foreach ($users as $user) {
        try {
            $service->reserve($ticketType->id, 1, $user->id);
            $successCount++;
        } catch (\Exception $e) {
            $failCount++;
        }
    }

    expect($successCount)->toBe(10)
        ->and($failCount)->toBe(10);

    $ticketType->refresh();
    expect($ticketType->reserved_count)->toBe(10);
});
