@extends('backend.layouts.admin')

@section('title', 'User Details')
@section('page-title', 'User Details')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">User Information</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small">Name</label>
                    <div class="fw-bold">{{ $user->name }}</div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Email</label>
                    <div>{{ $user->email }}</div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Phone</label>
                    <div>{{ $user->phone ?? 'N/A' }}</div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Role</label>
                    <div>
                        @if($user->is_admin)
                            <span class="badge bg-success">
                                <i class="bi bi-shield-check"></i> Admin
                            </span>
                        @else
                            <span class="badge bg-secondary">User</span>
                        @endif
                    </div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Member Since</label>
                    <div>{{ $user->created_at->format('M d, Y') }}</div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('backend.users.edit', $user) }}" class="btn btn-warning w-100">
                        <i class="bi bi-pencil"></i> Edit User
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Booking History</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Event</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($user->bookings as $booking)
                                <tr>
                                    <td><strong>{{ $booking->code }}</strong></td>
                                    <td>{{ $booking->reservation->event->name ?? 'N/A' }}</td>
                                    <td>${{ number_format($booking->total_amount, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $booking->status === 'confirmed' ? 'success' : ($booking->status === 'pending' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($booking->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $booking->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <a href="{{ route('backend.bookings.show', $booking) }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No bookings yet</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
