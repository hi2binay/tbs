<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;
    protected $fillable = [
        'slug',
        'name',
        'description',
        'venue',
        'location',
        'status',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    protected $appends = ['title', 'image_url', 'category'];

    public function ticketTypes(): HasMany
    {
        return $this->hasMany(TicketType::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('starts_at', '>', now());
    }

    public function getTitleAttribute()
    {
        return $this->name;
    }

    public function getImageUrlAttribute()
    {
        return 'https://via.placeholder.com/800x400/4f46e5/ffffff?text=' . urlencode($this->name);
    }

    public function getCategoryAttribute()
    {
        return 'general';
    }

    public function getVenueAttribute($value)
    {
        // Return object for compatibility with views that use $event->venue->name
        return (object) [
            'name' => $value ?? 'TBD',
            'address' => $this->location ?? '',
            'city' => $this->location ?? '',
            'capacity' => null,
        ];
    }
}
