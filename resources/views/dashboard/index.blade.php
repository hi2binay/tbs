@extends('layouts.app')

@section('title', 'My Bookings')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h1>My Bookings</h1>
            <p class="text-muted">Manage all your event bookings in one place</p>
        </div>
    </div>

    @forelse($bookings as $booking)
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h5 class="card-title">{{ $booking->event->title }}</h5>
                        <p class="card-text mb-2">
                            <i class="bi bi-calendar"></i> {{ $booking->event->starts_at->format('M d, Y - h:i A') }}<br>
                            <i class="bi bi-geo-alt"></i> {{ $booking->event->venue->name }}, {{ $booking->event->venue->city }}
                        </p>
                        <p class="mb-2">
                            <span class="badge bg-{{ $booking->status === 'confirmed' ? 'success' : ($booking->status === 'canceled' ? 'danger' : 'warning') }}">
                                {{ ucfirst($booking->status) }}
                            </span>
                            <span class="badge bg-{{ $booking->payment_status === 'completed' ? 'success' : ($booking->payment_status === 'failed' ? 'danger' : 'warning') }}">
                                Payment: {{ ucfirst($booking->payment_status) }}
                            </span>
                        </p>
                        <small class="text-muted">
                            Booked on: {{ $booking->created_at->format('M d, Y') }} |
                            Tickets: {{ $booking->tickets->count() }}
                        </small>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <h4 class="text-primary mb-3">${{ number_format($booking->total_amount, 2) }}</h4>
                        <a href="{{ route('bookings.show', $booking) }}" class="btn btn-primary btn-sm">View Details</a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="alert alert-info">
            <h5 class="alert-heading">No bookings yet</h5>
            <p class="mb-0">You haven't made any bookings. <a href="{{ route('events.index') }}" class="alert-link">Browse events</a> to get started!</p>
        </div>
    @endforelse

    <div class="row mt-4">
        <div class="col">
            {{ $bookings->links() }}
        </div>
    </div>
</div>
@endsection
