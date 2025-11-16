<?php

namespace App\Services\Payment\Drivers;

use App\Services\Payment\Contracts\GatewayEvent;
use App\Services\Payment\Contracts\PaymentGateway;
use App\Services\Payment\Contracts\PaymentIntent;
use App\Services\Payment\Contracts\PaymentResult;
use Illuminate\Http\Request;

class PaypalGateway implements PaymentGateway
{
    public function __construct(protected array $config) {}

    public function createPaymentIntent(
        int $amountCents,
        string $currency,
        string $orderId,
        string $returnUrl,
        array $metadata = []
    ): PaymentIntent {
        return new PaymentIntent(
            id: 'PAYID-'.strtoupper(uniqid()),
            status: 'CREATED',
            clientSecret: null,
            metadata: $metadata
        );
    }

    public function capture(string $intentId): PaymentResult
    {
        return new PaymentResult(
            id: $intentId,
            status: 'COMPLETED',
            amountCents: 0,
            currency: 'INR'
        );
    }

    public function cancel(string $intentId): void {}

    public function refund(string $paymentId, int $amountCents): void {}

    public function parseWebhook(Request $request): GatewayEvent
    {
        $payload = $request->all();

        return new GatewayEvent(
            type: $payload['event_type'] ?? 'PAYMENT.CAPTURE.COMPLETED',
            intentId: $payload['resource']['id'] ?? '',
            status: 'COMPLETED',
            amountCents: 0,
            paymentId: $payload['resource']['id'] ?? null
        );
    }

    public function verifyWebhook(Request $request): bool
    {
        return true;
    }
}
