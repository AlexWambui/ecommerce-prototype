<?php

namespace Modules\Sale\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Product\Models\Product;
use App\Concerns\HasUuid;

class CartItem extends Model
{
    protected $guarded = [];

    protected $casts = [
        'attributes' => 'array',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // recalculate item totals
    public function recalculate(): void
    {
        $this->subtotal = $this->quantity * $this->unit_price;
        $this->total = $this->subtotal + $this->tax - $this->discount;
        $this->save();

        // Recalculate parent cart
        $this->cart->recalculate();
    }
}