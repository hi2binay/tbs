<?php

namespace App\Services\Payment\Contracts;

class PaymentIntent
{
    public function __construct(
        public string $id,
        public string $status,
        public ?string $clientSecret = null,
        public ?array $metadata = null
    ) {}
}
