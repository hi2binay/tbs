<?php

use App\Services\Payment\Contracts\PaymentGateway;
use App\Services\Payment\Drivers\PaypalGateway;
use App\Services\Payment\Drivers\RazorpayGateway;
use App\Services\Payment\Drivers\StripeGateway;
use App\Services\Payment\Drivers\UpiGateway;
use App\Services\Payment\PaymentManager;

uses(Tests\TestCase::class);

beforeEach(function () {
    $this->manager = new PaymentManager();
});

it('resolves stripe driver', function () {
    config(['ticketing.payments.default' => 'stripe']);
    config(['ticketing.payments.gateways.stripe' => ['key' => 'test']]);

    $driver = $this->manager->driver();

    expect($driver)->toBeInstanceOf(StripeGateway::class)
        ->and($driver)->toBeInstanceOf(PaymentGateway::class);
});

it('resolves razorpay driver', function () {
    config(['ticketing.payments.gateways.razorpay' => ['key' => 'test']]);

    $driver = $this->manager->driver('razorpay');

    expect($driver)->toBeInstanceOf(RazorpayGateway::class);
});

it('resolves paypal driver', function () {
    config(['ticketing.payments.gateways.paypal' => ['client_id' => 'test']]);

    $driver = $this->manager->driver('paypal');

    expect($driver)->toBeInstanceOf(PaypalGateway::class);
});

it('resolves upi driver', function () {
    config(['ticketing.payments.gateways.upi' => ['merchant_id' => 'test']]);

    $driver = $this->manager->driver('upi');

    expect($driver)->toBeInstanceOf(UpiGateway::class);
});

it('throws exception for unconfigured gateway', function () {
    expect(fn () => $this->manager->driver('invalid'))
        ->toThrow(InvalidArgumentException::class, 'Payment gateway [invalid] is not configured');
});

it('throws exception for unsupported gateway', function () {
    config(['ticketing.payments.gateways.unsupported' => ['key' => 'test']]);

    expect(fn () => $this->manager->driver('unsupported'))
        ->toThrow(InvalidArgumentException::class, 'Unsupported payment gateway [unsupported]');
});

it('caches driver instances', function () {
    config(['ticketing.payments.gateways.stripe' => ['key' => 'test']]);

    $first = $this->manager->driver('stripe');
    $second = $this->manager->driver('stripe');

    expect($first)->toBe($second);
});
