@extends('backend.layouts.admin')

@section('title', 'Bookings')
@section('page-title', 'Bookings Management')

@section('content')
<div class="card">
    <div class="card-header bg-white">
        <h5 class="mb-0">All Bookings</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search by code, name, or email..." 
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel"></i> Filter
                </button>
            </div>
            <div class="col-md-3">
                <a href="{{ route('backend.bookings.index') }}" class="btn btn-secondary w-100">
                    <i class="bi bi-x-circle"></i> Clear
                </a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Customer</th>
                        <th>Event</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                        <tr>
                            <td><strong>{{ $booking->code }}</strong></td>
                            <td>
                                <div>{{ $booking->user->name }}</div>
                                <small class="text-muted">{{ $booking->user->email }}</small>
                            </td>
                            <td>{{ $booking->reservation->event->name ?? 'N/A' }}</td>
                            <td>${{ number_format($booking->total_amount, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ $booking->status === 'confirmed' ? 'success' : ($booking->status === 'pending' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </td>
                            <td>
                                @if($booking->payment)
                                    <span class="badge bg-{{ $booking->payment->status === 'completed' ? 'success' : ($booking->payment->status === 'pending' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($booking->payment->status) }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $booking->created_at->format('M d, Y H:i') }}</td>
                            <td class="table-actions">
                                <a href="{{ route('backend.bookings.show', $booking) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No bookings found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $bookings->links() }}
        </div>
    </div>
</div>
@endsection
