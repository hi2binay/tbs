@extends('backend.layouts.admin')

@section('title', 'Edit Event')
@section('page-title', 'Edit Event')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Event Details</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('backend.events.update', $event) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label">Event Name *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name', $event->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description *</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="5" required>{{ old('description', $event->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="venue" class="form-label">Venue *</label>
                            <input type="text" class="form-control @error('venue') is-invalid @enderror" 
                                   id="venue" name="venue" value="{{ old('venue', $event->venue) }}" required>
                            @error('venue')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="location" class="form-label">Location *</label>
                            <input type="text" class="form-control @error('location') is-invalid @enderror" 
                                   id="location" name="location" value="{{ old('location', $event->location) }}" required>
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="starts_at" class="form-label">Starts At *</label>
                            <input type="datetime-local" class="form-control @error('starts_at') is-invalid @enderror" 
                                   id="starts_at" name="starts_at" 
                                   value="{{ old('starts_at', $event->starts_at->format('Y-m-d\TH:i')) }}" required>
                            @error('starts_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="ends_at" class="form-label">Ends At *</label>
                            <input type="datetime-local" class="form-control @error('ends_at') is-invalid @enderror" 
                                   id="ends_at" name="ends_at" 
                                   value="{{ old('ends_at', $event->ends_at->format('Y-m-d\TH:i')) }}" required>
                            @error('ends_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status *</label>
                        <select class="form-select @error('status') is-invalid @enderror" 
                                id="status" name="status" required>
                            <option value="draft" {{ old('status', $event->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ old('status', $event->status) === 'published' ? 'selected' : '' }}>Published</option>
                            <option value="cancelled" {{ old('status', $event->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('backend.events.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Event</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Ticket Types</h5>
                <a href="{{ route('backend.ticket-types.create', $event) }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus"></i> Add
                </a>
            </div>
            <div class="card-body">
                @forelse($event->ticketTypes as $ticketType)
                    <div class="border-bottom pb-2 mb-2">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>{{ $ticketType->name }}</strong>
                                <div class="small text-muted">
                                    ${{ number_format($ticketType->price_cents / 100, 2) }} | Qty: {{ $ticketType->total_quantity }}
                                </div>
                            </div>
                            <div>
                                <a href="{{ route('backend.ticket-types.edit', [$event, $ticketType]) }}" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('backend.ticket-types.destroy', [$event, $ticketType]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center">No ticket types yet</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
