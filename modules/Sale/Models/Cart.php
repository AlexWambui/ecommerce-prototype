<?php

namespace Modules\Sale\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\User\Models\User;
use App\Concerns\HasUuid;

class Cart extends Model
{
    use HasUuid;

    protected $guarded = [];

    protected $casts = [
        'expires_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'shipping' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function getItemCount(): int
    {
        return $this->items->sum('quantity');
    }

    public function isEmpty(): bool
    {
        return $this->items->isEmpty();
    }

    public function recalculate(): void
    {
        $subtotal = $this->items->sum('subtotal');
        $tax = $this->items->sum('tax');
        $discount = $this->items->sum('discount');
        $total = $subtotal + $tax + $this->shipping - $discount;

        $this->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'discount' => $discount,
            'total' => max(0, $total), // Ensure total isn't negative
        ]);
    }

    public function clear(): void
    {
        $this->items()->delete();
        $this->update([
            'subtotal' => 0,
            'tax' => 0,
            'shipping' => 0,
            'discount' => 0,
            'total' => 0,
            'coupon_code' => null,
        ]);
    }

    public function merge(Cart $otherCart): void
    {
        foreach ($otherCart->items as $item) {
            $existingItem = $this->items()
                ->where('product_id', $item->product_id)
                ->first();

            if ($existingItem) {
                // Update quantity if product already in cart
                $existingItem->quantity += $item->quantity;
                $existingItem->subtotal = $existingItem->quantity * $existingItem->unit_price;
                $existingItem->total = $existingItem->subtotal + $existingItem->tax - $existingItem->discount;
                $existingItem->save();
            } else {
                // Move the item to this cart
                $item->cart_id = $this->id;
                $item->save();
            }
        }

        // Recalculate totals
        $this->recalculate();

        // Delete the old cart
        $otherCart->delete();
    }

    // Scope for active carts
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now())
            ->orWhereNull('expires_at');
    }
}