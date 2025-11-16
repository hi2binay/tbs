# Laravel 12 Ticket Booking System - Agent Guidelines

## Commands
- **Run all tests**: `php artisan test` or `vendor/bin/pest`
- **Run single test file**: `php artisan test tests/Feature/ExampleTest.php`
- **Run specific test**: `php artisan test --filter test_name`
- **Run concurrency tests**: `php artisan test tests/Feature/Concurrency/`
- **Run with coverage**: `php artisan test --coverage --min=80`
- **Run parallel**: `php artisan test --parallel`
- **Code formatting**: `vendor/bin/pint` (Laravel Pint)
- **Dev server**: `composer dev` (runs server, queue, logs, and vite concurrently)
- **Build assets**: `npm run build`
- **Dev assets**: `npm run dev`
- **Expire reservations**: `php artisan reservations:expire`
- **Seed database**: `php artisan db:seed`
- **Fresh migrate+seed**: `php artisan migrate:fresh --seed`
- **Load testing**: `k6 run tests/k6/concurrency-test.js` (requires k6 installation)

## Architecture
- **Framework**: Laravel 12 (PHP 8.2+) with Vite, Bootstrap 5.3, Laravel Sanctum
- **Testing**: Pest PHP (not PHPUnit) - use `it()` and `expect()` syntax
- **Database**: SQLite (testing), MySQL/PostgreSQL (production)
- **Structure**: 
  - Controllers: `app/Http/Controllers/` (web), `app/Http/Controllers/Api/V1/` (API), `app/Http/Controllers/Backend/` (admin)
  - Services: `app/Services/Reservation/`, `app/Services/Payment/`
  - Views: `resources/views/` (frontend), `resources/views/backend/` (admin)
  - API Routes: `/api/v1/*` with Sanctum auth
  - Backend Routes: `/backend/*` with admin middleware
- **Key Features**:
  - Concurrency control via atomic DB operations and row-level locking
  - Multi-gateway payment system (Stripe, Razorpay, PayPal, UPI)
  - Event-driven notifications (email/SMS)
  - Real-time inventory management with reservation expiry

## Code Style
- **Indentation**: 4 spaces (2 for YAML), LF line endings, UTF-8
- **PHP**: Follow Laravel conventions - PSR-4 autoloading, Eloquent models in `app/Models/`
- **Testing**: Use Pest syntax (`it('description', function() {...})`) not PHPUnit classes
- **Formatting**: Always run `vendor/bin/pint` before committing
- **Concurrency**: Use DB::transaction() and conditional UPDATEs for inventory management
- **Payments**: Never call external APIs inside DB transactions

## Important Notes
- ReservationService uses atomic conditional UPDATE for concurrency safety
- Scheduled task runs every minute to expire stale reservations
- Admin user: admin@example.com / password (after seeding)
- Payment gateways are mocked for development (implement real integrations as needed)
- Bootstrap 5 for all frontend views, no Tailwind in views (Tailwind for emails only)
