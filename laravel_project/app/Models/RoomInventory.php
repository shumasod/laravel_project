<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomInventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'accommodation_id',
        'room_type',
        'date',
        'total_rooms',
        'available_rooms',
        'reserved_rooms',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function accommodation(): BelongsTo
    {
        return $this->belongsTo(Accommodation::class);
    }

    /**
     * 在庫を予約
     */
    public function reserve(int $quantity = 1): bool
    {
        if ($this->available_rooms < $quantity) {
            return false;
        }

        $this->available_rooms -= $quantity;
        $this->reserved_rooms += $quantity;
        $this->save();

        return true;
    }

    /**
     * 在庫を解放（キャンセル時）
     */
    public function release(int $quantity = 1): void
    {
        $this->available_rooms += $quantity;
        $this->reserved_rooms -= $quantity;
        $this->save();
    }
}
