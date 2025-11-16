@extends('backend.layouts.admin')

@section('title', 'Events')
@section('page-title', 'Events Management')

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">All Events</h5>
        <a href="{{ route('backend.events.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Create Event
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Venue</th>
                        <th>Location</th>
                        <th>Starts At</th>
                        <th>Status</th>
                        <th>Ticket Types</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($events as $event)
                        <tr>
                            <td><strong>{{ $event->name }}</strong></td>
                            <td>{{ $event->venue }}</td>
                            <td>{{ $event->location }}</td>
                            <td>{{ $event->starts_at->format('M d, Y H:i') }}</td>
                            <td>
                                <span class="badge bg-{{ $event->status === 'published' ? 'success' : ($event->status === 'draft' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($event->status) }}
                                </span>
                            </td>
                            <td>{{ $event->ticket_types_count }}</td>
                            <td class="table-actions">
                                <a href="{{ route('backend.events.edit', $event) }}" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <form action="{{ route('backend.events.destroy', $event) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No events found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $events->links() }}
        </div>
    </div>
</div>
@endsection
