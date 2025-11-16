# Testing the Notification System

## Quick Test Guide

### 1. Environment Setup

Ensure your `.env` file has email configured:

```env
MAIL_MAILER=log  # For testing - emails saved to logs
# OR for real email testing:
# MAIL_MAILER=smtp
# MAIL_HOST=smtp.mailtrap.io
# MAIL_PORT=2525
# MAIL_USERNAME=your_username
# MAIL_PASSWORD=your_password

QUEUE_CONNECTION=sync  # Process immediately (for testing)
```

### 2. Manual Test via Tinker

```bash
php artisan tinker
```

```php
// Create test data
$user = User::factory()->create([
    'email' => 'test@example.com',
    'phone' => '+1234567890',
    'name' => 'Test User'
]);

$event = Event::factory()->create([
    'name' => 'Test Concert 2025',
    'venue' => 'Test Arena',
    'location' => 'New York, NY',
    'starts_at' => now()->addDays(30),
]);

$ticketType = TicketType::factory()->create([
    'event_id' => $event->id,
    'name' => 'VIP Pass',
    'price_cents' => 5000,
    'total_quantity' => 100,
]);

$reservation = Reservation::factory()->create([
    'user_id' => $user->id,
    'event_id' => $event->id,
    'ticket_type_id' => $ticketType->id,
    'quantity' => 2,
    'status' => 'pending',
    'expires_at' => now()->addMinutes(10),
]);

// Test the full flow
$service = app(\App\Services\Reservation\ReservationService::class);
$booking = $service->confirm($reservation->id, 'test_payment_intent_123');

// Check the booking was created
echo "Booking Code: " . $booking->code . "\n";
echo "Total Amount: " . $booking->currency . " " . ($booking->total_amount_cents / 100) . "\n";

// If using MAIL_MAILER=log, check storage/logs/laravel.log for email
// If using real SMTP, check the inbox
```

### 3. Test Email Preview

Add this route temporarily to `routes/web.php`:

```php
Route::get('/test-email-preview', function () {
    // Create or fetch test booking
    $booking = \App\Models\Booking::factory()->create();
    
    // Load relationships
    $booking->load(['reservation.event', 'reservation.ticketType', 'user']);
    
    // Return mailable preview
    return new \App\Mail\BookingConfirmation($booking);
})->middleware('web');
```

Visit: `http://localhost:8000/test-email-preview`

### 4. Test with Notification Facade

```bash
php artisan tinker
```

```php
use App\Notifications\BookingConfirmed;
use App\Models\User;
use App\Models\Booking;

// Get or create a user
$user = User::first() ?? User::factory()->create(['email' => 'test@example.com']);

// Get or create a booking with relationships
$booking = Booking::with(['reservation.event', 'reservation.ticketType'])->first();

// If no booking exists, create test data:
if (!$booking) {
    $event = Event::factory()->create();
    $ticketType = TicketType::factory()->create(['event_id' => $event->id]);
    $reservation = Reservation::factory()->create([
        'user_id' => $user->id,
        'event_id' => $event->id,
        'ticket_type_id' => $ticketType->id,
    ]);
    $booking = Booking::factory()->create([
        'user_id' => $user->id,
        'reservation_id' => $reservation->id,
    ]);
    $booking->load(['reservation.event', 'reservation.ticketType']);
}

// Send notification
$user->notify(new BookingConfirmed($booking));

echo "Notification sent to: " . $user->email;
```

### 5. Test Event Dispatching

```bash
php artisan tinker
```

```php
use App\Events\BookingCreated;
use App\Models\Booking;

$booking = Booking::with(['reservation.event', 'reservation.ticketType', 'user'])->first();

// Manually dispatch event
BookingCreated::dispatch($booking);

echo "Event dispatched for booking: " . $booking->code;
```

### 6. Check Logs

#### Email Logs (if using log driver)

```bash
# Windows
type storage\logs\laravel.log | findstr "Booking Confirmation"

# Linux/Mac
tail -f storage/logs/laravel.log | grep "Booking Confirmation"
```

#### SMS Logs (mock implementation)

```bash
# Windows
type storage\logs\laravel.log | findstr "SMS would be sent"

# Linux/Mac
tail -f storage/logs/laravel.log | grep "SMS would be sent"
```

### 7. Test with Queue

#### Setup Queue

```bash
# Create jobs table if not exists
php artisan queue:table
php artisan migrate

# Set queue connection in .env
QUEUE_CONNECTION=database
```

#### Dispatch and Process

```bash
# Terminal 1: Start queue worker
php artisan queue:work

# Terminal 2: Trigger notification (via tinker or test)
php artisan tinker
```

```php
// In tinker
$service = app(\App\Services\Reservation\ReservationService::class);
$booking = $service->confirm($reservationId, 'test_payment');

// Check Terminal 1 - you should see job processing
```

### 8. Run Automated Tests

```bash
# Run all notification tests
php artisan test tests/Feature/Notifications/BookingNotificationTest.php

# Run specific test
php artisan test --filter "dispatches booking created event"

# Run with coverage
php artisan test --coverage
```

### 9. Test Email with Mailtrap

1. Sign up at [mailtrap.io](https://mailtrap.io)
2. Get SMTP credentials
3. Update `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@ticketing.test
MAIL_FROM_NAME="Ticket Booking System"
```

4. Trigger notification
5. Check Mailtrap inbox for email

### 10. Test SMS Configuration

#### Enable SMS in config

```env
SMS_NOTIFICATIONS_ENABLED=true
```

#### Check logs for mock SMS

```bash
php artisan tinker
```

```php
// Create user with phone
$user = User::factory()->create([
    'email' => 'test@example.com',
    'phone' => '+1234567890'
]);

// Create and send notification
$booking = Booking::with(['reservation.event', 'reservation.ticketType'])->first();
$user->notify(new \App\Notifications\BookingConfirmed($booking));

// Check logs
exit
```

```bash
type storage\logs\laravel.log | findstr "SMS would be sent to"
```

You should see:
```
[timestamp] INFO SMS would be sent to: +1234567890
{
    "message": "Booking confirmed! ...",
    "booking_id": 123
}
```

## Complete Integration Test

Create a test script: `test-notification-system.php`

```php
<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Event;
use App\Models\TicketType;
use App\Services\Reservation\ReservationService;

echo "=== Notification System Test ===\n\n";

// 1. Create test user
echo "1. Creating test user...\n";
$user = User::factory()->create([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'phone' => '+1234567890',
]);
echo "   ✓ User created: {$user->email}\n\n";

// 2. Create test event
echo "2. Creating test event...\n";
$event = Event::factory()->create([
    'name' => 'Test Concert 2025',
    'venue' => 'Test Arena',
    'location' => 'New York, NY',
    'starts_at' => now()->addDays(30),
]);
echo "   ✓ Event created: {$event->name}\n\n";

// 3. Create ticket type
echo "3. Creating ticket type...\n";
$ticketType = TicketType::factory()->create([
    'event_id' => $event->id,
    'name' => 'VIP Pass',
    'price_cents' => 5000,
    'total_quantity' => 100,
]);
echo "   ✓ Ticket type created: {$ticketType->name} (${$ticketType->price_cents / 100})\n\n";

// 4. Create reservation
echo "4. Creating reservation...\n";
$service = app(ReservationService::class);
$reservation = $service->reserve(
    ticketTypeId: $ticketType->id,
    quantity: 2,
    userId: $user->id
);
echo "   ✓ Reservation created: {$reservation->id}\n\n";

// 5. Confirm booking (triggers notification)
echo "5. Confirming booking (this will trigger notification)...\n";
$booking = $service->confirm($reservation->id, 'test_payment_intent_' . time());
echo "   ✓ Booking confirmed: {$booking->code}\n";
echo "   ✓ Total: {$booking->currency} " . ($booking->total_amount_cents / 100) . "\n\n";

// 6. Check results
echo "6. Notification sent!\n";
echo "   - Email sent to: {$user->email}\n";
echo "   - SMS logged for: {$user->phone}\n\n";

echo "=== Check your email logs or Mailtrap inbox ===\n";
echo "For email logs: storage/logs/laravel.log\n";
echo "For SMS logs: Look for 'SMS would be sent to' in laravel.log\n";
```

Run it:

```bash
php test-notification-system.php
```

## Verification Checklist

After running tests, verify:

- [ ] Email appears in logs or inbox
- [ ] Email contains correct booking code
- [ ] Email shows event name and details
- [ ] Email shows ticket type and quantity
- [ ] Email displays total amount
- [ ] SMS message logged (if enabled)
- [ ] SMS contains booking code
- [ ] Queue jobs processed (if using queue)
- [ ] No errors in laravel.log
- [ ] Event was dispatched
- [ ] Listener was executed
- [ ] Notification was queued/sent

## Troubleshooting

### Notification not sent

1. Check event is registered:
```bash
php artisan event:list
```

2. Check listener is working:
```bash
php artisan tinker
>>> event(new \App\Events\BookingCreated(\App\Models\Booking::first()));
```

3. Check user has email:
```bash
php artisan tinker
>>> User::find(1)->email
```

### Email not received

1. Check mail configuration:
```bash
php artisan tinker
>>> config('mail')
```

2. Test mail directly:
```bash
php artisan tinker
>>> Mail::raw('Test', fn($msg) => $msg->to('test@example.com')->subject('Test'));
```

3. Check logs:
```bash
type storage\logs\laravel.log
```

### Queue not processing

1. Check queue table exists:
```bash
php artisan migrate
```

2. Start queue worker:
```bash
php artisan queue:work --tries=3
```

3. Check failed jobs:
```bash
php artisan queue:failed
php artisan queue:retry all
```

## Clean Up Test Data

```bash
php artisan tinker
```

```php
// Delete test bookings
\App\Models\Booking::where('status', 'confirmed')->delete();
\App\Models\Reservation::truncate();
\App\Models\User::where('email', 'test@example.com')->delete();
```

## Production Testing

Before deploying to production:

1. Test with real email service (not Mailtrap)
2. Test with real SMS provider (Twilio/Nexmo)
3. Verify queue worker is running as daemon
4. Check email deliverability (not in spam)
5. Test with various email clients (Gmail, Outlook, etc.)
6. Verify mobile rendering of email
7. Test SMS character limits
8. Monitor notification delivery rates
9. Set up alerting for failed jobs
10. Configure proper rate limiting
