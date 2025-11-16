<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreReservationRequest;
use App\Models\Reservation;
use App\Services\Reservation\OutOfStockException;
use App\Services\Reservation\PerUserLimitException;
use App\Services\Reservation\ReservationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    public function __construct(
        protected ReservationService $reservationService
    ) {}

    public function store(StoreReservationRequest $request): JsonResponse
    {
        try {
            $reservation = $this->reservationService->reserve(
                ticketTypeId: $request->input('ticket_type_id'),
                quantity: $request->input('quantity'),
                userId: Auth::id(),
                idempotencyKey: $request->input('idempotency_key')
            );

            $reservation->load(['ticketType', 'event']);

            return response()->json([
                'message' => 'Reservation created successfully',
                'data' => $reservation,
            ], 201);
        } catch (OutOfStockException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 409);
        } catch (PerUserLimitException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(int $id): JsonResponse
    {
        $reservation = Reservation::where('id', $id)
            ->where('user_id', Auth::id())
            ->with(['ticketType', 'event', 'booking'])
            ->firstOrFail();

        return response()->json([
            'data' => $reservation,
        ]);
    }

    public function cancel(int $id): JsonResponse
    {
        $reservation = Reservation::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($reservation->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending reservations can be canceled',
            ], 422);
        }

        $this->reservationService->cancel($id);

        return response()->json([
            'message' => 'Reservation canceled successfully',
        ]);
    }
}
