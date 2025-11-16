@extends('layouts.app')

@section('title', 'Book Tickets - ' . $event->title)

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="mb-4">Book Tickets</h1>

            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">{{ $event->title }}</h5>
                    <p class="text-muted mb-0">
                        <i class="bi bi-calendar"></i> {{ $event->starts_at->format('M d, Y - h:i A') }}<br>
                        <i class="bi bi-geo-alt"></i> {{ $event->venue->name }}
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('bookings.store', $event) }}" id="bookingForm">
                @csrf

                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Select Tickets</h5>

                        @foreach($event->ticketTypes as $index => $ticketType)
                            @if($ticketType->available_quantity > 0)
                                <div class="border rounded p-3 mb-3 ticket-type-item">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <h6 class="mb-1">{{ $ticketType->name }}</h6>
                                            <small class="text-muted">{{ $ticketType->description }}</small><br>
                                            <small class="text-muted">Available: {{ $ticketType->available_quantity }}</small>
                                        </div>
                                        <div class="col-md-3">
                                            <h5 class="text-primary mb-0">${{ number_format($ticketType->price, 2) }}</h5>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number"
                                                   name="tickets[{{ $index }}][quantity]"
                                                   class="form-control ticket-quantity"
                                                   min="0"
                                                   max="{{ $ticketType->available_quantity }}"
                                                   value="0"
                                                   data-price="{{ $ticketType->price }}">
                                            <input type="hidden"
                                                   name="tickets[{{ $index }}][ticket_type_id]"
                                                   value="{{ $ticketType->id }}">
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Payment Method</h5>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="payment_method" id="card" value="card" required>
                            <label class="form-check-label" for="card">
                                Credit/Debit Card
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="payment_method" id="bank" value="bank_transfer">
                            <label class="form-check-label" for="bank">
                                Bank Transfer
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="cash" value="cash">
                            <label class="form-check-label" for="cash">
                                Cash on Pickup
                            </label>
                        </div>
                        @error('payment_method')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Total Amount:</h5>
                            <h3 class="text-primary mb-0" id="totalAmount">$0.00</h3>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('events.show', $event) }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary flex-grow-1">Confirm Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const quantityInputs = document.querySelectorAll('.ticket-quantity');
        const totalAmountEl = document.getElementById('totalAmount');

        function updateTotal() {
            let total = 0;
            quantityInputs.forEach(input => {
                const quantity = parseInt(input.value) || 0;
                const price = parseFloat(input.dataset.price) || 0;
                total += quantity * price;
            });
            totalAmountEl.textContent = '$' + total.toFixed(2);
        }

        quantityInputs.forEach(input => {
            input.addEventListener('input', updateTotal);
        });
    });
</script>
@endpush
@endsection
