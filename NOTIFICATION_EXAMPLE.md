# Notification System - Usage Examples

## Quick Start

### 1. Environment Setup

Add to your `.env` file:

```env
# Email Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@ticketing.local
MAIL_FROM_NAME="Ticket Booking System"

# SMS Configuration (Optional - disabled by default)
SMS_NOTIFICATIONS_ENABLED=false

# If enabling SMS, configure provider:
SMS_PROVIDER=twilio
TWILIO_ACCOUNT_SID=your_account_sid
TWILIO_AUTH_TOKEN=your_auth_token
TWILIO_FROM_NUMBER=+1234567890

# Queue Configuration
QUEUE_CONNECTION=database
```

### 2. Run Queue Worker

For background notification processing:

```bash
php artisan queue:work
```

Or for development (synchronous):

```env
QUEUE_CONNECTION=sync
```

## Usage Examples

### Automatic Notification (Already Implemented)

Notifications are automatically sent when a booking is confirmed:

```php
use App\Services\Reservation\ReservationService;

$service = app(ReservationService::class);

// This automatically triggers the BookingCreated event
// which sends email/SMS notifications
$booking = $service->confirm($reservationId, $paymentIntentId);
```

### Manual Notification Sending

If you need to manually send a notification:

```php
use App\Models\User;
use App\Models\Booking;
use App\Notifications\BookingConfirmed;

$user = User::find(1);
$booking = Booking::with(['reservation.event', 'reservation.ticketType'])->find(1);

// Send notification
$user->notify(new BookingConfirmed($booking));
```

### Testing Notifications

#### Using Notification Fake

```php
use Illuminate\Support\Facades\Notification;
use App\Notifications\BookingConfirmed;

// In your test
Notification::fake();

// Perform action that triggers notification
$service->confirm($reservationId, $paymentIntentId);

// Assert notification was sent
Notification::assertSentTo(
    $user,
    BookingConfirmed::class,
    function ($notification) use ($booking) {
        return $notification->booking->id === $booking->id;
    }
);
```

#### Using Event Fake

```php
use Illuminate\Support\Facades\Event;
use App\Events\BookingCreated;

Event::fake([BookingCreated::class]);

// Perform action
$booking = $service->confirm($reservationId, $paymentIntentId);

// Assert event was dispatched
Event::assertDispatched(BookingCreated::class, function ($event) use ($booking) {
    return $event->booking->id === $booking->id;
});
```

### Preview Email Template

Add a temporary route to preview the email (development only):

```php
// routes/web.php
Route::get('/preview-booking-email', function () {
    $booking = \App\Models\Booking::with([
        'reservation.event',
        'reservation.ticketType'
    ])->first();
    
    return new \App\Mail\BookingConfirmation($booking);
})->middleware('auth');
```

Then visit: `http://localhost/preview-booking-email`

## Code Flow

```
┌─────────────────────────────────────┐
│  ReservationService::confirm()      │
│  Creates Booking                    │
└────────────┬────────────────────────┘
             │
             ▼
┌─────────────────────────────────────┐
│  BookingCreated::dispatch($booking) │
│  Event is fired                     │
└────────────┬────────────────────────┘
             │
             ▼
┌─────────────────────────────────────┐
│  SendBookingNotification::handle()  │
│  Listener receives event            │
└────────────┬────────────────────────┘
             │
             ▼
┌─────────────────────────────────────┐
│  $user->notify(BookingConfirmed)    │
│  Notification is queued             │
└────────────┬────────────────────────┘
             │
             ▼
┌─────────────────────────────────────┐
│  Queue Worker processes job         │
└────────────┬────────────────────────┘
             │
             ├──────────────┬──────────────┐
             ▼              ▼              ▼
    ┌───────────────┐  ┌────────┐  ┌──────────┐
    │ Email Channel │  │  SMS   │  │  Other   │
    │   (Mail)      │  │ (Nexmo)│  │ Channels │
    └───────────────┘  └────────┘  └──────────┘
             │              │
             ▼              ▼
    ┌───────────────┐  ┌────────┐
    │ BookingConf.  │  │  Log   │
    │   Mailable    │  │ (Mock) │
    └───────────────┘  └────────┘
             │
             ▼
    ┌───────────────┐
    │  Email View   │
    │  (.blade.php) │
    └───────────────┘
```

## Customization

### Customize Email Template

Edit `resources/views/emails/booking-confirmation.blade.php`:

```blade
{{-- Add custom branding --}}
<div class="header" style="background: linear-gradient(135deg, #your-color1, #your-color2);">
    <img src="{{ asset('images/logo.png') }}" alt="Logo">
    <h1>{{ config('app.name') }}</h1>
</div>

{{-- Customize content --}}
<p>Dear {{ $booking->user->name }},</p>
```

### Add Additional Notification Channels

```php
// In BookingConfirmed notification
public function via(object $notifiable): array
{
    $channels = ['mail'];
    
    if (config('ticketing.sms_notifications_enabled')) {
        $channels[] = 'nexmo';
    }
    
    // Add push notifications
    if ($notifiable->wantsNotification('push')) {
        $channels[] = 'fcm'; // Firebase Cloud Messaging
    }
    
    // Add Slack notifications (for admin)
    if ($notifiable->isAdmin()) {
        $channels[] = 'slack';
    }
    
    return $channels;
}
```

### Customize SMS Message

Edit the `toNexmo()` method in `app/Notifications/BookingConfirmed.php`:

```php
public function toNexmo(object $notifiable): array
{
    $booking = $this->booking->load(['reservation.event']);
    $event = $booking->reservation->event;
    
    // Shorter message
    $message = "✅ {$event->name} - {$booking->code}";
    
    // Or more detailed
    $message = sprintf(
        "Your booking for %s is confirmed!\nCode: %s\nDate: %s\nVenue: %s",
        $event->name,
        $booking->code,
        $event->starts_at->format('M d, Y'),
        $event->venue
    );
    
    return ['content' => $message];
}
```

## Troubleshooting Commands

```bash
# Clear cache
php artisan cache:clear
php artisan config:clear

# Check queue jobs
php artisan queue:work --once

# Failed jobs
php artisan queue:failed
php artisan queue:retry all

# Test email configuration
php artisan tinker
>>> Mail::raw('Test email', function($msg) { $msg->to('test@example.com')->subject('Test'); });

# Check event listeners are registered
php artisan event:list

# Optimize application
php artisan optimize
```

## Production Checklist

- [ ] Configure production mail server (SMTP, Mailgun, SES, etc.)
- [ ] Set up queue worker as a daemon (Supervisor)
- [ ] Configure SMS provider credentials (if enabled)
- [ ] Test email deliverability
- [ ] Set up monitoring for failed jobs
- [ ] Configure rate limiting for notifications
- [ ] Add unsubscribe links (if required)
- [ ] Implement QR code generation
- [ ] Set up notification logging/tracking
- [ ] Test with real user data
- [ ] Configure retry logic for failed notifications
