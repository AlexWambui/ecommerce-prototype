<?php

namespace Modules\Order\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use Modules\User\Models\User;
use Modules\Payment\Models\Payment;
use App\Concerns\HasUuid;
use Modules\Order\Enums\OrderStatusEnum;
use Modules\Order\Enums\DeliveryStatusEnum;

class Order extends Model
{
    use HasUuid;

    protected $guarded = [];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_selling_price' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'sold_at' => 'datetime',
        'order_status' => OrderStatusEnum::class,
        'delivery_status' => DeliveryStatusEnum::class,
    ];

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

    public function currentOrderStatus()
    {
        return $this->belongsTo(OrderStatus::class, 'current_order_status_id');
    }

    public function currentDeliveryStatus()
    {
        return $this->belongsTo(OrderStatus::class, 'current_delivery_status_id');
    }

    public function updateOrderStatus(OrderStatusEnum $status, ?string $notes = null, ?array $metadata = null, ?int $userId = null): OrderStatus
    {
        // Validate transition
        if (!$this->canTransitionToOrderStatus($status)) {
            throw new InvalidArgumentException("Invalid order status transition from {$this->order_status->value} to {$status->value}");
        }

        $statusRecord = $this->orderStatuses()->create([
            'type' => 'order',
            'status' => $status->value,
            'notes' => $notes,
            'metadata' => $metadata,
            'user_id' => $userId ?? Auth::id(),
            'is_system' => is_null($userId),
            'changed_at' => now(),
        ]);

        $this->update([
            'order_status' => $status->value,
            'current_order_status_id' => $statusRecord->id,
        ]);

        return $statusRecord;
    }

    public function updateDeliveryStatus(DeliveryStatusEnum $status, ?string $notes = null, ?array $metadata = null, ?int $userId = null): OrderStatus
    {
        // Validate transition
        if (!$this->canTransitionToDeliveryStatus($status)) {
            throw new InvalidArgumentException("Invalid delivery status transition from {$this->delivery_status->value} to {$status->value}");
        }

        $statusRecord = $this->orderStatuses()->create([
            'type' => 'delivery',
            'status' => $status->value,
            'notes' => $notes,
            'metadata' => $metadata,
            'user_id' => $userId ?? Auth::id(),
            'is_system' => is_null($userId),
            'changed_at' => now(),
        ]);

        $this->update([
            'delivery_status' => $status->value,
            'current_delivery_status_id' => $statusRecord->id,
        ]);

        return $statusRecord;
    }

    public function canTransitionToOrderStatus(OrderStatusEnum $newStatus): bool
    {
        $currentStatus = $this->order_status ?? OrderStatusEnum::PENDING;
        $validTransitions = $this->getValidOrderTransitions();
        
        return in_array($newStatus->value, $validTransitions[$currentStatus->value] ?? []);
    }

    public function canTransitionToDeliveryStatus(DeliveryStatusEnum $newStatus): bool
    {
        $currentStatus = $this->delivery_status ?? DeliveryStatusEnum::PENDING;
        $validTransitions = $this->getValidDeliveryTransitions();
        
        return in_array($newStatus->value, $validTransitions[$currentStatus->value] ?? []);
    }

    protected function getValidOrderTransitions(): array
    {
        return [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['processing', 'cancelled'],
            'processing' => ['ready_for_pickup', 'cancelled'],
            'ready_for_pickup' => ['completed', 'cancelled'],
            'completed' => ['refunded'],
            'cancelled' => ['refunded'],
            'refunded' => [],
        ];
    }

    protected function getValidDeliveryTransitions(): array
    {
        return [
            'pending' => ['picked_up'],
            'picked_up' => ['in_transit'],
            'in_transit' => ['out_for_delivery', 'delivery_failed'],
            'out_for_delivery' => ['delivered', 'delivery_failed'],
            'delivered' => ['returned'],
            'delivery_failed' => ['returned'],
            'returned' => [],
        ];
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getPaymentStatusAttribute(): string
    {
        $total_paid = $this->payments()->sum('amount');

        if ($total_paid <= 0) {
            return 'pending';
        }

        if ($total_paid >= $this->total_selling_price) {
            return 'paid';
        }

        return 'partially_paid';
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

    public function getFullNameAttribute(): string
    {
        return $this->customer_name ?? 'Guest';
    }

    public function isShopPickup(): bool
    {
        return $this->delivery_method === 'shop';
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->order_status->value, ['pending', 'confirmed', 'processing']);
    }

    public function canBeShipped(): bool
    {
        return $this->status === 'processing' && !$this->shipped_at;
    }

    public function scopeCompleted($query)
    {
        return $query->where('order_status', OrderStatusEnum::COMPLETED->value);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeDelivered($query)
    {
        return $query->where('delivery_status', DeliveryStatusEnum::DELIVERED->value);
    }

    public function scopePaid($query)
    {
        return $query->whereColumn('amount_paid', '>=', 'total_selling_price');
    }

    public function scopePending($query)
    {
        return $query->where('order_status', OrderStatusEnum::PENDING->value);
    }

    public function scopeProcessing($query)
    {
        return $query->where('order_status', OrderStatusEnum::PROCESSING->value);
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