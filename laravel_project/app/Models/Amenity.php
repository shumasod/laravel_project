<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Amenity extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'name_en',
        'icon',
        'is_highlight',
        'display_order',
    ];

    protected $casts = [
        'is_highlight' => 'boolean',
    ];

    /**
     * カテゴリ
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(AmenityCategory::class, 'category_id');
    }

    /**
     * このアメニティを持つ宿泊施設
     */
    public function accommodations(): BelongsToMany
    {
        return $this->belongsToMany(Accommodation::class, 'accommodation_amenities')
            ->withPivot('note')
            ->withTimestamps();
    }

    /**
     * ハイライト表示アメニティ
     */
    public function scopeHighlight($query)
    {
        return $query->where('is_highlight', true);
    }
}
