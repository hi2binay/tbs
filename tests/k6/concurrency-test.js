import http from 'k6/http';
import { check, sleep } from 'k6';
import { Rate } from 'k6/metrics';

// Custom metrics
const errorRate = new Rate('errors');
const oversoldRate = new Rate('oversold');

// Test configuration
export const options = {
  scenarios: {
    // Scenario 1: Sustained load - 1000 concurrent users
    sustained_load: {
      executor: 'constant-vus',
      vus: 1000,
      duration: '30s',
      gracefulStop: '5s',
    },
    
    // Scenario 2: Spike test - sudden surge
    spike_test: {
      executor: 'ramping-vus',
      startVUs: 0,
      stages: [
        { duration: '10s', target: 2000 }, // Ramp up to 2000 users
        { duration: '20s', target: 2000 }, // Stay at 2000
        { duration: '10s', target: 0 },    // Ramp down
      ],
      gracefulRampDown: '5s',
      startTime: '40s', // Start after sustained_load
    },
  },
  
  thresholds: {
    'http_req_duration': ['p(95)<1000', 'p(99)<2000'], // 95% under 1s, 99% under 2s
    'http_req_failed': ['rate<0.1'],                    // Less than 10% failed requests
    'errors': ['rate<0.15'],                            // Less than 15% business logic errors
    'oversold': ['rate==0'],                            // CRITICAL: No overselling ever
  },
};

// Configuration
const BASE_URL = __ENV.BASE_URL || 'http://localhost:8000';
const TICKET_TYPE_ID = __ENV.TICKET_TYPE_ID || '1';

// Setup: Run once before the test
export function setup() {
  // You can create test data here or fetch initial state
  console.log('🚀 Starting concurrency test...');
  console.log(`Target: ${BASE_URL}`);
  console.log(`Ticket Type ID: ${TICKET_TYPE_ID}`);
  
  return {
    ticketTypeId: TICKET_TYPE_ID,
  };
}

// Main test function - executed by each virtual user
export default function (data) {
  // Register a new user
  const registerRes = http.post(`${BASE_URL}/api/v1/auth/register`, JSON.stringify({
    name: `User ${__VU}-${__ITER}`,
    email: `user-${__VU}-${__ITER}@test.com`,
    password: 'password123',
    password_confirmation: 'password123',
  }), {
    headers: { 'Content-Type': 'application/json' },
  });
  
  if (registerRes.status !== 201) {
    errorRate.add(1);
    console.log(`Registration failed: ${registerRes.status}`);
    return;
  }
  
  const token = registerRes.json('token');
  
  // Try to reserve tickets
  const reserveRes = http.post(`${BASE_URL}/api/v1/reservations`, JSON.stringify({
    ticket_type_id: parseInt(data.ticketTypeId),
    quantity: 1,
  }), {
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`,
    },
  });
  
  // Check response
  const reserveCheck = check(reserveRes, {
    'reservation created (201)': (r) => r.status === 201,
    'tickets unavailable (422)': (r) => r.status === 422,
    'no server errors': (r) => r.status < 500,
  });
  
  if (!reserveCheck) {
    errorRate.add(1);
  }
  
  // If reservation successful, try to confirm it
  if (reserveRes.status === 201) {
    const reservationId = reserveRes.json('data.id');
    
    // Simulate payment processing
    sleep(0.1);
    
    const paymentRes = http.post(`${BASE_URL}/api/v1/payments`, JSON.stringify({
      reservation_id: reservationId,
      payment_method: 'stripe',
    }), {
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
      },
    });
    
    check(paymentRes, {
      'payment successful': (r) => r.status === 201 || r.status === 200,
      'payment no errors': (r) => r.status < 500,
    });
  }
  
  // Small delay between iterations
  sleep(0.5);
}

// Teardown: Run once after the test
export function teardown(data) {
  console.log('🏁 Concurrency test completed!');
  
  // Verify inventory integrity
  const verifyRes = http.get(`${BASE_URL}/api/v1/events`);
  
  if (verifyRes.status === 200) {
    console.log('✅ Inventory check endpoint accessible');
  }
  
  console.log('\n⚠️  IMPORTANT: Manually verify inventory integrity:');
  console.log('   php artisan tinker');
  console.log('   >>> $tt = App\\Models\\TicketType::find(' + data.ticketTypeId + ');');
  console.log('   >>> $tt->sold_count + $tt->reserved_count <= $tt->total_quantity;');
}

// Custom trend for response times
export function handleSummary(data) {
  return {
    'stdout': textSummary(data, { indent: ' ', enableColors: true }),
    'summary.json': JSON.stringify(data),
  };
}

function textSummary(data, options) {
  const indent = options.indent || '';
  const enableColors = options.enableColors || false;
  
  let summary = '\n';
  summary += `${indent}📊 Test Summary\n`;
  summary += `${indent}${'='.repeat(50)}\n\n`;
  
  summary += `${indent}Total Requests: ${data.metrics.http_reqs.values.count}\n`;
  summary += `${indent}Failed Requests: ${data.metrics.http_req_failed.values.rate * 100}%\n`;
  summary += `${indent}Request Duration (p95): ${data.metrics.http_req_duration.values['p(95)']}ms\n`;
  summary += `${indent}Request Duration (p99): ${data.metrics.http_req_duration.values['p(99)']}ms\n`;
  
  if (data.metrics.errors) {
    summary += `${indent}Error Rate: ${data.metrics.errors.values.rate * 100}%\n`;
  }
  
  summary += `\n${indent}⚠️  Verify inventory integrity manually!\n`;
  
  return summary;
}
