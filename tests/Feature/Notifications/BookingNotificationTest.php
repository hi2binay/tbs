<?php

use App\Events\BookingCreated;
use App\Listeners\SendBookingNotification;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Reservation;
use App\Models\TicketType;
use App\Models\User;
use App\Notifications\BookingConfirmed;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

it('dispatches booking created event when booking is confirmed', function () {
    Event::fake([BookingCreated::class]);

    $user = User::factory()->create();
    $event = Event::factory()->create();
    $ticketType = TicketType::factory()->create([
        'event_id' => $event->id,
        'total_quantity' => 100,
    ]);

    $reservation = Reservation::factory()->create([
        'user_id' => $user->id,
        'event_id' => $event->id,
        'ticket_type_id' => $ticketType->id,
        'status' => 'pending',
    ]);

    $service = app(\App\Services\Reservation\ReservationService::class);
    $booking = $service->confirm($reservation->id, 'test_payment_intent');

    Event::assertDispatched(BookingCreated::class, function ($event) use ($booking) {
        return $event->booking->id === $booking->id;
    });
});

it('sends booking confirmation notification to user', function () {
    Notification::fake();

    $user = User::factory()->create(['phone' => '+1234567890']);
    $event = Event::factory()->create();
    $ticketType = TicketType::factory()->create(['event_id' => $event->id]);

    $reservation = Reservation::factory()->create([
        'user_id' => $user->id,
        'event_id' => $event->id,
        'ticket_type_id' => $ticketType->id,
    ]);

    $booking = Booking::factory()->create([
        'reservation_id' => $reservation->id,
        'user_id' => $user->id,
    ]);

    $listener = new SendBookingNotification;
    $listener->handle(new BookingCreated($booking));

    Notification::assertSentTo($user, BookingConfirmed::class, function ($notification) use ($booking) {
        return $notification->booking->id === $booking->id;
    });
});

it('booking confirmation notification includes correct channels', function () {
    $user = User::factory()->create(['phone' => '+1234567890']);
    $event = Event::factory()->create();
    $ticketType = TicketType::factory()->create(['event_id' => $event->id]);

    $reservation = Reservation::factory()->create([
        'user_id' => $user->id,
        'event_id' => $event->id,
        'ticket_type_id' => $ticketType->id,
    ]);

    $booking = Booking::factory()->create([
        'reservation_id' => $reservation->id,
        'user_id' => $user->id,
    ]);

    $notification = new BookingConfirmed($booking);

    expect($notification->via($user))->toContain('mail');

    config(['ticketing.sms_notifications_enabled' => true]);
    expect($notification->via($user))->toContain('mail', 'nexmo');
});

it('generates correct email data for booking confirmation', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create([
        'name' => 'Test Concert',
        'venue' => 'Test Arena',
    ]);
    $ticketType = TicketType::factory()->create([
        'event_id' => $event->id,
        'name' => 'VIP Pass',
        'price_cents' => 5000,
    ]);

    $reservation = Reservation::factory()->create([
        'user_id' => $user->id,
        'event_id' => $event->id,
        'ticket_type_id' => $ticketType->id,
        'quantity' => 2,
    ]);

    $booking = Booking::factory()->create([
        'reservation_id' => $reservation->id,
        'user_id' => $user->id,
        'total_amount_cents' => 10000,
        'code' => 'BK-TEST123',
    ]);

    $mailable = new \App\Mail\BookingConfirmation($booking);

    expect($mailable->envelope()->subject)->toContain('Test Concert');
    expect($mailable->booking->code)->toBe('BK-TEST123');
});
