# Booking Notification System

This document describes the email and SMS notification system for booking confirmations.

## Overview

When a booking is confirmed through the `ReservationService::confirm()` method, the system automatically sends confirmation notifications to the customer via email and optionally SMS.

## Architecture

### Event-Driven Flow

1. **Event Dispatch**: When `ReservationService::confirm()` creates a booking, it dispatches a `BookingCreated` event
2. **Listener Execution**: The `SendBookingNotification` listener receives the event
3. **Notification Sending**: The listener triggers the `BookingConfirmed` notification
4. **Channel Delivery**: The notification is delivered via configured channels (email, SMS)

### Components

#### Events
- **`App\Events\BookingCreated`**: Dispatched when a booking is successfully created

#### Listeners
- **`App\Listeners\SendBookingNotification`**: Handles the `BookingCreated` event and sends notifications
  - Implements `ShouldQueue` for background processing
  - Sends notification only if the booking has an associated user

#### Notifications
- **`App\Notifications\BookingConfirmed`**: Multi-channel notification for booking confirmations
  - Supports email (via Mailable) and SMS (via Nexmo/Twilio)
  - Includes booking code, event details, ticket information
  - QR code placeholder for venue entry

#### Mailables
- **`App\Mail\BookingConfirmation`**: Email message with professional Bootstrap styling
  - Includes event name, date, venue, location
  - Ticket type, quantity, unit price, total amount
  - Booking code (unique identifier)
  - QR code placeholder
  - Call-to-action button

## Email Template

The email template is located at `resources/views/emails/booking-confirmation.blade.php` and includes:

- **Header**: Gradient purple header with "Booking Confirmed" message
- **Success Badge**: Visual confirmation indicator
- **Booking Code**: Prominently displayed unique booking identifier
- **Event Details**: Date, time, venue, location
- **Ticket Information**: Type, quantity, pricing breakdown
- **QR Code Placeholder**: Space for generated QR code (future implementation)
- **Important Notice**: Instructions to present email at venue
- **Footer**: Support links and copyright

### Template Variables

The email view receives the following variables:
- `$booking` - The Booking model instance
- `$event` - The Event model instance
- `$ticketType` - The TicketType model instance
- `$reservation` - The Reservation model instance

## SMS Configuration

### Enabling SMS Notifications

By default, SMS notifications are disabled. To enable them:

1. **Set environment variable**:
   ```env
   SMS_NOTIFICATIONS_ENABLED=true
   ```

2. **Configure SMS provider** (Nexmo/Vonage or Twilio):
   ```env
   SMS_PROVIDER=twilio
   TWILIO_ACCOUNT_SID=your_account_sid
   TWILIO_AUTH_TOKEN=your_auth_token
   TWILIO_FROM_NUMBER=your_phone_number
   ```

3. **Install SMS package** (if using Nexmo):
   ```bash
   composer require laravel/nexmo-notification-channel
   ```

### Mock SMS Implementation

For testing/development, SMS messages are logged instead of sent. Check the application logs to see SMS content:

```
[timestamp] INFO SMS would be sent to: +1234567890
{
    "message": "Booking confirmed! Event Name - Venue\nTickets: 2 x VIP Pass\nCode: BK-ABCD123456\nDate: Nov 15, 2025 7:00 PM",
    "booking_id": 123
}
```

### SMS Message Format

The SMS includes:
- Event name and venue
- Ticket quantity and type
- Booking code
- Event date and time

## Configuration

### Config File: `config/ticketing.php`

```php
'sms_notifications_enabled' => env('SMS_NOTIFICATIONS_ENABLED', false),

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
```

## User Model Requirements

The User model must use the `Notifiable` trait (already implemented):

```php
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;
    
    // User must have 'phone' field for SMS notifications
    protected $fillable = ['name', 'email', 'password', 'phone'];
}
```

## Queue Configuration

Both the listener and notification implement `ShouldQueue` for asynchronous processing. Ensure your queue worker is running:

```bash
php artisan queue:work
```

For development, you can use the `sync` queue driver in `.env`:
```env
QUEUE_CONNECTION=sync
```

## Testing

### Running Notification Tests

```bash
php artisan test --filter BookingNotificationTest
```

### Manual Testing

1. **Fake notifications**:
   ```php
   Notification::fake();
   
   // Create booking
   $booking = $service->confirm($reservationId, $paymentIntentId);
   
   // Assert notification sent
   Notification::assertSentTo($user, BookingConfirmed::class);
   ```

2. **Test email preview** (add route for development):
   ```php
   Route::get('/email-preview', function () {
       $booking = Booking::with(['reservation.event', 'reservation.ticketType'])->first();
       return new \App\Mail\BookingConfirmation($booking);
   });
   ```

## Future Enhancements

1. **QR Code Generation**: Implement actual QR code generation for booking codes
2. **Push Notifications**: Add mobile app push notifications
3. **WhatsApp Integration**: Send confirmations via WhatsApp Business API
4. **Calendar Integration**: Include .ics file attachment for calendar imports
5. **Reminder Notifications**: Send event reminders 24 hours before the event
6. **Ticket PDF**: Generate and attach PDF tickets to emails

## Troubleshooting

### Notifications Not Sending

1. **Check queue is running**: `php artisan queue:work`
2. **Check event is registered**: Verify `EventServiceProvider` in `bootstrap/providers.php`
3. **Check user has email**: Ensure `User` model has valid email address
4. **Check logs**: Review `storage/logs/laravel.log` for errors

### Email Not Delivered

1. **Check mail configuration**: Verify `.env` mail settings
2. **Test mail connection**: `php artisan tinker` → `Mail::raw('Test', function($msg) { $msg->to('test@example.com')->subject('Test'); })`
3. **Check spam folder**: Email might be filtered as spam

### SMS Not Sending

1. **Check SMS is enabled**: `SMS_NOTIFICATIONS_ENABLED=true` in `.env`
2. **Check user has phone**: Verify `User` model has `phone` field populated
3. **Check SMS provider credentials**: Verify Twilio/Nexmo credentials in `.env`
4. **Check logs**: SMS messages are logged even when mocked

## Security Considerations

- Booking codes should be unique and unpredictable (handled by Booking model)
- Email templates should not expose sensitive payment information
- SMS messages should be concise to avoid truncation
- Rate limiting should be applied to prevent notification spam
- User phone numbers should be validated and formatted correctly

## Dependencies

- Laravel 12 framework
- Laravel Mail system
- Laravel Notification system
- Laravel Queue system
- (Optional) `laravel/nexmo-notification-channel` for SMS
- (Optional) Twilio PHP SDK for SMS via Twilio
