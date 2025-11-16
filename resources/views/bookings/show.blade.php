@extends('layouts.app')

@section('title', 'Booking Confirmation')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-4">
                <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-check-lg" viewBox="0 0 16 16">
                        <path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z"/>
                    </svg>
                </div>
                <h1 class="mb-2">Booking Confirmed!</h1>
                <p class="text-muted">Your booking has been successfully created.</p>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Booking Details</h5>
                    <div class="row mb-2">
                        <div class="col-md-4 text-muted">Booking ID:</div>
                        <div class="col-md-8"><strong>{{ $booking->id }}</strong></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4 text-muted">Status:</div>
                        <div class="col-md-8">
                            <span class="badge bg-{{ $booking->status === 'confirmed' ? 'success' : 'warning' }}">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4 text-muted">Payment Status:</div>
                        <div class="col-md-8">
                            <span class="badge bg-{{ $booking->payment_status === 'completed' ? 'success' : 'warning' }}">
                                {{ ucfirst($booking->payment_status) }}
                            </span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-muted">Booked On:</div>
                        <div class="col-md-8">{{ $booking->created_at->format('M d, Y - h:i A') }}</div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Event Information</h5>
                    <h6>{{ $booking->event->title }}</h6>
                    <p class="mb-1">
                        <i class="bi bi-calendar"></i> {{ $booking->event->starts_at->format('l, F d, Y - h:i A') }}
                    </p>
                    <p class="mb-0">
                        <i class="bi bi-geo-alt"></i> {{ $booking->event->venue->name }}, {{ $booking->event->venue->city }}
                    </p>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Tickets ({{ $booking->tickets->count() }})</h5>
                    @foreach($booking->tickets->groupBy('ticket_type_id') as $tickets)
                        @php
                            $ticketType = $tickets->first()->ticketType;
                            $count = $tickets->count();
                        @endphp
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                            <div>
                                <strong>{{ $ticketType->name }}</strong> x {{ $count }}<br>
                                <small class="text-muted">${{ number_format($ticketType->price, 2) }} each</small>
                            </div>
                            <div class="text-end">
                                <strong>${{ number_format($ticketType->price * $count, 2) }}</strong>
                            </div>
                        </div>
                    @endforeach
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <h5 class="mb-0">Total Amount:</h5>
                        <h4 class="text-primary mb-0">${{ number_format($booking->total_amount, 2) }}</h4>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body text-center">
                    <h5 class="card-title mb-3">QR Code</h5>
                    <div class="bg-light p-4 d-inline-block rounded">
                        <div class="bg-white border border-2 p-3" style="width: 200px; height: 200px; display: flex; align-items: center; justify-content: center;">
                            <span class="text-muted">QR Code Placeholder<br><small>Booking #{{ $booking->id }}</small></span>
                        </div>
                    </div>
                    <p class="text-muted mt-3 mb-0">
                        <small>Present this QR code at the event entrance</small>
                    </p>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('dashboard') }}" class="btn btn-primary flex-grow-1">View My Bookings</a>
                <a href="{{ route('events.index') }}" class="btn btn-outline-primary">Browse More Events</a>
            </div>
        </div>
    </div>
</div>
@endsection
