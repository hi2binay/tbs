# Testing Guide

This guide explains how to run and understand the comprehensive test suite for the ticketing system.

## Table of Contents

- [Quick Start](#quick-start)
- [Test Structure](#test-structure)
- [Running Tests](#running-tests)
- [Concurrency Testing](#concurrency-testing)
- [Load Testing with External Tools](#load-testing-with-external-tools)
- [CI/CD Integration](#cicd-integration)
- [Writing New Tests](#writing-new-tests)

## Quick Start

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage

# Run specific test file
php artisan test tests/Unit/ReservationServiceTest.php

# Run parallel tests (faster)
php artisan test --parallel
```

## Test Structure

### Unit Tests (`tests/Unit/`)

Tests individual components in isolation:

- **ReservationServiceTest**: Core reservation logic
  - `reserve()` - ticket reservation with inventory management
  - `confirm()` - converting reservations to bookings
  - `expire()` - handling expired reservations
  - `cancel()` - canceling pending reservations
  - Boundary conditions (zero, exact capacity, negative)
  - Per-user limits enforcement
  - Idempotency key handling

- **PaymentManagerTest**: Payment gateway driver resolution
  - Stripe, Razorpay, PayPal, UPI driver loading
  - Configuration validation
  - Driver caching

### Feature Tests (`tests/Feature/`)

Tests API endpoints and user flows:

- **EventTest**: Event API endpoints
  - Listing events (published, draft, upcoming)
  - Searching and filtering
  - Sorting by date
  - Pagination
  - Event details with ticket types

- **ReservationTest**: Reservation API
  - Creating reservations via API
  - Authentication requirements
  - Validation rules
  - Idempotency headers
  - Listing user reservations
  - Canceling reservations

- **BookingTest**: Complete booking flow
  - End-to-end reservation → payment → booking
  - Listing bookings
  - Viewing booking details
  - Authorization checks
  - Booking code generation
  - Price calculations

- **AuthTest**: Authentication system
  - User registration
  - Login/logout
  - Sanctum token generation
  - Validation rules
  - Duplicate prevention

- **BackendTest**: Admin panel CRUD operations
  - Event management (create, update, delete)
  - Ticket type management
  - User management
  - Booking management
  - Authorization (admin-only access)

### Concurrency Tests (`tests/Feature/Concurrency/`)

**THE CRITICAL TEST SUITE** - Validates system integrity under concurrent load:

- **ConcurrencyTest**: Race condition prevention
  - 50 concurrent users attempting to reserve tickets
  - 1000 requests for limited ticket inventory
  - Concurrent booking confirmations
  - Mixed operations (reserve, confirm, cancel)
  - Database constraint validation

#### Running Concurrency Tests

```bash
# Run all concurrency tests
php artisan test tests/Feature/Concurrency/

# Run specific concurrency test
php artisan test --filter concurrency

# Run with parallel execution
php artisan test tests/Feature/Concurrency/ --parallel --processes=10
```

**Key Assertions:**
- `sold_count + reserved_count ≤ total_quantity` (NEVER exceeded)
- No phantom reservations (DB count matches model count)
- Atomic operations (no partial updates)
- Proper exception handling for out-of-stock scenarios

### Integration Tests (`tests/Feature/Integration/`)

Tests complete user journeys across multiple components:

- **BookingFlowTest**: End-to-end scenarios
  - Complete flow: reserve → payment → confirm → notification
  - Expiry flow: reserve → wait → auto-expire → inventory restored
  - Refund flow: booking → refund → inventory restored
  - Multiple sequential operations
  - Data consistency validation

## Running Tests

### Basic Commands

```bash
# Run all tests
php artisan test

# Run with output
php artisan test --verbose

# Run specific test
php artisan test --filter test_name

# Run tests in a directory
php artisan test tests/Feature/

# Stop on first failure
php artisan test --stop-on-failure
```

### Advanced Options

```bash
# Run tests in parallel (faster for large suites)
php artisan test --parallel --processes=4

# Run with code coverage
php artisan test --coverage --min=80

# Run specific test suite
php artisan test --testsuite=Unit

# Filter by group
php artisan test --group=concurrency
```

### Database Considerations

All tests use the `RefreshDatabase` trait, which:
- Migrates a fresh database before each test
- Rolls back changes after each test
- Ensures test isolation

**Default test database:** SQLite in-memory (fast, no configuration needed)

To use a different database for testing:

```env
# .env.testing
DB_CONNECTION=mysql
DB_DATABASE=ticketing_test
```

## Concurrency Testing

### Understanding the Concurrency Tests

The concurrency tests validate that the ticketing system maintains data integrity under high concurrent load. This is **critical** for preventing overselling.

#### Test Scenarios

1. **Basic Concurrency** (50 users, 100 tickets)
   - Validates basic race condition handling
   - Ensures no overselling occurs
   - Tests database locking mechanisms

2. **High Contention** (500 users, 50 tickets)
   - 10:1 demand ratio
   - Maximum race condition pressure
   - Validates proper exception handling

3. **Concurrent Confirmations** (10 simultaneous confirmations)
   - Tests payment confirmation race conditions
   - Validates inventory transfer (reserved → sold)
   - Ensures atomic operations

4. **Mixed Operations** (reserve + confirm + cancel)
   - Real-world scenario with multiple operation types
   - Tests inventory consistency across operations
   - Validates proper locking at all stages

### Local Concurrency Testing

```bash
# Run basic concurrency test
php artisan test --filter "handles concurrent reservation requests"

# Run with more aggressive parallel execution
php artisan test tests/Feature/Concurrency/ --parallel --processes=10

# Monitor database during test
# (in separate terminal)
watch -n 1 'php artisan tinker --execute="echo App\Models\TicketType::find(1)"'
```

### Scaling to 1000+ Concurrent Requests

Pest PHP tests run in-process and have limitations for massive concurrency. For 1000+ concurrent requests, use external load testing tools.

## Load Testing with External Tools

### Using Apache Bench (ab)

Simple HTTP load testing:

```bash
# Install (if not available)
# Windows: comes with Apache
# Mac: brew install httpd
# Linux: apt-get install apache2-utils

# Run load test
ab -n 1000 -c 100 -p reserve.json -T application/json \
   -H "Authorization: Bearer YOUR_TOKEN" \
   http://localhost:8000/api/v1/reservations

# reserve.json:
{
  "ticket_type_id": 1,
  "quantity": 1
}
```

### Using k6 (Recommended)

Modern load testing with detailed metrics:

```bash
# Install k6
# Windows: choco install k6
# Mac: brew install k6
# Linux: snap install k6

# Run load test
k6 run tests/k6/concurrency-test.js
```

**Create `tests/k6/concurrency-test.js`:**

```javascript
import http from 'k6/http';
import { check } from 'k6';

export const options = {
  vus: 1000, // 1000 concurrent users
  duration: '30s',
  thresholds: {
    http_req_duration: ['p(95)<500'], // 95% of requests under 500ms
    http_req_failed: ['rate<0.1'],    // Less than 10% errors
  },
};

export default function () {
  const url = 'http://localhost:8000/api/v1/reservations';
  const payload = JSON.stringify({
    ticket_type_id: 1,
    quantity: 1,
  });

  const params = {
    headers: {
      'Content-Type': 'application/json',
      'Authorization': 'Bearer YOUR_TOKEN',
    },
  };

  const res = http.post(url, payload, params);
  
  check(res, {
    'status is 201 or 422': (r) => r.status === 201 || r.status === 422,
    'no 500 errors': (r) => r.status !== 500,
  });
}
```

Run the test:

```bash
# Basic run
k6 run tests/k6/concurrency-test.js

# With detailed output
k6 run --out json=results.json tests/k6/concurrency-test.js

# Ramping load test
k6 run --vus 1 --duration 10s --vus 500 --duration 20s --vus 1000 --duration 30s tests/k6/concurrency-test.js
```

### Using Artillery

Alternative load testing tool:

```bash
# Install
npm install -g artillery

# Create tests/artillery/concurrency.yml:
```

```yaml
config:
  target: 'http://localhost:8000'
  phases:
    - duration: 60
      arrivalRate: 100
      name: "Sustained load"
  defaults:
    headers:
      Authorization: 'Bearer YOUR_TOKEN'

scenarios:
  - name: "Reserve tickets"
    flow:
      - post:
          url: "/api/v1/reservations"
          json:
            ticket_type_id: 1
            quantity: 1
```

Run:

```bash
artillery run tests/artillery/concurrency.yml
```

### Analyzing Results

After load testing, verify:

```bash
# Check ticket inventory integrity
php artisan tinker

>>> $tt = App\Models\TicketType::find(1);
>>> $tt->sold_count + $tt->reserved_count <= $tt->total_quantity; // Must be true
>>> App\Models\Reservation::where('ticket_type_id', 1)
      ->whereIn('status', ['pending', 'confirmed'])
      ->sum('quantity') === $tt->sold_count + $tt->reserved_count; // Must be true
```

## CI/CD Integration

### GitHub Actions

```yaml
# .github/workflows/tests.yml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          extensions: mbstring, pdo, pdo_sqlite
          
      - name: Install Dependencies
        run: composer install --prefer-dist --no-progress
        
      - name: Run Tests
        run: php artisan test --parallel --coverage --min=80
        
      - name: Run Concurrency Tests
        run: php artisan test tests/Feature/Concurrency/
```

### GitLab CI

```yaml
# .gitlab-ci.yml
test:
  image: php:8.2
  script:
    - composer install
    - php artisan test --parallel
    - php artisan test tests/Feature/Concurrency/
```

## Writing New Tests

### Pest PHP Syntax

```php
<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Simple test
it('does something', function () {
    expect(true)->toBeTrue();
});

// Test with setup
beforeEach(function () {
    $this->user = User::factory()->create();
});

it('uses setup data', function () {
    expect($this->user)->toBeInstanceOf(User::class);
});

// Grouped tests
describe('feature group', function () {
    it('tests feature A', function () {
        // ...
    });
    
    it('tests feature B', function () {
        // ...
    });
});
```

### Best Practices

1. **Test Isolation**: Each test should be independent
   ```php
   uses(RefreshDatabase::class); // Fresh DB for each test
   ```

2. **Descriptive Names**: Use clear, intention-revealing names
   ```php
   it('prevents reserving more tickets than available', function () {
       // ...
   });
   ```

3. **Arrange-Act-Assert Pattern**:
   ```php
   it('creates a booking', function () {
       // Arrange
       $user = User::factory()->create();
       $ticketType = TicketType::factory()->create();
       
       // Act
       $booking = createBooking($user, $ticketType);
       
       // Assert
       expect($booking)->toBeInstanceOf(Booking::class);
   });
   ```

4. **Factory Usage**: Use factories for test data
   ```php
   User::factory()->count(10)->create();
   Event::factory()->create(['status' => 'published']);
   ```

5. **Test One Thing**: Each test should validate one behavior
   ```php
   // Good
   it('validates email format', function () { /* ... */ });
   it('validates email uniqueness', function () { /* ... */ });
   
   // Bad
   it('validates email', function () {
       // Tests format AND uniqueness
   });
   ```

## Test Coverage

Generate coverage report:

```bash
# Terminal output
php artisan test --coverage

# HTML report
php artisan test --coverage-html coverage/

# Minimum coverage threshold
php artisan test --coverage --min=80
```

View HTML report:
```bash
# Windows
start coverage/index.html

# Mac/Linux
open coverage/index.html
```

## Troubleshooting

### Tests Failing Intermittently

- Database locking issues: Ensure proper transaction handling
- Race conditions: Check concurrency tests specifically
- Time-dependent tests: Use `$this->travel()` for time manipulation

### Slow Test Execution

```bash
# Use parallel execution
php artisan test --parallel

# Use in-memory SQLite
# Ensure DB_CONNECTION=sqlite in .env.testing
```

### Database Seeding Issues

```bash
# Clear and re-migrate
php artisan migrate:fresh --env=testing

# Check test database configuration
php artisan tinker --env=testing
>>> DB::connection()->getDatabaseName()
```

## Additional Resources

- [Pest PHP Documentation](https://pestphp.com/)
- [Laravel Testing Guide](https://laravel.com/docs/testing)
- [k6 Documentation](https://k6.io/docs/)
- [Artillery Documentation](https://artillery.io/docs/)

## Summary

The test suite provides comprehensive coverage:

- ✅ **Unit Tests**: Core business logic validation
- ✅ **Feature Tests**: API endpoint behavior
- ✅ **Concurrency Tests**: Race condition prevention (CRITICAL)
- ✅ **Integration Tests**: End-to-end user journeys
- ✅ **Load Tests**: 1000+ concurrent request handling (external tools)

**Key Metric**: The system MUST maintain `sold_count + reserved_count ≤ total_quantity` under ALL conditions.
