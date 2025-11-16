@extends('backend.layouts.admin')

@section('title', 'Edit Ticket Type')
@section('page-title', 'Edit Ticket Type')

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Edit Ticket Type for {{ $event->name }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('backend.ticket-types.update', [$event, $ticketType]) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label">Ticket Type Name *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name', $ticketType->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3">{{ old('description', $ticketType->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Price ($) *</label>
                            <input type="number" step="0.01" class="form-control @error('price_cents') is-invalid @enderror" 
                                   id="price" name="price" value="{{ old('price', $ticketType->price_cents / 100) }}" required>
                            @error('price_cents')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <input type="hidden" name="price_cents" id="price_cents">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="total_quantity" class="form-label">Quantity *</label>
                            <input type="number" class="form-control @error('total_quantity') is-invalid @enderror" 
                                   id="total_quantity" name="total_quantity" value="{{ old('total_quantity', $ticketType->total_quantity) }}" required>
                            @error('total_quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('backend.events.edit', $event) }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Ticket Type</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelector('form').addEventListener('submit', function(e) {
        const price = parseFloat(document.getElementById('price').value);
        document.getElementById('price_cents').value = Math.round(price * 100);
    });
</script>
@endpush
