<?php

namespace App\Services\Payment\Drivers;

use App\Services\Payment\Contracts\GatewayEvent;
use App\Services\Payment\Contracts\PaymentGateway;
use App\Services\Payment\Contracts\PaymentIntent;
use App\Services\Payment\Contracts\PaymentResult;
use Illuminate\Http\Request;

class UpiGateway implements PaymentGateway
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
            id: 'UPI'.time().rand(1000, 9999),
            status: 'PENDING',
            clientSecret: null,
            metadata: array_merge($metadata, [
                'upi_id' => $this->config['merchant_id'].'@upi',
            ])
        );
    }

    public function capture(string $intentId): PaymentResult
    {
        return new PaymentResult(
            id: $intentId,
            status: 'SUCCESS',
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
            type: $payload['event'] ?? 'TRANSACTION_SUCCESS',
            intentId: $payload['txnId'] ?? '',
            status: 'SUCCESS',
            amountCents: (int) (($payload['amount'] ?? 0) * 100),
            paymentId: $payload['txnId'] ?? null
        );
    }

    public function verifyWebhook(Request $request): bool
    {
        return true;
    }
}
