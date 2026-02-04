<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Favorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'accommodation_id',
        'memo',
    ];

    /**
     * 顧客
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * 宿泊施設
     */
    public function accommodation(): BelongsTo
    {
        return $this->belongsTo(Accommodation::class);
    }

    /**
     * お気に入り追加/削除をトグル
     */
    public static function toggle(int $customerId, int $accommodationId): array
    {
        $favorite = self::where('customer_id', $customerId)
            ->where('accommodation_id', $accommodationId)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return ['action' => 'removed', 'favorite' => null];
        }

        $favorite = self::create([
            'customer_id' => $customerId,
            'accommodation_id' => $accommodationId,
        ]);

        return ['action' => 'added', 'favorite' => $favorite];
    }
}
