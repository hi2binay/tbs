<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketType extends Model
{
    use HasFactory;
    protected $fillable = [
        'event_id',
        'name',
        'description',
        'currency',
        'price_cents',
        'total_quantity',
        'sold_count',
        'reserved_count',
        'per_user_limit',
        'is_active',
    ];

    protected $casts = [
        'price_cents' => 'integer',
        'total_quantity' => 'integer',
        'sold_count' => 'integer',
        'reserved_count' => 'integer',
        'per_user_limit' => 'integer',
        'is_active' => 'boolean',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function bookingItems(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }

    public function getAvailableQuantityAttribute(): int
    {
        return $this->total_quantity - $this->sold_count - $this->reserved_count;
    }

    public function getPriceAttribute(): float
    {
        return $this->price_cents / 100;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
