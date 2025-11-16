<?php

namespace App\Services\Payment\Contracts;

use Illuminate\Http\Request;

interface PaymentGateway
{
    public function createPaymentIntent(
        int $amountCents,
        string $currency,
        string $orderId,
        string $returnUrl,
        array $metadata = []
    ): PaymentIntent;

    public function capture(string $intentId): PaymentResult;

    public function cancel(string $intentId): void;

    public function refund(string $paymentId, int $amountCents): void;

    public function parseWebhook(Request $request): GatewayEvent;

    public function verifyWebhook(Request $request): bool;
}
