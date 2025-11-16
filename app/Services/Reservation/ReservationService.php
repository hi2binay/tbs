<?php

namespace App\Services\Reservation;

use App\Events\BookingCreated;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Reservation;
use App\Models\TicketType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReservationService
{
    public function reserve(
        int $ticketTypeId,
        int $quantity,
        ?int $userId = null,
        ?string $idempotencyKey = null
    ): Reservation {
        if ($idempotencyKey) {
            $existing = Reservation::where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                return $existing;
            }
        }

        return DB::transaction(function () use ($ticketTypeId, $quantity, $userId, $idempotencyKey) {
            $ticketType = TicketType::lockForUpdate()->findOrFail($ticketTypeId);

            $affected = DB::table('ticket_types')
                ->where('id', $ticketTypeId)
                ->whereRaw('(total_quantity - sold_count - reserved_count) >= ?', [$quantity])
                ->update(['reserved_count' => DB::raw("reserved_count + $quantity")]);

            if ($affected === 0) {
                throw new OutOfStockException('Tickets are not available');
            }

            if ($ticketType->per_user_limit && $userId) {
                $userReservedCount = Reservation::where('user_id', $userId)
                    ->where('ticket_type_id', $ticketTypeId)
                    ->whereIn('status', ['pending', 'confirmed'])
                    ->sum('quantity');

                if ($userReservedCount + $quantity > $ticketType->per_user_limit) {
                    DB::table('ticket_types')
                        ->where('id', $ticketTypeId)
                        ->update(['reserved_count' => DB::raw("reserved_count - $quantity")]);

                    throw new PerUserLimitException("You can only reserve up to {$ticketType->per_user_limit} tickets");
                }
            }

            $reservation = Reservation::create([
                'user_id' => $userId,
                'event_id' => $ticketType->event_id,
                'ticket_type_id' => $ticketTypeId,
                'quantity' => $quantity,
                'status' => 'pending',
                'expires_at' => now()->addMinutes(config('ticketing.reservation_ttl', 10)),
                'idempotency_key' => $idempotencyKey ?? Str::uuid(),
            ]);

            return $reservation;
        });
    }

    public function confirm(int $reservationId, string $paymentIntentId): Booking
    {
        return DB::transaction(function () use ($reservationId, $paymentIntentId) {
            $reservation = Reservation::where('id', $reservationId)
                ->where('status', 'pending')
                ->where('expires_at', '>', now())
                ->lockForUpdate()
                ->firstOrFail();

            DB::table('ticket_types')
                ->where('id', $reservation->ticket_type_id)
                ->update([
                    'sold_count' => DB::raw("sold_count + {$reservation->quantity}"),
                    'reserved_count' => DB::raw("reserved_count - {$reservation->quantity}"),
                ]);

            $ticketType = $reservation->ticketType;
            $totalAmount = $ticketType->price_cents * $reservation->quantity;

            $booking = Booking::create([
                'reservation_id' => $reservation->id,
                'user_id' => $reservation->user_id,
                'status' => 'confirmed',
                'total_amount_cents' => $totalAmount,
                'currency' => $ticketType->currency,
            ]);

            BookingItem::create([
                'booking_id' => $booking->id,
                'ticket_type_id' => $ticketType->id,
                'quantity' => $reservation->quantity,
                'unit_price_cents' => $ticketType->price_cents,
            ]);

            $reservation->update([
                'status' => 'confirmed',
                'payment_intent_id' => $paymentIntentId,
            ]);

            BookingCreated::dispatch($booking);

            return $booking;
        });
    }

    public function expire(Reservation $reservation): void
    {
        DB::transaction(function () use ($reservation) {
            if ($reservation->status !== 'pending') {
                return;
            }

            DB::table('ticket_types')
                ->where('id', $reservation->ticket_type_id)
                ->update(['reserved_count' => DB::raw("reserved_count - {$reservation->quantity}")]);

            $reservation->update(['status' => 'expired']);
        });
    }

    public function cancel(int $reservationId): void
    {
        DB::transaction(function () use ($reservationId) {
            $reservation = Reservation::where('id', $reservationId)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->firstOrFail();

            DB::table('ticket_types')
                ->where('id', $reservation->ticket_type_id)
                ->update(['reserved_count' => DB::raw("reserved_count - {$reservation->quantity}")]);

            $reservation->update(['status' => 'canceled']);
        });
    }

    public function expireStaleReservations(): int
    {
        $expiredReservations = Reservation::expired()->get();

        foreach ($expiredReservations as $reservation) {
            $this->expire($reservation);
        }

        return $expiredReservations->count();
    }
}
