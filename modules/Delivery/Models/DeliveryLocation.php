<?php

namespace Modules\Delivery\Models;

use App\Concerns\HasSlug;
use App\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryLocation extends Model
{
    use HasSlug, HasUuid;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function deliveryAreas(): HasMany
    {
        return $this->hasMany(DeliveryArea::class, 'delivery_location_id');
    }
}
