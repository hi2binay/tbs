<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CreatePaymentIntentRequest;
use App\Models\Reservation;
use App\Services\Payment\PaymentManager;
use App\Services\Reservation\ReservationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentManager $paymentManager,
        protected ReservationService $reservationService
    ) {}

    public function createIntent(CreatePaymentIntentRequest $request): JsonResponse
    {
        $reservation = Reservation::where('id', $request->input('reservation_id'))
            ->where('user_id', Auth::id())
            ->with('ticketType')
            ->firstOrFail();

        if ($reservation->status !== 'pending') {
            return response()->json([
                'message' => 'Reservation is not in pending status',
            ], 422);
        }

        if ($reservation->isExpired()) {
            return response()->json([
                'message' => 'Reservation has expired',
            ], 410);
        }

        $amount = $reservation->ticketType->price_cents * $reservation->quantity;

        try {
            $gateway = $this->paymentManager->driver($request->input('payment_method'));

            $paymentIntent = $gateway->createPaymentIntent(
                $amount,
                $reservation->ticketType->currency,
                [
                    'reservation_id' => $reservation->id,
                    'user_id' => $reservation->user_id,
                ]
            );

            $reservation->update([
                'payment_intent_id' => $paymentIntent['id'],
            ]);

            return response()->json([
                'message' => 'Payment intent created successfully',
                'data' => [
                    'payment_intent' => $paymentIntent,
                    'reservation' => $reservation,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Payment intent creation failed', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to create payment intent',
            ], 500);
        }
    }

    public function webhook(Request $request): JsonResponse
    {
        $gateway = $this->paymentManager->driver($request->query('gateway', 'stripe'));

        try {
            $event = $gateway->handleWebhook($request->all());

            if ($event['type'] === 'payment.succeeded') {
                $reservationId = $event['metadata']['reservation_id'] ?? null;
                $paymentIntentId = $event['payment_intent_id'] ?? null;

                if ($reservationId && $paymentIntentId) {
                    $this->reservationService->confirm($reservationId, $paymentIntentId);
                }
            }

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Webhook processing failed', [
                'gateway' => $request->query('gateway'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Webhook processing failed',
            ], 400);
        }
    }
}
