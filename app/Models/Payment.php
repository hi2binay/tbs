<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'booking_id',
        'provider',
        'provider_payment_id',
        'status',
        'amount_cents',
        'currency',
        'idempotency_key',
        'payload',
        'error_message',
    ];

    protected $casts = [
        'amount_cents' => 'integer',
        'payload' => 'array',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function getAmountAttribute(): float
    {
        return $this->amount_cents / 100;
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCaptured(): bool
    {
        return $this->status === 'captured';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
