@extends('layouts.app')

@section('title', $event->title)

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-8">
            @if($event->image_url)
                <img src="{{ $event->image_url }}" class="img-fluid rounded mb-4" alt="{{ $event->title }}">
            @else
                <div class="bg-secondary rounded mb-4" style="height: 400px;"></div>
            @endif

            <h1 class="mb-3">{{ $event->title }}</h1>

            <div class="mb-4">
                <span class="badge bg-info">{{ ucfirst($event->category) }}</span>
                <span class="badge bg-success">{{ ucfirst($event->status) }}</span>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Event Details</h5>
                    <p class="mb-2"><strong>Start:</strong> {{ $event->starts_at->format('l, F d, Y - h:i A') }}</p>
                    <p class="mb-2"><strong>End:</strong> {{ $event->ends_at->format('l, F d, Y - h:i A') }}</p>
                    <p class="mb-2"><strong>Venue:</strong> {{ $event->venue->name }}</p>
                    <p class="mb-0"><strong>Address:</strong> {{ $event->venue->address }}, {{ $event->venue->city }}</p>
                    @if($event->venue->capacity)
                        <p class="mb-0"><strong>Capacity:</strong> {{ number_format($event->venue->capacity) }}</p>
                    @endif
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Description</h5>
                    <p class="card-text">{!! nl2br(e($event->description)) !!}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-body">
                    <h5 class="card-title mb-4">Ticket Types</h5>

                    @forelse($event->ticketTypes as $ticketType)
                        <div class="border-bottom pb-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-1">{{ $ticketType->name }}</h6>
                                    <small class="text-muted">{{ $ticketType->description }}</small>
                                </div>
                                <h5 class="text-primary mb-0">${{ number_format($ticketType->price, 2) }}</h5>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    Available: {{ $ticketType->available_quantity }} / {{ $ticketType->total_quantity }}
                                </small>
                                @if($ticketType->available_quantity > 0)
                                    <span class="badge bg-success">Available</span>
                                @else
                                    <span class="badge bg-danger">Sold Out</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-muted">No tickets available yet.</p>
                    @endforelse

                    @if($event->ticketTypes->where('available_quantity', '>', 0)->count() > 0)
                        @auth
                            <a href="{{ route('bookings.create', $event) }}" class="btn btn-primary w-100 mt-3">Book Tickets</a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-primary w-100 mt-3">Login to Book</a>
                        @endauth
                    @else
                        <button class="btn btn-secondary w-100 mt-3" disabled>Sold Out</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
