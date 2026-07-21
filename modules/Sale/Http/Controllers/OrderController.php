<?php

namespace Modules\Sale\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Sale\Services\OrderService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * List user orders
     */
    public function index()
    {
        $orders = $this->orderService->getUserOrders(auth()->id());

        return Inertia::render('Sale/Orders/Index', [
            'orders' => [
                'data' => $orders->items(),
                'links' => $orders->links(),
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * Show order details
     */
    public function show(int $orderId)
    {
        $order = $this->orderService->getOrderWithDetails($orderId);

        if ($order->user_id !== auth()->id()) {
            abort(403, 'You do not own this order');
        }

        return Inertia::render('Sale/Orders/Show', [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'created_at' => $order->created_at->toISOString(),
                'subtotal' => $order->subtotal,
                'tax' => $order->tax,
                'shipping_cost' => $order->shipping_cost,
                'discount' => $order->discount,
                'total' => $order->total,
                'customer_first_name' => $order->customer_first_name,
                'customer_last_name' => $order->customer_last_name,
                'customer_email' => $order->customer_email,
                'shipping_address_line1' => $order->shipping_address_line1,
                'shipping_address_line2' => $order->shipping_address_line2,
                'shipping_city' => $order->shipping_city,
                'shipping_state' => $order->shipping_state,
                'shipping_postal_code' => $order->shipping_postal_code,
                'shipping_country' => $order->shipping_country,
                'shipping_method' => $order->shipping_method,
                'tracking_number' => $order->tracking_number,
                'paid_at' => $order->paid_at ? $order->paid_at->toISOString() : null,
                'shipped_at' => $order->shipped_at ? $order->shipped_at->toISOString() : null,
                'delivered_at' => $order->delivered_at ? $order->delivered_at->toISOString() : null,
                'items' => $order->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_name' => $item->product_name,
                        'product_sku' => $item->product_sku,
                        'product_attributes' => $item->product_attributes,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'subtotal' => $item->subtotal,
                        'total' => $item->total,
                    ];
                }),
                'payment' => $order->payment ? [
                    'id' => $order->payment->id,
                    'provider' => $order->payment->provider,
                    'transaction_id' => $order->payment->transaction_id,
                    'amount' => $order->payment->amount,
                    'status' => $order->payment->status,
                    'created_at' => $order->payment->created_at->toISOString(),
                ] : null,
                'statuses' => $order->statuses->map(function ($status) {
                    return [
                        'status' => $status->status,
                        'notes' => $status->notes,
                        'created_at' => $status->created_at->toISOString(),
                        'user_name' => $status->user ? $status->user->name : 'System',
                    ];
                }),
            ],
            'can_cancel' => $order->canBeCancelled(),
        ]);
    }

    /**
     * Cancel order
     */
    public function cancel(Request $request, int $orderId)
    {
        $order = $this->orderService->getOrderWithDetails($orderId);

        if ($order->user_id !== auth()->id()) {
            abort(403, 'You do not own this order');
        }

        if (!$order->canBeCancelled()) {
            return back()->with('error', 'This order cannot be cancelled');
        }

        $this->orderService->updateStatus(
            $order,
            'cancelled',
            $request->reason ?? 'Order cancelled by customer'
        );

        return redirect()->back()->with('success', 'Order cancelled successfully');
    }

    /**
     * Track order
     */
    public function track(int $orderId)
    {
        $order = $this->orderService->getOrderWithDetails($orderId);

        if ($order->user_id !== auth()->id()) {
            abort(403, 'You do not own this order');
        }

        return Inertia::render('Sale/Orders/Track', [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'tracking_number' => $order->tracking_number,
                'shipping_method' => $order->shipping_method,
                'shipped_at' => $order->shipped_at ? $order->shipped_at->toISOString() : null,
                'delivered_at' => $order->delivered_at ? $order->delivered_at->toISOString() : null,
                'statuses' => $order->statuses->map(function ($status) {
                    return [
                        'status' => $status->status,
                        'notes' => $status->notes,
                        'created_at' => $status->created_at->toISOString(),
                    ];
                }),
            ],
        ]);
    }
}