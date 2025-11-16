<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingItem extends Model
{
    protected $fillable = [
        'booking_id',
        'ticket_type_id',
        'quantity',
        'unit_price_cents',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price_cents' => 'integer',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }

    public function getUnitPriceAttribute(): float
    {
        return $this->unit_price_cents / 100;
    }

    public function getTotalAttribute(): float
    {
        return ($this->unit_price_cents * $this->quantity) / 100;
    }
}
