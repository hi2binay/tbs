@extends('layouts.app')

@section('title', 'Welcome')

@section('content')
<div class="bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold">Discover Amazing Events</h1>
                <p class="lead">Book tickets for concerts, festivals, sports, and more!</p>
                <a href="{{ route('events.index') }}" class="btn btn-light btn-lg">Browse Events</a>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <h2 class="mb-4">Upcoming Events</h2>
    <div class="row g-4">
        @php
            $upcomingEvents = \App\Models\Event::where('status', 'published')
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at', 'asc')
                ->limit(6)
                ->get();
        @endphp

        @forelse($upcomingEvents as $event)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    @if($event->image_url)
                        <img src="{{ $event->image_url }}" class="card-img-top" alt="{{ $event->title }}" style="height: 200px; object-fit: cover;">
                    @else
                        <div class="bg-secondary" style="height: 200px;"></div>
                    @endif
                    <div class="card-body">
                        <h5 class="card-title">{{ $event->title }}</h5>
                        <p class="card-text text-muted small">
                            <i class="bi bi-calendar"></i> {{ $event->starts_at->format('M d, Y') }}<br>
                            <i class="bi bi-geo-alt"></i> {{ $event->venue->name }}
                        </p>
                        <p class="card-text">{{ Str::limit($event->description, 100) }}</p>
                    </div>
                    <div class="card-footer bg-white">
                        <a href="{{ route('events.show', $event) }}" class="btn btn-primary btn-sm w-100">View Details</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">No upcoming events at the moment.</div>
            </div>
        @endforelse
    </div>

    @if($upcomingEvents->count() >= 6)
        <div class="text-center mt-4">
            <a href="{{ route('events.index') }}" class="btn btn-outline-primary">View All Events</a>
        </div>
    @endif
</div>
@endsection
