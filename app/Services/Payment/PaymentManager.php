<?php

namespace App\Services\Payment;

use App\Services\Payment\Contracts\PaymentGateway;
use App\Services\Payment\Drivers\PaypalGateway;
use App\Services\Payment\Drivers\RazorpayGateway;
use App\Services\Payment\Drivers\StripeGateway;
use App\Services\Payment\Drivers\UpiGateway;
use InvalidArgumentException;

class PaymentManager
{
    protected array $drivers = [];

    public function driver(?string $name = null): PaymentGateway
    {
        $name = $name ?? config('ticketing.payments.default');

        if (! isset($this->drivers[$name])) {
            $this->drivers[$name] = $this->createDriver($name);
        }

        return $this->drivers[$name];
    }

    protected function createDriver(string $name): PaymentGateway
    {
        $config = config("ticketing.payments.gateways.{$name}");

        if (! $config) {
            throw new InvalidArgumentException("Payment gateway [{$name}] is not configured.");
        }

        return match ($name) {
            'stripe' => new StripeGateway($config),
            'razorpay' => new RazorpayGateway($config),
            'paypal' => new PaypalGateway($config),
            'upi' => new UpiGateway($config),
            default => throw new InvalidArgumentException("Unsupported payment gateway [{$name}]."),
        };
    }
}
