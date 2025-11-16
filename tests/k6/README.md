# k6 Load Testing

This directory contains k6 load testing scripts for the ticketing system.

## Installation

### Windows
```bash
choco install k6
```

### macOS
```bash
brew install k6
```

### Linux
```bash
snap install k6
```

Or download from: https://k6.io/docs/get-started/installation/

## Running Tests

### Basic Concurrency Test

```bash
# Run with defaults (localhost:8000, ticket_type_id=1)
k6 run concurrency-test.js

# Run with custom configuration
k6 run --env BASE_URL=http://localhost:8000 --env TICKET_TYPE_ID=1 concurrency-test.js

# Run with custom scenario
k6 run --vus 2000 --duration 60s concurrency-test.js
```

### Before Running

1. **Prepare the database:**
   ```bash
   php artisan migrate:fresh --seed
   ```

2. **Start the server:**
   ```bash
   php artisan serve
   # Or for production testing:
   composer dev
   ```

3. **Create test ticket type:**
   ```bash
   php artisan tinker
   >>> $event = App\Models\Event::factory()->create(['status' => 'published']);
   >>> $tt = App\Models\TicketType::factory()->create([
         'event_id' => $event->id,
         'total_quantity' => 1000,
         'price_cents' => 5000,
       ]);
   >>> echo $tt->id; // Use this ID in the test
   ```

### Interpreting Results

The test will output metrics like:

```
📊 Test Summary
==================================================

Total Requests: 15000
Failed Requests: 5.2%
Request Duration (p95): 450ms
Request Duration (p99): 890ms
Error Rate: 8.3%
```

#### Key Metrics

- **http_req_duration (p95)**: 95% of requests completed under this time
- **http_req_failed**: Percentage of HTTP errors (4xx, 5xx)
- **errors**: Business logic errors (out of stock is expected)
- **oversold**: CRITICAL - must be 0% (no overselling)

### After Running

**CRITICAL: Verify inventory integrity**

```bash
php artisan tinker

>>> $tt = App\Models\TicketType::find(1);
>>> $sold = $tt->sold_count;
>>> $reserved = $tt->reserved_count;
>>> $total = $tt->total_quantity;
>>> 
>>> echo "Sold: $sold\n";
>>> echo "Reserved: $reserved\n";
>>> echo "Total: $total\n";
>>> echo "Available: " . ($total - $sold - $reserved) . "\n";
>>> 
>>> // CRITICAL CHECK
>>> if ($sold + $reserved <= $total) {
      echo "✅ PASS: No overselling detected\n";
    } else {
      echo "❌ FAIL: OVERSELLING DETECTED!\n";
    }
>>>
>>> // Verify reservation counts
>>> $actualReserved = App\Models\Reservation::where('ticket_type_id', $tt->id)
      ->whereIn('status', ['pending', 'confirmed'])
      ->sum('quantity');
>>> if ($actualReserved === $sold + $reserved) {
      echo "✅ PASS: Reservation counts match\n";
    } else {
      echo "❌ FAIL: Count mismatch!\n";
    }
```

## Test Scenarios

### Scenario 1: Sustained Load
- 1000 concurrent users
- 30 seconds duration
- Tests steady-state performance

### Scenario 2: Spike Test
- Ramps from 0 → 2000 users in 10s
- Maintains 2000 users for 20s
- Ramps down to 0 in 10s
- Tests system response to traffic spikes

## Customization

Edit `concurrency-test.js` to customize:

```javascript
export const options = {
  scenarios: {
    custom_scenario: {
      executor: 'constant-vus',
      vus: 500,              // Number of concurrent users
      duration: '60s',       // Test duration
    },
  },
  thresholds: {
    'http_req_duration': ['p(95)<500'],  // Performance targets
  },
};
```

## Common Issues

### Port Already in Use
```bash
# Kill existing server
# Windows:
netstat -ano | findstr :8000
taskkill /PID <PID> /F

# Mac/Linux:
lsof -ti:8000 | xargs kill
```

### Database Locked
```bash
# Reset test database
php artisan migrate:fresh --env=testing
```

### High Error Rate
- Check server logs: `storage/logs/laravel.log`
- Verify database can handle connections
- Increase database connection pool
- Check queue worker is running

## Advanced Usage

### Output to JSON
```bash
k6 run --out json=results.json concurrency-test.js
```

### Output to InfluxDB (for Grafana)
```bash
k6 run --out influxdb=http://localhost:8086/k6 concurrency-test.js
```

### Cloud Execution
```bash
k6 cloud run concurrency-test.js
```

## Best Practices

1. **Start small**: Test with 10-100 users first
2. **Monitor resources**: Watch CPU, memory, database connections
3. **Clean data**: Reset database between test runs
4. **Verify integrity**: Always check inventory after tests
5. **Production-like**: Use production database engine for accurate results

## Resources

- [k6 Documentation](https://k6.io/docs/)
- [k6 Examples](https://k6.io/docs/examples/)
- [Performance Testing Guide](https://k6.io/docs/test-types/introduction/)
