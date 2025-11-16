<?php

namespace App\Listeners;

use App\Events\BookingCreated;
use App\Notifications\BookingConfirmed;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendBookingNotification implements ShouldQueue
{
    public function handle(BookingCreated $event): void
    {
        $booking = $event->booking;

        if ($booking->user) {
            $booking->user->notify(new BookingConfirmed($booking));
        }
    }
}
