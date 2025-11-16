<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Booking $booking
    ) {
        $this->booking->load(['reservation.event', 'reservation.ticketType', 'items']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Booking Confirmation - '.$this->booking->reservation->event->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-confirmation',
            with: [
                'booking' => $this->booking,
                'event' => $this->booking->reservation->event,
                'ticketType' => $this->booking->reservation->ticketType,
                'reservation' => $this->booking->reservation,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
