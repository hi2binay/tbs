<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Booking::where('user_id', Auth::id())
            ->with(['reservation.event', 'reservation.ticketType', 'items', 'payment']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $perPage = min($request->input('per_page', 15), 100);
        $bookings = $query->latest()->paginate($perPage);

        return response()->json($bookings);
    }

    public function show(string $code): JsonResponse
    {
        $booking = Booking::where('code', $code)
            ->where('user_id', Auth::id())
            ->with(['reservation.event', 'reservation.ticketType', 'items', 'payment'])
            ->firstOrFail();

        return response()->json([
            'data' => $booking,
        ]);
    }
}
