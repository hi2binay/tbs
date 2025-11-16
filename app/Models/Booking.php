<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory;
    protected $fillable = [
        'reservation_id',
        'user_id',
        'code',
        'status',
        'total_amount_cents',
        'currency',
    ];

    protected $casts = [
        'total_amount_cents' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->code)) {
                $booking->code = 'BK-'.strtoupper(Str::random(10));
            }
        });
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function getTotalAmountAttribute(): float
    {
        return $this->total_amount_cents / 100;
    }

    public function getPaymentStatusAttribute()
    {
        return $this->payment?->status ?? 'pending';
    }

    public function getEventAttribute()
    {
        return $this->reservation->event ?? null;
    }
}
