<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create(Event $event)
    {
        $event->load('ticketTypes');

        return view('bookings.create', compact('event'));
    }

    public function store(Request $request, Event $event)
    {
        $validated = $request->validate([
            'tickets' => 'required|array|min:1',
            'tickets.*.ticket_type_id' => 'required|exists:ticket_types,id',
            'tickets.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:card,bank_transfer,cash',
        ]);

        try {
            DB::beginTransaction();

            $totalAmount = 0;
            $ticketData = [];

            foreach ($validated['tickets'] as $ticketItem) {
                $ticketType = TicketType::findOrFail($ticketItem['ticket_type_id']);
                $quantity = $ticketItem['quantity'];

                if ($ticketType->available_quantity < $quantity) {
                    throw new \Exception("Not enough tickets available for {$ticketType->name}");
                }

                $totalAmount += $ticketType->price * $quantity;
                $ticketData[] = [
                    'ticket_type' => $ticketType,
                    'quantity' => $quantity,
                ];
            }

            $booking = Booking::create([
                'user_id' => auth()->id(),
                'event_id' => $event->id,
                'total_amount' => $totalAmount,
                'payment_method' => $validated['payment_method'],
                'payment_status' => 'pending',
                'booking_status' => 'confirmed',
            ]);

            foreach ($ticketData as $item) {
                $ticketType = $item['ticket_type'];
                $quantity = $item['quantity'];

                for ($i = 0; $i < $quantity; $i++) {
                    $booking->tickets()->create([
                        'ticket_type_id' => $ticketType->id,
                        'price' => $ticketType->price,
                        'status' => 'valid',
                    ]);
                }

                $ticketType->decrement('available_quantity', $quantity);
            }

            DB::commit();

            return redirect()->route('bookings.show', $booking)
                ->with('success', 'Booking created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);

        $booking->load(['event.venue', 'tickets.ticketType']);

        return view('bookings.show', compact('booking'));
    }
}
