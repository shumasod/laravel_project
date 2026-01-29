<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AmenityCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_en',
        'icon',
        'display_order',
    ];

    /**
     * このカテゴリのアメニティ
     */
    public function amenities(): HasMany
    {
        return $this->hasMany(Amenity::class, 'category_id')->orderBy('display_order');
    }
}
