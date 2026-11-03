<?php

namespace Modules\Delivery\Models;

use App\Concerns\HasSlug;
use App\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryArea extends Model
{
    use HasSlug, HasUuid;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(DeliveryLocation::class, 'delivery_location_id');
    }
}
