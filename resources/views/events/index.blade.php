@extends('layouts.app')

@section('title', 'Events')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h1>All Events</h1>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col">
            <form method="GET" action="{{ route('events.index') }}" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search events..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                {{ ucfirst($category) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="sort" class="form-select">
                        <option value="date_asc" {{ request('sort') == 'date_asc' ? 'selected' : '' }}>Date (Earliest)</option>
                        <option value="date_desc" {{ request('sort') == 'date_desc' ? 'selected' : '' }}>Date (Latest)</option>
                        <option value="title" {{ request('sort') == 'title' ? 'selected' : '' }}>Title (A-Z)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4">
        @forelse($events as $event)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    @if($event->image_url)
                        <img src="{{ $event->image_url }}" class="card-img-top" alt="{{ $event->title }}" style="height: 200px; object-fit: cover;">
                    @else
                        <div class="bg-secondary d-flex align-items-center justify-content-center" style="height: 200px;">
                            <span class="text-white">No Image</span>
                        </div>
                    @endif
                    <div class="card-body d-flex flex-column">
                        <span class="badge bg-info mb-2 align-self-start">{{ ucfirst($event->category) }}</span>
                        <h5 class="card-title">{{ $event->title }}</h5>
                        <p class="card-text text-muted small mb-2">
                            <i class="bi bi-calendar"></i> {{ $event->starts_at->format('M d, Y - h:i A') }}<br>
                            <i class="bi bi-geo-alt"></i> {{ $event->venue->name }}
                        </p>
                        <p class="card-text flex-grow-1">{{ Str::limit($event->description, 120) }}</p>
                        <a href="{{ route('events.show', $event) }}" class="btn btn-primary mt-auto">View Details</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-warning">No events found matching your criteria.</div>
            </div>
        @endforelse
    </div>

    <div class="row mt-4">
        <div class="col">
            {{ $events->links() }}
        </div>
    </div>
</div>
@endsection
