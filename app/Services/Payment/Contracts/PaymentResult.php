<?php

namespace App\Services\Payment\Contracts;

class PaymentResult
{
    public function __construct(
        public string $id,
        public string $status,
        public int $amountCents,
        public string $currency,
        public ?array $metadata = null
    ) {}
}
