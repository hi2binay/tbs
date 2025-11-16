<?php

namespace App\Services\Payment\Drivers;

use App\Services\Payment\Contracts\GatewayEvent;
use App\Services\Payment\Contracts\PaymentGateway;
use App\Services\Payment\Contracts\PaymentIntent;
use App\Services\Payment\Contracts\PaymentResult;
use Illuminate\Http\Request;

class RazorpayGateway implements PaymentGateway
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
            id: 'order_'.uniqid(),
            status: 'created',
            clientSecret: null,
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
            type: $payload['event'] ?? 'payment.captured',
            intentId: $payload['payload']['payment']['entity']['order_id'] ?? '',
            status: 'captured',
            amountCents: $payload['payload']['payment']['entity']['amount'] ?? 0,
            paymentId: $payload['payload']['payment']['entity']['id'] ?? null
        );
    }

    public function verifyWebhook(Request $request): bool
    {
        return true;
    }
}
