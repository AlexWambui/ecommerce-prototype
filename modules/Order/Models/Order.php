<?php

namespace Modules\Order\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;
use Modules\User\Models\User;
use Modules\Payment\Models\Payment;
use App\Concerns\HasUuid;

class Order extends Model
{
    use HasUuid;

    protected $guarded = [];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'sold_at' => 'datetime',
    ];

    // Order fulfillment statuses
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';

    // Payment statuses
    const PAYMENT_PENDING = 'pending';
    const PAYMENT_PARTIALLY_PAID = 'partially_paid';
    const PAYMENT_PAID = 'paid';
    const PAYMENT_DELIVERED = 'refunded';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function orderStatuses(): HasMany
    {
        return $this->hasMany(OrderStatus::class, 'order_id');
    }

    public function currentOrderStatus(): ?OrderStatus
    {
        return $this->statuses->first();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getPaymentStatusAttribute(): string
    {
        $total_paid = $this->payments()->sum('amount');

        if ($total_paid <= 0) {
            return self::PAYMENT_PENDING;
        }

        if ($total_paid >= $this->total_selling_price) {
            return self::PAYMENT_PAID;
        }

        return self::PAYMENT_PARTIALLY_PAID;
    }

    public function getTotalPaidAttribute(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function getBalanceAttribute(): float
    {
        return max(0, $this->total_selling_price - $this->total_paid);
    }

    public function isFullyPaid(): bool
    {
        return $this->total_paid >= $this->total_selling_price;
    }

    public function updateAmountPaid(): void
    {
        $this->amount_paid = $this->total_paid;
        $this->save();
    }

    public function getDeliveryStatusAttribute(): string
    {
        // If delivery method is 'shop', it's always 'pickup'
        if ($this->delivery_method === 'shop') {
            return 'pickup';
        }

        // For delivery orders, check the order status
        return $this->order_status ?? self::STATUS_PENDING;
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

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeDelivered($query)
    {
        return $query->where('order_status', self::STATUS_DELIVERED);
    }

    public function scopePaid($query)
    {
        return $query->whereColumn('amount_paid', '>=', 'total_selling_price');
    }

    public function scopePending($query)
    {
        return $query->where('order_status', self::STATUS_PENDING);
    }

    public function scopeProcessing($query)
    {
        return $query->where('order_status', self::STATUS_PROCESSING);
    }

    public function scopeShipped($query)
    {
        return $query->where('order_status', self::STATUS_SHIPPED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('order_status', self::STATUS_CANCELLED);
    }

    public function scopeSearch($query, $search)
    {
        if (empty($search)) {
            return $query;
        }

        $search_term = '%' . strtolower($search) . '%';

        return $query->where(function ($q) use ($search_term) {
            $q->whereRaw('LOWER(order_number) LIKE ?', [$search_term])
            ->orWhereRaw('LOWER(customer_name) LIKE ?', [$search_term])
            ->orWhereRaw('LOWER(customer_phone) LIKE ?', [$search_term])
            ->orWhereRaw('LOWER(order_status) LIKE ?', [$search_term])
            ->orWhereHas('user', function ($user_query) use ($search_term) {
                $user_query->whereRaw('LOWER(name) LIKE ?', [$search_term])
                ->orWhereRaw('LOWER(email) LIKE ?', [$search_term]);
            });
        });
    }
}