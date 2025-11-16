# 📧 Notification System - Quick Reference Card

## 🚀 Quick Start (3 Steps)

### 1. Configure Email (.env)
```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_FROM_ADDRESS=noreply@ticketing.local
QUEUE_CONNECTION=sync  # or database for background
```

### 2. Start Queue (if using database queue)
```bash
php artisan queue:work
```

### 3. Done! 
Notifications automatically send when bookings are confirmed.

---

## 📁 File Locations

| Component | File Path |
|-----------|-----------|
| **Event** | `app/Events/BookingCreated.php` |
| **Listener** | `app/Listeners/SendBookingNotification.php` |
| **Notification** | `app/Notifications/BookingConfirmed.php` |
| **Mailable** | `app/Mail/BookingConfirmation.php` |
| **Email View** | `resources/views/emails/booking-confirmation.blade.php` |
| **Config** | `config/ticketing.php` |
| **Tests** | `tests/Feature/Notifications/BookingNotificationTest.php` |

---

## 🔄 How It Works

```
Booking Confirmed
       ↓
BookingCreated Event Dispatched
       ↓
SendBookingNotification Listener
       ↓
BookingConfirmed Notification
       ↓
   Email + SMS
```

---

## 📧 Email Includes

✅ Booking code (unique identifier)  
✅ Event name, date, time, venue  
✅ Ticket type, quantity, price  
✅ Total amount  
✅ QR code placeholder  
✅ Professional Bootstrap styling  

---

## 📱 SMS Configuration

### Disabled by Default (Logs Only)
```env
SMS_NOTIFICATIONS_ENABLED=false
```

### To Enable Real SMS
```env
SMS_NOTIFICATIONS_ENABLED=true
SMS_PROVIDER=twilio
TWILIO_ACCOUNT_SID=your_sid
TWILIO_AUTH_TOKEN=your_token
TWILIO_FROM_NUMBER=+1234567890
```

Then install:
```bash
composer require laravel/nexmo-notification-channel
```

---

## 🧪 Quick Test

```bash
php artisan tinker
```

```php
// Create test booking
$user = User::first();
$booking = Booking::with(['reservation.event', 'reservation.ticketType'])->first();

// Send notification
$user->notify(new \App\Notifications\BookingConfirmed($booking));

// Check logs
exit
```

```bash
type storage\logs\laravel.log
```

---

## 👀 Preview Email

Add to `routes/web.php`:
```php
Route::get('/email-preview', function () {
    $booking = \App\Models\Booking::with([
        'reservation.event',
        'reservation.ticketType'
    ])->first();
    return new \App\Mail\BookingConfirmation($booking);
});
```

Visit: `http://localhost/email-preview`

---

## 🔧 Common Commands

```bash
# Check event is registered
php artisan event:list

# Clear config cache
php artisan config:clear

# Test email
php artisan tinker
>>> Mail::raw('Test', fn($m) => $m->to('test@example.com')->subject('Test'));

# Process one queue job
php artisan queue:work --once

# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Run tests
php artisan test --filter BookingNotification
```

---

## 🐛 Troubleshooting

| Issue | Solution |
|-------|----------|
| Email not sent | Check `storage/logs/laravel.log` |
| Queue not processing | Run `php artisan queue:work` |
| Event not firing | Check `EventServiceProvider` registered |
| No notification | Ensure user has email address |

---

## 📊 Monitoring

### Check Email Logs (if using log driver)
```bash
type storage\logs\laravel.log | findstr "Booking Confirmation"
```

### Check SMS Logs
```bash
type storage\logs\laravel.log | findstr "SMS would be sent"
```

### Check Queue Jobs
```bash
php artisan queue:monitor
```

---

## 🎨 Customization

### Change Email Colors
Edit `resources/views/emails/booking-confirmation.blade.php`:
```css
background: linear-gradient(135deg, #YOUR_COLOR1, #YOUR_COLOR2);
```

### Change SMS Message
Edit `app/Notifications/BookingConfirmed.php` → `toNexmo()` method

### Add New Channel
Edit `app/Notifications/BookingConfirmed.php` → `via()` method:
```php
public function via($notifiable): array
{
    return ['mail', 'nexmo', 'slack']; // Add channels here
}
```

---

## ✅ Verification Checklist

Before going to production:

- [ ] Email configuration tested
- [ ] Queue worker running as daemon
- [ ] Email deliverability verified (not spam)
- [ ] SMS configuration tested (if enabled)
- [ ] Failed job monitoring set up
- [ ] Logs being monitored
- [ ] Rate limiting configured
- [ ] Test with real user data

---

## 📚 Documentation Files

- **`NOTIFICATIONS.md`** - Full system documentation
- **`NOTIFICATION_EXAMPLE.md`** - Usage examples  
- **`NOTIFICATION_SYSTEM_SUMMARY.md`** - Implementation summary
- **`NOTIFICATION_FLOW_DIAGRAM.md`** - Architecture diagrams
- **`TESTING_NOTIFICATIONS.md`** - Testing guide
- **`IMPLEMENTATION_CHECKLIST.md`** - Complete checklist
- **`NOTIFICATION_QUICK_REFERENCE.md`** - This file

---

## 💡 Pro Tips

1. **Development**: Use `MAIL_MAILER=log` to save emails to logs
2. **Testing**: Use `QUEUE_CONNECTION=sync` for immediate processing
3. **Production**: Use `QUEUE_CONNECTION=database` with daemon queue worker
4. **Debugging**: Enable `APP_DEBUG=true` in development
5. **Email Testing**: Use [Mailtrap.io](https://mailtrap.io) for safe email testing

---

## 🎯 Key Features

✨ **Automatic** - Sends on booking confirmation  
⚡ **Background** - Queued for performance  
📧 **Professional** - Bootstrap-styled emails  
📱 **Multi-channel** - Email + SMS support  
🔧 **Configurable** - Easy to customize  
🧪 **Tested** - Comprehensive test suite  
📝 **Documented** - Extensive documentation  

---

## 🆘 Need Help?

1. Check logs: `storage/logs/laravel.log`
2. Review documentation: `NOTIFICATIONS.md`
3. Run tests: `php artisan test --filter BookingNotification`
4. Test manually: See `TESTING_NOTIFICATIONS.md`

---

**Status**: ✅ Ready for Production  
**Version**: 1.0.0  
**Last Updated**: 2025-11-15
