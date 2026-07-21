<?php

namespace Modules\Sale\Services;

use Modules\Sale\Models\Order;
use Modules\Sale\Models\Cart;
use Modules\Sale\Models\OrderItem;
use Modules\User\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Create an order from a cart
     */
    public function createOrderFromCart(Cart $cart, array $customerData, array $shippingData, array $billingData = null): Order
    {
        return DB::transaction(function () use ($cart, $customerData, $shippingData, $billingData) {
            // If no billing data provided, use shipping data
            if (!$billingData) {
                $billingData = $shippingData;
            }

            // Generate unique order number
            $orderNumber = $this->generateOrderNumber();

            // Create order
            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => auth()->id(),
                'status' => 'pending',
                'customer_email' => $customerData['email'],
                'customer_phone' => $customerData['phone'] ?? null,
                'customer_first_name' => $customerData['first_name'],
                'customer_last_name' => $customerData['last_name'],
                'shipping_address_line1' => $shippingData['address_line1'],
                'shipping_address_line2' => $shippingData['address_line2'] ?? null,
                'shipping_city' => $shippingData['city'],
                'shipping_state' => $shippingData['state'] ?? null,
                'shipping_postal_code' => $shippingData['postal_code'],
                'shipping_country' => $shippingData['country'],
                'billing_address_line1' => $billingData['address_line1'],
                'billing_address_line2' => $billingData['address_line2'] ?? null,
                'billing_city' => $billingData['city'],
                'billing_state' => $billingData['state'] ?? null,
                'billing_postal_code' => $billingData['postal_code'],
                'billing_country' => $billingData['country'],
                'currency' => $cart->currency,
                'subtotal' => $cart->subtotal,
                'tax' => $cart->tax,
                'shipping_cost' => $cart->shipping,
                'discount' => $cart->discount,
                'total' => $cart->total,
                'customer_notes' => $customerData['notes'] ?? null,
            ]);

            // Create order items from cart items
            foreach ($cart->items as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'product_name' => $cartItem->product_name,
                    'product_sku' => $cartItem->product_sku,
                    'product_attributes' => $cartItem->attributes,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $cartItem->unit_price,
                    'subtotal' => $cartItem->subtotal,
                    'tax' => $cartItem->tax,
                    'discount' => $cartItem->discount,
                    'total' => $cartItem->total,
                ]);
            }

            // Add initial status
            $order->addStatus('pending', 'Order created');

            // Clear the cart
            $cart->clear();

            return $order;
        });
    }

    /**
     * Get order with details
     */
    public function getOrderWithDetails(int $orderId): Order
    {
        return Order::with(['items.product', 'payment', 'statuses.user'])
            ->findOrFail($orderId);
    }

    /**
     * Get user's order history
     */
    public function getUserOrders(int $userId, int $perPage = 10)
    {
        return Order::forUser($userId)
            ->with(['items', 'payment'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Update order status
     */
    public function updateStatus(Order $order, string $newStatus, ?string $notes = null): void
    {
        $oldStatus = $order->status;
        
        $order->update(['status' => $newStatus]);
        $order->addStatus($newStatus, $notes);

        // Handle specific status updates
        if ($newStatus === 'shipped' && !$order->shipped_at) {
            $order->update(['shipped_at' => now()]);
        }

        if ($newStatus === 'delivered' && !$order->delivered_at) {
            $order->update(['delivered_at' => now()]);
        }
    }

    /**
     * Generate unique order number
     */
    protected function generateOrderNumber(): string
    {
        $prefix = 'ORD';
        $date = now()->format('Ymd');
        $random = Str::upper(Str::random(6));
        
        $orderNumber = $prefix . $date . $random;

        // Ensure uniqueness
        while (Order::where('order_number', $orderNumber)->exists()) {
            $random = Str::upper(Str::random(6));
            $orderNumber = $prefix . $date . $random;
        }

        return $orderNumber;
    }
}