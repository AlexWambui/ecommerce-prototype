<?php

namespace Modules\Order\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Modules\Order\Models\Order;
use Modules\Order\Models\OrderStatus;
use Modules\Order\Models\OrderItem;
use Modules\Product\Models\Product;
use Modules\Payment\Models\Payment;
use Modules\Order\Http\Resources\OrderResource;
use Modules\Order\Http\Resources\ProductPOSResource;
use Modules\Order\Http\Requests\OrderRequest;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::search($request->search)->latest()->paginate(50);

        return Inertia::render('app/orders/orders/Index', [
            'orders' => OrderResource::collection($orders),
            'filters' => [
                'search' => $request->search
            ],
        ]);
    }

    public function create()
    {
        $products = Product::query()
            ->orderBy('name')
            ->where('is_active', true)
            ->get();

        return inertia('app/orders/orders/Create', [
            'products' => ProductPOSResource::collection($products)
        ]);
    }

    public function store(OrderRequest $request)
    {
        $validated = $request->validated();

        return DB::transaction(function () use ($validated) {
            // --- CALCULATE TOTALS ---
            $subtotal = collect($validated['cart_items'])->sum(fn($item) => $item['price']);

            // Total cost price
            $total_cost_price = 0;
            foreach ($validated['cart_items'] as $item) {
                $product = Product::find($item['id']);
                $total_cost_price += ($product->cost_price ?? 0);
            }

            // Total selling price (subtotal + delivery)
            $total_selling_price = $subtotal + $validated['delivery_cost'];

            // Calculate total paid from payments
            $total_paid = collect($validated['payments'])->sum('amount');

            // Determine payment status
            $payment_status = $total_paid <= 0 ? 'pending' : ($total_paid >= $total_selling_price ? 'paid' : 'partially_paid');

            // Set the initial order status based on delivery method
            $order_status = $validated['delivery_method'] === 'delivery' ? Order::STATUS_PENDING : Order::STATUS_PROCESSING;

            // delivery details
            $delivery_location = $validated['delivery_method'] === 'shop' ? 'shop' : $validated['location'];
            $delivery_area = $validated['delivery_method'] === 'shop' ? 'shop' : $validated['area'];
            $delivery_address = $validated['delivery_method'] === 'shop' ? 'shop' : $validated['address'];

            // --- CREATE THE ORDER ---
            $order = Order::create([
                'order_number' => 'Ord_' . strtoupper(Str::random(6)) . '_' . now()->format('ymd'),
                'order_channel' => $validated['order_channel'],
                
                'subtotal' => $subtotal,
                'shipping_cost' => $validated['delivery_cost'],
                'total_selling_price' => $total_selling_price,
                'amount_paid' => $total_paid,

                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_email' => $validated['customer_email'],

                'delivery_method' => $validated['delivery_method'],
                'delivery_location' => $delivery_location,
                'delivery_area' => $delivery_area,
                'delivery_address' => $delivery_address,

                'sold_at' => now(),
            ]);

            OrderStatus::create([
                'order_id' => $order->id,
                'status' => $order_status,
                'notes' => 'Order created via ' . $validated['order_channel'] . ' | Payment: ' . $payment_status,
                'user_id' => Auth::id(), // If admin is logged in
                'changed_at' => now(),
            ]);

            // --- CREATE ORDER ITEMS (Loop through cart) ---
            foreach ($validated['cart_items'] as $item) {
                $product = Product::find($item['id']);
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    
                    'product_name' => $product->name,
                    'quantity' => 1,
                    'cost_price' => $product->cost_price ?? 0,
                    'selling_price' => $item['price'],
                    'discount' => 0,
                ]);

                // Decrease stock
                $product->decrement('current_stock', 1);
            }

            // --- CREATE PAYMENT RECORD ---
            foreach ($validated['payments'] as $paymentData) {
                if ($paymentData['amount'] > 0) {
                    Payment::create([
                        'order_id' => $order->id,
                        'payment_method' => $paymentData['method'],
                        'transaction_reference' => null, // Handled manually for walk-in
                        'amount' => $paymentData['amount'],
                        'payment_status' => 'paid',
                    ]);
                }
            }

            // If fully paid and delivery, update order status to processing
            if ($payment_status === 'paid' && $validated['delivery_method'] === 'delivery') {
                $order->update(['order_status' => Order::STATUS_PROCESSING]);

                OrderStatus::create([
                    'order_id' => $order->id,
                    'status' => Order::STATUS_PROCESSING,
                    'notes' => 'Order fully paid, ready for processing',
                    'user_id' => Auth::id(),
                    'changed_at' => now(),
                ]);
            }

            // --- OPTIONAL: ASSIGN LOYALTY POINTS ---
            // If you have a user/loyalty system, you can add points here
            // $user = User::where('phone', $validated['customer_phone'])->first();
            // if($user) $user->increment('points', floor($totalAmount / 100));

            Inertia::flash('toast', [
                'type' => 'success',
                'message' => "Order added successfully",
            ]);

            return redirect()->back();
        });
    }

    public function edit()
    {
        //
    }

    public function destroy()
    {
        //
    }
}