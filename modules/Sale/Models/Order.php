<?php

namespace Modules\Sale\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\User\Models\User;
use Modules\Payment\Models\Payment;

class Order extends Model
{
    protected $guarded = [];

    protected $casts = [
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function statuses(): HasMany
    {
        return $this->hasMany(OrderStatus::class)->orderBy('created_at', 'desc');
    }

    public function currentStatus(): ?OrderStatus
    {
        return $this->statuses->first();
    }

    public function getFullNameAttribute(): string
    {
        return $this->customer_first_name . ' ' . $this->customer_last_name;
    }

    public function getFullShippingAddressAttribute(): string
    {
        $address = $this->shipping_address_line1;
        if ($this->shipping_address_line2) {
            $address .= ', ' . $this->shipping_address_line2;
        }
        $address .= ', ' . $this->shipping_city;
        if ($this->shipping_state) {
            $address .= ', ' . $this->shipping_state;
        }
        $address .= ' ' . $this->shipping_postal_code;
        $address .= ', ' . $this->shipping_country;
        return $address;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    public function canBeShipped(): bool
    {
        return $this->status === 'processing' && !$this->shipped_at;
    }

    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'processing',
            'paid_at' => now(),
        ]);
        $this->addStatus('processing', 'Payment confirmed');
    }

    public function addStatus(string $status, ?string $notes = null, ?int $userId = null): OrderStatus
    {
        return $this->statuses()->create([
            'order_id' => $this->id,
            'status' => $status,
            'notes' => $notes,
            'user_id' => $userId,
            'is_system' => $userId === null,
        ]);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}