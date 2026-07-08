<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Concerns\HasUuid;
use App\Concerns\HasSlug;

class Product extends Model
{
    use HasUuid, HasSlug;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_new' => 'boolean',
    ];

    protected $appends = [
        'category_name',
        'thumbnail_url',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function getCategoryNameAttribute(): string
    {
        return $this->category?->name ?? 'Uncategorized';
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class, 'product_id')->orderBy('sort_order');
    }

    public function getThumbnailUrlAttribute(): string
    {
        return $this->images->first()?->image_url ?? asset('assets/images/default.png');
    }
}