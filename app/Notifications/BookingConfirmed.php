<?php

namespace App\Notifications;

use App\Mail\BookingConfirmation;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class BookingConfirmed extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Booking $booking
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['mail'];

        if (config('ticketing.sms_notifications_enabled', false)) {
            $channels[] = 'nexmo';
        }

        return $channels;
    }

    public function toMail(object $notifiable): BookingConfirmation
    {
        return new BookingConfirmation($this->booking);
    }

    public function toNexmo(object $notifiable): array
    {
        $booking = $this->booking->load(['reservation.event', 'reservation.ticketType']);
        $event = $booking->reservation->event;
        $ticketType = $booking->reservation->ticketType;

        $message = sprintf(
            "Booking confirmed! %s - %s\nTickets: %d x %s\nCode: %s\nDate: %s",
            $event->name,
            $event->venue,
            $booking->reservation->quantity,
            $ticketType->name,
            $booking->code,
            $event->starts_at->format('M d, Y g:i A')
        );

        Log::info('SMS would be sent to: '.$notifiable->phone, [
            'message' => $message,
            'booking_id' => $this->booking->id,
        ]);

        return [
            'content' => $message,
        ];
    }
}
