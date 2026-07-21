<?php

namespace Modules\Sale\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Sale\Services\CartService;
use Modules\Sale\Services\OrderService;
use Modules\Payment\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    protected CartService $cartService;
    protected OrderService $orderService;
    protected PaymentService $paymentService;

    public function __construct(
        CartService $cartService,
        OrderService $orderService,
        PaymentService $paymentService
    ) {
        $this->cartService = $cartService;
        $this->orderService = $orderService;
        $this->paymentService = $paymentService;
    }

    /**
     * Show checkout page
     */
    public function index()
    {
        $cart = $this->cartService->getCart();

        if ($cart->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty');
        }

        $user = auth()->user();

        return Inertia::render('Sale/Checkout/Index', [
            'cart' => $this->formatCartForCheckout($cart),
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? null,
            ] : null,
            'step' => 1, // Email step
        ]);
    }

    /**
     * Process email step
     */
    public function processEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = \Modules\User\Models\User::where('email', $request->email)->first();

        if ($user) {
            // If user is not logged in, log them in silently
            if (!auth()->check()) {
                auth()->login($user);
                // Merge guest cart with user cart
                $this->cartService->mergeGuestCartWithUser($user);
            }
        } else {
            // Create ghost account
            $user = \Modules\User\Models\User::create([
                'name' => 'Guest',
                'email' => $request->email,
                'password' => bcrypt(Str::random(50)),
                'is_ghost' => true,
            ]);

            // Log the ghost in
            auth()->login($user);

            // Associate cart with the ghost user
            $cart = $this->cartService->getCart();
            $cart->update([
                'user_id' => $user->id,
                'email' => $request->email,
            ]);

            // Merge any existing guest cart
            $this->cartService->mergeGuestCartWithUser($user);
        }

        return response()->json([
            'success' => true,
            'message' => 'Email processed successfully',
            'redirect' => route('checkout.shipping')
        ]);
    }

    /**
     * Show shipping form
     */
    public function shipping()
    {
        if (!auth()->check()) {
            return redirect()->route('checkout.index');
        }

        $cart = $this->cartService->getCart();

        if ($cart->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty');
        }

        $user = auth()->user();

        return Inertia::render('Sale/Checkout/Shipping', [
            'cart' => $this->formatCartForCheckout($cart),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? null,
            ],
            'step' => 2,
        ]);
    }

    /**
     * Process shipping information
     */
    public function processShipping(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'nullable|string|max:255',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|size:2',
            'shipping_method' => 'required|string|in:standard,express',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Save shipping info to session
        session([
            'checkout.shipping' => $request->only([
                'first_name', 'last_name', 'phone', 'address_line1',
                'address_line2', 'city', 'state', 'postal_code',
                'country', 'shipping_method', 'notes'
            ])
        ]);

        // Update user profile with shipping info
        $user = auth()->user();
        $user->update([
            'phone' => $request->phone,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Shipping information saved',
            'redirect' => route('checkout.payment')
        ]);
    }

    /**
     * Show payment form
     */
    public function payment()
    {
        if (!auth()->check()) {
            return redirect()->route('checkout.index');
        }

        $cart = $this->cartService->getCart();

        if ($cart->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty');
        }

        if (!session('checkout.shipping')) {
            return redirect()->route('checkout.shipping')
                ->with('error', 'Please provide shipping information first');
        }

        // Calculate shipping cost
        $shippingMethod = session('checkout.shipping.shipping_method');
        $shippingCost = $this->calculateShipping($shippingMethod, $cart);
        $total = $cart->subtotal + $cart->tax + $shippingCost - $cart->discount;

        return Inertia::render('Sale/Checkout/Payment', [
            'cart' => $this->formatCartForCheckout($cart),
            'shipping_cost' => $shippingCost,
            'total' => $total,
            'shipping_method' => $shippingMethod,
            'step' => 3,
        ]);
    }

    /**
     * Process payment and place order
     */
    public function processPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|string|in:card,mpesa,paypal',
            'card_number' => 'required_if:payment_method,card|string|size:16',
            'card_expiry' => 'required_if:payment_method,card|string|size:5',
            'card_cvc' => 'required_if:payment_method,card|string|size:3',
            'mpesa_phone' => 'required_if:payment_method,mpesa|string|regex:/^[0-9]{10,12}$/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $order = null;

            DB::transaction(function () use ($request, &$order) {
                $cart = $this->cartService->getCart();
                $shippingData = session('checkout.shipping');

                // Calculate final total with shipping
                $shippingCost = $this->calculateShipping(
                    $shippingData['shipping_method'],
                    $cart
                );

                // Update cart with shipping cost
                $cart->shipping = $shippingCost;
                $cart->recalculate();

                // Create order from cart
                $order = $this->orderService->createOrderFromCart(
                    $cart,
                    [
                        'email' => auth()->user()->email,
                        'phone' => $shippingData['phone'],
                        'first_name' => $shippingData['first_name'],
                        'last_name' => $shippingData['last_name'],
                        'notes' => $shippingData['notes'] ?? null,
                    ],
                    $shippingData,
                    $shippingData
                );

                // Process payment
                $paymentData = [
                    'provider' => $request->payment_method,
                    'payment_method' => $request->payment_method,
                    'mpesa_phone' => $request->mpesa_phone ?? null,
                ];

                $payment = $this->paymentService->processPayment($order, $paymentData);
                
                // Simulate payment success (replace with real gateway)
                $payment->provider_response = [
                    'success' => true,
                    'transaction_id' => 'TXN' . strtoupper(Str::random(10)),
                ];
                $payment->save();

                $this->paymentService->confirmPayment($payment);
                session()->forget('checkout.shipping');
            });

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully',
                'redirect' => route('checkout.confirmation', $order->id)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show order confirmation
     */
    public function confirmation(int $orderId)
    {
        $order = $this->orderService->getOrderWithDetails($orderId);

        if ($order->user_id !== auth()->id()) {
            abort(403, 'You do not own this order');
        }

        return Inertia::render('Sale/Checkout/Confirmation', [
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
            ],
        ]);
    }

    /**
     * Calculate shipping cost
     */
    protected function calculateShipping(string $method, $cart): float
    {
        $subtotal = $cart->subtotal;

        if ($subtotal >= 100) {
            return 0;
        }

        return $method === 'express' ? 15.00 : 5.00;
    }

    /**
     * Format cart for checkout
     */
    protected function formatCartForCheckout($cart): array
    {
        return [
            'id' => $cart->id,
            'items' => $cart->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'product_sku' => $item->product_sku,
                    'unit_price' => $item->unit_price,
                    'quantity' => $item->quantity,
                    'attributes' => $item->attributes,
                    'subtotal' => $item->subtotal,
                    'total' => $item->total,
                ];
            }),
            'subtotal' => $cart->subtotal,
            'tax' => $cart->tax,
            'shipping' => $cart->shipping,
            'discount' => $cart->discount,
            'total' => $cart->total,
            'items_count' => $cart->getItemCount(),
        ];
    }
}