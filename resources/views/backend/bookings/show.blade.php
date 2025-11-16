@extends('backend.layouts.admin')

@section('title', 'Booking Details')
@section('page-title', 'Booking Details')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Booking Information</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small">Booking Code</label>
                    <div class="fw-bold">{{ $booking->code }}</div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Status</label>
                    <div>
                        <span class="badge bg-{{ $booking->status === 'confirmed' ? 'success' : ($booking->status === 'pending' ? 'warning' : 'secondary') }}">
                            {{ ucfirst($booking->status) }}
                        </span>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Total Amount</label>
                    <div class="h4 text-success">${{ number_format($booking->total_amount, 2) }}</div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Booked On</label>
                    <div>{{ $booking->created_at->format('M d, Y H:i') }}</div>
                </div>

                @if($booking->status !== 'refunded' && $booking->status !== 'cancelled')
                    <div class="mt-4">
                        <form action="{{ route('backend.bookings.refund', $booking) }}" method="POST" 
                              onsubmit="return confirm('Are you sure you want to refund this booking?')">
                            @csrf
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bi bi-arrow-counterclockwise"></i> Refund Booking
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header bg-white">
                <h5 class="mb-0">Customer Information</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small">Name</label>
                    <div class="fw-bold">{{ $booking->user->name }}</div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Email</label>
                    <div>{{ $booking->user->email }}</div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Phone</label>
                    <div>{{ $booking->user->phone ?? 'N/A' }}</div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('backend.users.show', $booking->user) }}" class="btn btn-sm btn-outline-primary w-100">
                        <i class="bi bi-person"></i> View Customer Profile
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Event Information</h5>
            </div>
            <div class="card-body">
                @if($booking->reservation && $booking->reservation->event)
                    <div class="mb-3">
                        <label class="text-muted small">Event Name</label>
                        <div class="h5">{{ $booking->reservation->event->name }}</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Venue</label>
                            <div>{{ $booking->reservation->event->venue }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Location</label>
                            <div>{{ $booking->reservation->event->location }}</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Starts At</label>
                            <div>{{ $booking->reservation->event->starts_at->format('M d, Y H:i') }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Ends At</label>
                            <div>{{ $booking->reservation->event->ends_at->format('M d, Y H:i') }}</div>
                        </div>
                    </div>
                @else
                    <p class="text-muted">Event information not available</p>
                @endif
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header bg-white">
                <h5 class="mb-0">Booked Tickets</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Ticket Type</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($booking->items as $item)
                                <tr>
                                    <td>{{ $item->ticketType->name ?? 'N/A' }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>${{ number_format($item->price_cents / 100, 2) }}</td>
                                    <td>${{ number_format(($item->price_cents * $item->quantity) / 100, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No items found</td>
                                </tr>
                            @endforelse
                            <tr class="table-active">
                                <td colspan="3" class="text-end"><strong>Total</strong></td>
                                <td><strong>${{ number_format($booking->total_amount, 2) }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if($booking->payment)
            <div class="card mt-3">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Payment Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="text-muted small">Payment Method</label>
                            <div>{{ ucfirst($booking->payment->payment_method) }}</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="text-muted small">Status</label>
                            <div>
                                <span class="badge bg-{{ $booking->payment->status === 'completed' ? 'success' : ($booking->payment->status === 'pending' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($booking->payment->status) }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="text-muted small">Transaction ID</label>
                            <div><small>{{ $booking->payment->transaction_id ?? 'N/A' }}</small></div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
