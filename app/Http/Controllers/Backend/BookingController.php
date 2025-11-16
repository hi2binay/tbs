<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with(['user', 'reservation.event', 'payment']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('code', 'like', '%'.$request->search.'%')
                    ->orWhereHas('user', function ($q) use ($request) {
                        $q->where('name', 'like', '%'.$request->search.'%')
                            ->orWhere('email', 'like', '%'.$request->search.'%');
                    });
            });
        }

        $bookings = $query->latest()->paginate(15);

        return view('backend.bookings.index', compact('bookings'));
    }

    public function show(Booking $booking)
    {
        $booking->load(['user', 'reservation.event', 'items.ticketType', 'payment']);

        return view('backend.bookings.show', compact('booking'));
    }

    public function refund(Booking $booking)
    {
        if ($booking->status === 'refunded') {
            return redirect()->route('backend.bookings.show', $booking)
                ->with('error', 'Booking has already been refunded.');
        }

        $booking->update(['status' => 'refunded']);

        if ($booking->payment) {
            $booking->payment->update(['status' => 'refunded']);
        }

        return redirect()->route('backend.bookings.show', $booking)
            ->with('success', 'Booking refunded successfully.');
    }
}
