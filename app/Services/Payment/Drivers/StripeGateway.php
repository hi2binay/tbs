<?php

namespace App\Services\Payment\Drivers;

use App\Services\Payment\Contracts\GatewayEvent;
use App\Services\Payment\Contracts\PaymentGateway;
use App\Services\Payment\Contracts\PaymentIntent;
use App\Services\Payment\Contracts\PaymentResult;
use Illuminate\Http\Request;

class StripeGateway implements PaymentGateway
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
            id: 'pi_'.uniqid(),
            status: 'requires_payment_method',
            clientSecret: 'secret_'.uniqid(),
            metadata: $metadata
        );
    }

    public function capture(string $intentId): PaymentResult
    {
        return new PaymentResult(
            id: $intentId,
            status: 'captured',
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
            type: $payload['type'] ?? 'payment_intent.succeeded',
            intentId: $payload['data']['object']['id'] ?? '',
            status: 'captured',
            amountCents: $payload['data']['object']['amount'] ?? 0,
            paymentId: $payload['data']['object']['id'] ?? null
        );
    }

    public function verifyWebhook(Request $request): bool
    {
        return true;
    }
}
