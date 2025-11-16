<?php

return [
    'reservation_ttl' => env('TICKETING_RESERVATION_TTL', 10),

    'payments' => [
        'default' => env('PAYMENT_GATEWAY', 'stripe'),

        'gateways' => [
            'stripe' => [
                'api_key' => env('STRIPE_SECRET_KEY'),
                'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
            ],
            'razorpay' => [
                'key_id' => env('RAZORPAY_KEY_ID'),
                'key_secret' => env('RAZORPAY_KEY_SECRET'),
                'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
            ],
            'paypal' => [
                'client_id' => env('PAYPAL_CLIENT_ID'),
                'client_secret' => env('PAYPAL_CLIENT_SECRET'),
                'mode' => env('PAYPAL_MODE', 'sandbox'),
            ],
            'upi' => [
                'merchant_id' => env('UPI_MERCHANT_ID'),
                'merchant_key' => env('UPI_MERCHANT_KEY'),
            ],
        ],
    ],

    'notifications' => [
        'email' => [
            'enabled' => env('NOTIFY_EMAIL_ENABLED', true),
            'from_address' => env('MAIL_FROM_ADDRESS', 'noreply@ticketing.local'),
            'from_name' => env('MAIL_FROM_NAME', 'Ticketing System'),
        ],
        'sms' => [
            'enabled' => env('NOTIFY_SMS_ENABLED', false),
            'provider' => env('SMS_PROVIDER', 'twilio'),
            'twilio' => [
                'account_sid' => env('TWILIO_ACCOUNT_SID'),
                'auth_token' => env('TWILIO_AUTH_TOKEN'),
                'from_number' => env('TWILIO_FROM_NUMBER'),
            ],
        ],
    ],

    'sms_notifications_enabled' => env('SMS_NOTIFICATIONS_ENABLED', false),

];
