# 🎫 Laravel Ticket Booking System

A production-ready ticket booking system built with Laravel 12, featuring advanced concurrency control, multi-gateway payments, and comprehensive testing.

## ✨ Features

### Core Functionality
- 🎪 **Event Management** - Create and manage events with multiple ticket types
- 🎟️ **Smart Reservation System** - Atomic concurrency control prevents double-booking
- 💳 **Multi-Gateway Payments** - Stripe, Razorpay, PayPal, UPI support
- 📧 **Automated Notifications** - Email and SMS alerts for bookings
- ⏰ **Auto-Expiry** - Reservations expire after configurable TTL
- 🔐 **Secure Authentication** - Laravel Sanctum for API + web

### Frontend
- 📱 **Responsive Bootstrap 5 UI** - Beautiful event browsing and booking
- 🔍 **Advanced Search** - Filter, sort, and paginate events
- 👤 **User Dashboard** - View booking history and details
- 🎨 **Professional Design** - Modern gradient UI with smooth animations

### Backend Admin Panel
- 📊 **Dashboard** - Real-time statistics (events, bookings, revenue)
- ⚙️ **Full CRUD** - Manage events, tickets, users, bookings
- 💰 **Refund Management** - Process refunds with inventory restoration
- 🔒 **Admin Protection** - Secure middleware-based access control

### Concurrency & Performance
- ⚡ **Atomic Operations** - Conditional UPDATEs prevent race conditions
- 🔒 **Row-Level Locking** - Safe concurrent booking confirmations
- 📈 **Load Tested** - k6 scripts for 10,000+ concurrent requests
- ✅ **Inventory Integrity** - CHECK constraints and reconciliation

### Developer Experience
- 🧪 **74 Tests** - Unit, feature, concurrency, and integration tests
- 📚 **Comprehensive Docs** - 12+ markdown guides
- 🎯 **Pest PHP** - Modern testing with readable syntax
- 🚀 **One-Command Setup** - Get started in minutes

## 🚀 Quick Start

```bash
# Clone and setup
git clone <repo-url>
cd tbs
composer install
npm install

# Configure environment
cp .env.example .env
php artisan key:generate

# Setup database
php artisan migrate --seed

# Build assets
npm run build

# Start development server (runs server, queue, logs, vite)
composer dev
```

Visit `http://localhost:8000` to see the application!

**Default Admin Login:**
- Email: `admin@example.com`
- Password: `password`

## 📖 Documentation

- **[QUICK_START.md](QUICK_START.md)** - Detailed setup guide
- **[AGENTS.md](AGENTS.md)** - Commands and guidelines
- **[FRONTEND.md](FRONTEND.md)** - UI documentation
- **[BACKEND_ADMIN_PANEL.md](BACKEND_ADMIN_PANEL.md)** - Admin guide
- **[NOTIFICATIONS.md](NOTIFICATIONS.md)** - Notification system
- **[TESTING.md](TESTING.md)** - Testing guide
- **[IMPLEMENTATION_CHECKLIST.md](IMPLEMENTATION_CHECKLIST.md)** - Feature status

## 🏗️ Architecture

```
app/
├── Http/Controllers/
│   ├── Api/V1/           # API endpoints (/api/v1/*)
│   ├── Auth/             # Authentication
│   ├── Backend/          # Admin panel (/backend/*)
│   └── *.php             # Frontend controllers
├── Models/               # Eloquent models
├── Services/
│   ├── Reservation/      # Core booking logic
│   └── Payment/          # Payment gateways
└── Notifications/        # Email/SMS notifications
```

## 🔑 Key Concepts

### Concurrency Control
The system uses **atomic conditional UPDATEs** to prevent race conditions:

```php
// Atomic inventory decrement - no overselling possible
DB::table('ticket_types')
  ->where('id', $ticketTypeId)
  ->whereRaw('(total_quantity - sold_count - reserved_count) >= ?', [$qty])
  ->update(['reserved_count' => DB::raw("reserved_count + $qty")]);
```

### Reservation Flow
1. **Reserve** → Atomic inventory decrement, create pending reservation
2. **Payment** → Create payment intent (outside transaction)
3. **Confirm** → Lock reservation, move reserved → sold, create booking
4. **Expire** → Scheduled job restores inventory from expired reservations

### Payment Abstraction
All payment gateways implement `PaymentGateway` interface:
- `createPaymentIntent()` - Initiate payment
- `capture()` - Capture authorized payment
- `webhook()` - Handle provider callbacks
- `refund()` - Process refunds

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run concurrency tests
php artisan test tests/Feature/Concurrency/

# Run with coverage
php artisan test --coverage

# Load test with k6 (10,000 concurrent users)
k6 run tests/k6/concurrency-test.js
```

**Test Coverage:** 74 tests across unit, feature, concurrency, and integration

## 🛠️ Tech Stack

- **Laravel 12** - PHP 8.2+
- **Bootstrap 5** - Responsive UI
- **Laravel Sanctum** - API authentication
- **Pest PHP** - Modern testing
- **Vite** - Asset bundling
- **MySQL/PostgreSQL** - Production database
- **SQLite** - Testing database

## 📊 API Endpoints

### Events
- `GET /api/v1/events` - List events (with search/filter/sort)
- `GET /api/v1/events/{id}` - Event details

### Reservations (requires auth)
- `POST /api/v1/reservations` - Create reservation
- `GET /api/v1/reservations/{id}` - View reservation
- `DELETE /api/v1/reservations/{id}` - Cancel reservation

### Bookings (requires auth)
- `GET /api/v1/bookings` - User's bookings
- `GET /api/v1/bookings/{id}` - Booking details

### Payments
- `POST /api/v1/payments/intent` - Create payment intent
- `POST /api/v1/payments/webhook` - Payment webhook

See [API documentation](docs/api.md) for details.

## 🔐 Security

- CSRF protection on all forms
- SQL injection prevention via Eloquent ORM
- XSS protection in Blade templates
- Password hashing with bcrypt
- API rate limiting
- Admin middleware protection
- Webhook signature verification

## 🌟 Production Deployment

Before deploying to production:

1. **Configure real payment gateways** in `.env`
2. **Setup email service** (SMTP/Mailgun/SES)
3. **Setup SMS service** (Twilio/AWS SNS)
4. **Configure queue driver** (Redis/SQS)
5. **Setup caching** (Redis)
6. **Enable monitoring** (Sentry/Bugsnag)
7. **Run performance tests** with k6
8. **Security audit** and penetration testing

See [deployment guide](docs/deployment.md) for detailed instructions.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
