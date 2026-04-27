<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'accommodation_id',
        'room_number',
        'room_type',
        'price_per_night',
        'capacity',
        'description',
        'is_available',
        // Travel search extensions
        'room_type_name',
        'square_meters',
        'bed_type',
        'bed_count',
        'max_occupancy',
        'base_price_weekday',
        'base_price_weekend',
        'room_amenities',
        'room_features',
        'main_image_url',
        'room_description',
        'is_smoking',
        'display_order',
    ];

    protected $casts = [
        'price_per_night' => 'decimal:2',
        'is_available' => 'boolean',
        'room_amenities' => 'array',
        'room_features' => 'array',
        'is_smoking' => 'boolean',
    ];

    /**
     * 宿泊プラン
     */
    public function plans(): HasMany
    {
        return $this->hasMany(RoomPlan::class);
    }

    public function accommodation(): BelongsTo
    {
        return $this->belongsTo(Accommodation::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
