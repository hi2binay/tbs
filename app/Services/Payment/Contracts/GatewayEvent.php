<?php

namespace App\Services\Payment\Contracts;

class GatewayEvent
{
    public function __construct(
        public string $type,
        public string $intentId,
        public string $status,
        public int $amountCents,
        public ?string $paymentId = null,
        public ?array $metadata = null
    ) {}
}
